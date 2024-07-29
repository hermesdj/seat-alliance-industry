<?php

namespace RecursiveTree\Seat\AllianceIndustry\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use RecursiveTree\Seat\AllianceIndustry\Helpers\AllianceIndustryHelper;
use RecursiveTree\Seat\AllianceIndustry\Item\PriceableEveItem;
use RecursiveTree\Seat\AllianceIndustry\Jobs\SendOrderNotification;
use RecursiveTree\Seat\AllianceIndustry\Jobs\UpdateRepeatingOrders;
use RecursiveTree\Seat\AllianceIndustry\Models\Order;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use RecursiveTree\Seat\AllianceIndustry\Models\Statistics\OrderStatistic;
use RecursiveTree\Seat\PricesCore\Exceptions\PriceProviderException;
use RecursiveTree\Seat\PricesCore\Facades\PriceProviderSystem;
use RecursiveTree\Seat\TreeLib\Helpers\SeatInventoryPluginHelper;
use RecursiveTree\Seat\TreeLib\Parser\Parser;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Web\Http\Controllers\Controller;

class AllianceIndustryOrderController extends Controller
{
    private const MaxOrderIdLength = 6;

    public function orders()
    {
        $orders = Order::with("deliveries")
            ->where('confirmed', true)
            ->where("completed", false)
            ->where("produce_until", ">", DB::raw("NOW()"))
            ->where("is_repeating", false)
            ->get()
            ->filter(function ($order) {
                return $order->assignedQuantity() < $order->totalQuantity();
            })
            ->filter(function ($order) {
                return $order->corp_id == null || $order->corp_id == auth()->user()->main_character->affiliation->corporation_id || $order->user_id == auth()->user()->id;
            });

        $personalOrders = Order::where("user_id", auth()->user()->id)->get();

        $statistics = OrderStatistic::generateAll();

        return view("allianceindustry::orders", compact("orders", "personalOrders", "statistics"));
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
        $location_id = AllianceIndustrySettings::$DEFAULT_ORDER_LOCATION->get(60003760);
        //ALSO UPDATE API
        $default_price_provider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get();
        //ALSO UPDATE API
        $allowPriceProviderSelection = AllianceIndustrySettings::$ALLOW_PRICE_PROVIDER_SELECTION->get(false);

        $defaultPriority = AllianceIndustrySettings::$DEFAULT_PRIORITY->get(2);

        //ALSO UPDATE API
        return view("allianceindustry::createOrder",
            compact(
                "allowPriceProviderSelection",
                "stations",
                "structures",
                "mpp",
                "location_id",
                "default_price_provider",
                "defaultPriority"
            )
        );
    }

    protected function parsePriceProviderItems($item_array, $price_modifier = 1): array
    {
        $items = array();

        foreach ($item_array as $item) {
            $item->price = intval($item->price * $price_modifier * 100);

            $typeID = $item->typeModel->typeID;

            if (!array_key_exists($typeID, $items)) {
                $items[$typeID] = (object)[
                    'typeID' => $typeID,
                    'price' => $item->price,
                    'quantity' => $item->amount,
                    'unitPrice' => $item->price / $item->amount
                ];
            } else {
                $items[$typeID]->quantity += $item->amount;
            }
        }

        return $items;
    }

    protected function computeOrderTotal($items): int
    {
        $total_price = 0;

        foreach ($items as $item) {
            $total_price += $item->price;
        }

        return $total_price;
    }

    public function submitOrder(Request $request)
    {
        $request->validate([
            "items" => "required|string",
            "quantity" => "required|integer|min:1",
            "profit" => "required|numeric|min:0",
            "days" => "required|integer|min:1",
            "location" => "required|integer",
            "addToSeatInventory" => "nullable|in:on",
            "priority" => "integer",
            "priceprovider" => "nullable|integer",
            "repetition" => "nullable|integer",
            "reference" => "nullable|string|max:32"
        ]);

        if (!$request->priority) $request->priority = 2;

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
        // TODO Move to its own function $prohibitManualPricesBelowValue = !AllianceIndustrySettings::$ALLOW_PRICES_BELOW_AUTOMATIC->get(false);
        $addToSeatInventory = $request->addToSeatInventory !== null;

        if (!SeatInventoryPluginHelper::pluginIsAvailable()) {
            $addToSeatInventory = false;
        }

        $items = $this->parsePriceProviderItems($parser_result->items, $price_modifier);

        // Create order
        $total_price = $this->computeOrderTotal($items);

        $order = new Order();
        $order->order_id = AllianceIndustryHelper::generateRandomString(self::MaxOrderIdLength);

        if ($request->reference != null) {
            $order->reference = $request->reference;
        } else {
            if ($parser_result->shipName != null) {
                $order->reference = $parser_result->shipName;
            } else {
                $order->reference = $order->order_id;
            }
        }

        $order->quantity = $request->quantity;
        $order->user_id = auth()->user()->id;
        $order->price = $total_price * $order->quantity;
        $order->location_id = $request->location;
        $order->created_at = $now;
        $order->produce_until = $produce_until;
        $order->add_seat_inventory = $addToSeatInventory;
        $order->profit = floatval($request->profit);
        $order->priority = $request->priority;
        $order->priceProvider = $priceProvider;
        $order->confirmed = false;

        $repetition = intval($request->repetition);
        if ($repetition > 0) {
            Gate::authorize("allianceindustry.create_repeating_orders");
            $order->is_repeating = true;
            $order->repeat_interval = $repetition;
            $order->repeat_date = now();
        }

        $order->save();

        foreach ($items as $item) {
            $model = new OrderItem();
            $model->order_id = $order->id;
            $model->type_id = $item->typeID;
            $model->quantity = $item->quantity * $order->quantity;
            $model->unit_price = $item->unitPrice;
            $model->save();
        }

        // update repeating orders
        UpdateRepeatingOrders::dispatch();

        $request->session()->flash("success", trans('allianceindustry::ai-orders.create_order_success'));
        return redirect()->route("allianceindustry.orders");
    }

    public function confirmOrder($orderId, Request $request)
    {
        $order = Order::find($orderId);

        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->route("allianceindustry.orders");
        }

        $order->confirmed = true;
        $order->save();

        //send notification that orders have been put up. We don't do it in an observer so it only gets triggered once
        SendOrderNotification::dispatch($order);

        return redirect()->back();
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

        $order->produce_until = carbon($order->produce_until)->addWeeks();
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

        $items = $this->parsePriceProviderItems($item_list, $profit_multiplier);

        $order->price = $this->computeOrderTotal($items);
        $order->save();

        foreach ($items as $item) {
            OrderItem::where('order_id', $order->id)
                ->where('type_id', $item->typeID)
                ->update(array(
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unitPrice)
                );
        }

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

    public function deleteCompletedOrders()
    {
        $orders = Order::where("user_id", auth()->user()->id)->where("completed", true)->where("is_repeating", false)->get();
        foreach ($orders as $order) {
            $order->delete();
        }

        return redirect()->back();
    }

    public function toggleReserveCorp($orderId, Request $request)
    {
        $order = Order::find($orderId);

        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->route("allianceindustry.orders");
        }

        if ($order->corp_id) {
            $order->corp_id = null;
        } else {
            $order->corp_id = auth()->user()->main_character->affiliation->corporation_id;
        }

        $order->save();

        return redirect()->back();
    }
}