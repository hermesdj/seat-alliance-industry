<?php

namespace RecursiveTree\Seat\AllianceIndustry\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use RecursiveTree\Seat\AllianceIndustry\Item\PriceableEveItem;
use RecursiveTree\Seat\AllianceIndustry\Jobs\SendOrderNotifications;
use RecursiveTree\Seat\AllianceIndustry\Jobs\UpdateRepeatingOrders;
use RecursiveTree\Seat\AllianceIndustry\Models\Delivery;
use RecursiveTree\Seat\AllianceIndustry\Models\Order;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use RecursiveTree\Seat\PricesCore\Exceptions\PriceProviderException;
use RecursiveTree\Seat\PricesCore\Facades\PriceProviderSystem;
use RecursiveTree\Seat\PricesCore\Models\PriceProviderInstance;
use RecursiveTree\Seat\TreeLib\Helpers\SeatInventoryPluginHelper;
use RecursiveTree\Seat\TreeLib\Parser\Parser;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Web\Http\Controllers\Controller;


class AllianceIndustryController extends Controller
{
    public function orders()
    {

        $orders = Order::with("deliveries")
            ->where("completed", false)
            ->where("produce_until", ">", DB::raw("NOW()"))
            ->where("is_repeating", false)
            ->get()
            ->filter(function ($order) {
                return $order->assignedQuantity() < $order->quantity;
            });

        $personalOrders = Order::where("user_id", auth()->user()->id)->get();

        return view("allianceindustry::orders", compact("orders", "personalOrders"));
    }

    public function createOrder()
    {
        //ALSO UPDATE API
        $stations = UniverseStation::all();
        //ALSO UPDATE API
        $structures = UniverseStructure::all();

        //ALSO UPDATE API
        $mpp = AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->get(2.5);
        //ALSO UPDATE API
        $location_id = AllianceIndustrySettings::$DEFAULT_ORDER_LOCATION->get(60003760);//jita
        //ALSO UPDATE API
        $default_price_provider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get();
        //ALSO UPDATE API
        $allowPriceProviderSelection = AllianceIndustrySettings::$ALLOW_PRICE_PROVIDER_SELECTION->get(false);

        //ALSO UPDATE API
        return view("allianceindustry::createOrder", compact("allowPriceProviderSelection", "stations", "structures", "mpp", "location_id", "default_price_provider"));
    }

    public function submitOrder(Request $request)
    {
        $request->validate([
            "items" => "required|string",
            "profit" => "required|numeric|min:0",
            "days" => "required|integer|min:1",
            "location" => "required|integer",
            "addProfitToManualPrices" => "nullable|in:on",
            "addToSeatInventory" => "nullable|in:on",
            "splitOrders" => "nullable|in:on",
            "priority" => "required|integer",
            "priceprovider" => "nullable|integer",
            "repetition" => "nullable|integer"
        ]);

        if (AllianceIndustrySettings::$ALLOW_PRICE_PROVIDER_SELECTION->get(false)) {
            $priceProvider = $request->priceprovider;
        } else {
            $priceProvider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get(null);
        }

        if ($priceProvider == null) {
            return redirect()->back()->with('error', trans('allianceindustry::ai-common.error_no_price_provider'));
        }

        $mpp = AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->get(2.5);
        if ($request->profit < $mpp) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_minimal_profit_too_low', ['mpp' => $mpp]));
            return redirect()->route("allianceindustry.createOrder");
        }

        if (!(UniverseStructure::where("structure_id", $request->location)->exists() || UniverseStation::where("station_id", $request->location)->exists())) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_structure_not_found'));
            return redirect()->route("allianceindustry.orders");
        }

        //parse items
        $parser_result = Parser::parseItems($request->items, PriceableEveItem::class);

        //check item count, don't request prices without any items
        if ($parser_result == null || $parser_result->items->isEmpty()) {
            $request->session()->flash("warning", trans('allianceindustry::ai-common.error_order_is_empty'));
            return redirect()->route("allianceindustry.orders");
        }

        try {
            PriceProviderSystem::getPrices($priceProvider, $parser_result->items);
        } catch (PriceProviderException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $now = now();
        $produce_until = now()->addDays($request->days);
        $price_modifier = (1 + (floatval($request->profit) / 100.0));
        $prohibitManualPricesBelowValue = !AllianceIndustrySettings::$ALLOW_PRICES_BELOW_AUTOMATIC->get(false);
        $addToSeatInventory = $request->addToSeatInventory !== null;
        $addProfitToManualPrice = $request->addProfitToManualPrices == "on";
        if (!SeatInventoryPluginHelper::pluginIsAvailable()) {
            $addToSeatInventory = false;
        }

        foreach ($parser_result->items as $item) {
            if($item->manualPrice) {
                $item->price = $item->manualPrice * $item->amount;
            }

            if ($item->manualPrice !== null && $item->manualPrice < $item->marketPrice && $prohibitManualPricesBelowValue) {
                $item->price = $item->marketPrice;
            }

            if ($item->manualPrice == null || $addProfitToManualPrice) {
                $item->price *= $price_modifier;
            }
        }

        if ($request->splitOrders === null) {
            //group mode
            $price = 0;
            foreach ($parser_result->items as $item){
                $price += $item->price; // amount is multiplied in price providers
            }

            $order = new Order();
            //TODO quantities
            $order->quantity = 1;
            $order->user_id = auth()->user()->id;
            $order->price = $price;
            $order->location_id = $request->location;
            $order->created_at = $now;
            $order->produce_until = $produce_until;
            $order->add_seat_inventory = $addToSeatInventory;
            $order->profit = floatval($request->profit);
            $order->priority = $request->priority;
            $order->priceProvider = $priceProvider;

            $repetition = intval($request->repetition);
            if ($repetition > 0) {
                Gate::authorize("allianceindustry.create_repeating_orders");
                $order->is_repeating = true;
                $order->repeat_interval = $repetition;
                $order->repeat_date = now();
            }

            $order->save();

            foreach ($parser_result->items as $item) {
                $model = new OrderItem();
                $model->order_id = $order->id;
                $model->type_id = $item->typeModel->typeID;
                $model->quantity = $item->amount;
                $model->save();
            }
        } else {
            //classical mode
            foreach ($parser_result->items as $item) {
                $order = new Order();
                $order->quantity = $item->amount;
                $order->user_id = auth()->user()->id;
                $order->price = $item->price; // if multiple items, this is already multiplied in the price provider
                $order->location_id = $request->location;
                $order->created_at = $now;
                $order->produce_until = $produce_until;
                $order->add_seat_inventory = $addToSeatInventory;
                $order->profit = floatval($request->profit);
                $order->priority = $request->priority;
                $order->priceProvider = $priceProvider;

                //this is duplicated
                $repetition = intval($request->repetition);
                if ($repetition > 0) {
                    Gate::authorize("allianceindustry.create_repeating_orders");
                    $order->is_repeating = true;
                    $order->repeat_interval = $repetition;
                    $order->repeat_date = now();
                }

                $order->save();

                $order_item = new OrderItem();
                $order_item->order_id = $order->id;
                $order_item->type_id = $item->typeModel->typeID;
                $order_item->quantity = 1;
                $order_item->save();
            }
        }


        //send notification that orders have been put up. We don't do it in an observer so it only gets triggered once
        SendOrderNotifications::dispatch()->onQueue('notifications');

        // update repeating orders
        UpdateRepeatingOrders::dispatch();

        $request->session()->flash("success", trans('allianceindustry::ai-orders.create_order_success'));
        return redirect()->route("allianceindustry.orders");
    }

    public function extendOrderTime(Request $request)
    {
        $request->validate([
            "order" => "required|integer"
        ]);
        $order = Order::find($request->order);
        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->back();
        }

        Gate::authorize("allianceindustry.same-user", $order->user_id);

        $order->produce_until = carbon($order->produce_until)->addWeeks(1);
        $order->save();

        $request->session()->flash("success", trans('allianceindustry::ai-orders.update_time_success'));
        return redirect()->back();
    }

    public function updateOrderPrice(Request $request)
    {
        $request->validate([
            "order" => "required|integer"
        ]);
        $order = Order::find($request->order);
        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->back();
        }

        Gate::authorize("allianceindustry.same-user", $order->user_id);

        $profit_multiplier = 1 + ($order->profit / 100.0);
        $item_list = $order->items->map(function ($item) {
            return $item->toEveItem();
        });

        //null is only after update, so don't use the setting
        $priceProvider = $order->priceProvider;
        if ($priceProvider === null) {
            return redirect()->back()->with('error', trans('allianceindustry::ai-common.error_obsolete_order'));
        }

        try {
            PriceProviderSystem::getPrices($priceProvider, $item_list);
        } catch (PriceProviderException $e) {
            return redirect()->back()->with('error', trans('allianceindustry::ai-common.error_price_provider_get_prices', ['message' => $e->getMessage()]));
        }

        $price = 0;
        foreach ($item_list as $item) {
            $price += $item->amount * $item->price;
        }
        $price *= $profit_multiplier;

        $order->price = $price;
        $order->save();

        $request->session()->flash("success", trans('allianceindustry::ai-orders.update_price_success'));
        return redirect()->back();
    }

    public function orderDetails($id, Request $request)
    {
        $order = Order::with("deliveries")->find($id);

        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->route("allianceindustry.orders");
        }

        return view("allianceindustry::orderDetails", compact("order"));
    }

    public function addDelivery($orderId, Request $request)
    {
        $request->validate([
            "quantity" => "required|integer"
        ]);

        $order = Order::find($orderId);
        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->route("allianceindustry.orders");
        }

        if ($order->is_repeating) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_delivery_not_assignable_to_repeating_order'));
            return redirect()->route("allianceindustry.orders");
        }

        //quantity > 0
        if ($request->quantity < 1) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_no_quantity_provided'));
            return redirect()->route("allianceindustry.orders");
        }

        //quantity <= max remaining
        if ($request->quantity > $order->quantity - $order->assignedQuantity()) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_too_much_quantity_provided'));
            return redirect()->route("allianceindustry.orders");
        }

        $delivery = new Delivery();
        $delivery->order_id = $order->id;
        $delivery->user_id = auth()->user()->id;
        $delivery->quantity = $request->quantity;
        $delivery->completed = false;
        $delivery->accepted = now();

        $delivery->save();

        $request->session()->flash("success", trans('allianceindustry::ai-deliveries.delivery_creation_success'));
        return redirect()->back();
    }

    public function setDeliveryState($orderId, Request $request)
    {
        $request->validate([
            "delivery" => "required|integer",
            "completed" => "required|boolean"
        ]);

        $delivery = Delivery::find($request->delivery);

        Gate::authorize("allianceindustry.same-user", $delivery->user_id);

        if ($request->completed) {
            $delivery->completed_at = now();
            $delivery->completed = true;
        } else {
            $delivery->completed_at = null;
            $delivery->completed = false;
        }
        $delivery->save();

        return redirect()->back();
    }

    public function deleteDelivery($orderId, Request $request)
    {
        $request->validate([
            "delivery" => "required|integer",
        ]);

        $delivery = Delivery::find($request->delivery);

        if ($delivery) {
            Gate::authorize("allianceindustry.same-user", $delivery->user_id);

            if ($delivery->completed) {
                Gate::authorize("allianceindustry.admin");
            }

            $delivery->delete();

            $request->session()->flash("success", trans('allianceindustry::ai-deliveries.delivery_removal_success'));
        } else {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_delivery_not_found'));
        }

        return redirect()->back();
    }

    public function deliveries()
    {
        $user_id = auth()->user()->id;

        $deliveries = Delivery::with("order")->where("user_id", $user_id)->get();

        return view("allianceindustry::deliveries", compact("deliveries"));
    }

    public function deleteOrder(Request $request)
    {
        $request->validate([
            "order" => "required|integer"
        ]);

        $order = Order::find($request->order);
        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->route("allianceindustry.orders");
        }

        Gate::authorize("allianceindustry.same-user", $order->user_id);

        if (!$order->deliveries->isEmpty() && !$order->completed && !auth()->user()->can("allianceindustry.admin")) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_deleted_in_progress_order'));
            return redirect()->route("allianceindustry.orders");
        }

        $order->delete();

        $request->session()->flash("success", trans('allianceindustry::ai-orders.close_order_success'));
        return redirect()->route("allianceindustry.orders");
    }

    public function about()
    {
        return view("allianceindustry::about");
    }

    public function settings()
    {
        $stations = UniverseStation::all();
        $structures = UniverseStructure::all();

        $defaultOrderLocation = AllianceIndustrySettings::$DEFAULT_ORDER_LOCATION->get(60003760);
        $mpp = AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->get(2.5);
        $orderCreationPingRoles = implode(" ", AllianceIndustrySettings::$ORDER_CREATION_PING_ROLES->get([]));
        $allowPriceBelowAutomatic = AllianceIndustrySettings::$ALLOW_PRICES_BELOW_AUTOMATIC->get(false);
        $allowPriceProviderSelection = AllianceIndustrySettings::$ALLOW_PRICE_PROVIDER_SELECTION->get(false);


        $default_price_provider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get();
        //dd($default_price_provider);

        $removeExpiredDeliveries = AllianceIndustrySettings::$REMOVE_EXPIRED_DELIVERIES->get(false);

        return view("allianceindustry::settings", compact("removeExpiredDeliveries", "allowPriceProviderSelection", "default_price_provider", "mpp", "orderCreationPingRoles", "allowPriceBelowAutomatic", "stations", "structures", "defaultOrderLocation"));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            "minimumprofitpercentage" => "required|numeric|min:0",
            "pingRolesOrderCreation" => "string|nullable",
            "allowPriceBelowAutomatic" => "nullable|in:on",
            "defaultLocation" => "required|integer",
            "defaultPriceProvider" => "required|integer",
            "allowPriceProviderSelection" => "nullable|in:on",
            "removeExpiredDeliveries" => "nullable|in:on",
        ]);

        $roles = [];
        if ($request->pingRolesOrderCreation) {
            //parse roles
            $roles = preg_replace('~\R~u', "\n", $request->pingRolesOrderCreation);
            $matches = [];
            preg_match_all("/\d+/m", $roles, $matches);
            $roles = $matches[0];
        }

        AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->set((int)$request->defaultPriceProvider);

        AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->set(floatval($request->minimumprofitpercentage));
        AllianceIndustrySettings::$ORDER_CREATION_PING_ROLES->set($roles);
        AllianceIndustrySettings::$ALLOW_PRICES_BELOW_AUTOMATIC->set(boolval($request->allowPriceBelowAutomatic));
        AllianceIndustrySettings::$DEFAULT_ORDER_LOCATION->set($request->defaultLocation);
        AllianceIndustrySettings::$ALLOW_PRICE_PROVIDER_SELECTION->set(boolval($request->allowPriceProviderSelection));
        AllianceIndustrySettings::$REMOVE_EXPIRED_DELIVERIES->set(boolval($request->removeExpiredDeliveries));

        $request->session()->flash("success", trans('allianceindustry::ai-settings.update_settings_success'));
        return redirect()->route("allianceindustry.settings");
    }

    public function deleteCompletedOrders()
    {
        $orders = Order::where("user_id", auth()->user()->id)->where("completed", true)->where("is_repeating", false)->get();
        foreach ($orders as $order) {
            $order->delete();
        }

        return redirect()->back();
    }

    public function buildTimePriceProviderConfiguration(Request $request)
    {
        $existing = PriceProviderInstance::find($request->id);

        $id = $request->id;
        $name = $existing->name ?? $request->name ?? '';
        $reaction_multiplier = $existing->configuration['reactions'] ?? 1;
        $manufacturing_multiplier = $existing->configuration['manufacturing'] ?? 1;

        return view('allianceindustry::priceprovider.buildTimeConfiguration', compact('id', 'name', 'reaction_multiplier', 'manufacturing_multiplier'));
    }

    public function buildTimePriceProviderConfigurationPost(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string',
            'manufacturing' => 'required|integer',
            'reactions' => 'required|integer',
        ]);

        $model = PriceProviderInstance::findOrNew($request->id);
        $model->name = $request->name;
        $model->backend = 'recursivetree/seat-alliance-industry/build-time';
        $model->configuration = [
            'reactions' => (int)$request->reactions,
            'manufacturing' => (int)$request->manufacturing,
        ];
        $model->save();

        return redirect()->route('pricescore::settings')->with('success', trans('allianceindustry::ai-common.price_provider_create_success'));
    }
}