<?php

namespace RecursiveTree\Seat\AllianceIndustry\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RecursiveTree\Seat\AllianceIndustry\Helpers\AllianceIndustryHelper;
use RecursiveTree\Seat\AllianceIndustry\Models\Delivery;
use RecursiveTree\Seat\AllianceIndustry\Models\DeliveryItem;
use RecursiveTree\Seat\AllianceIndustry\Models\Order;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use Seat\Web\Http\Controllers\Controller;

class AllianceIndustryDeliveryController extends Controller
{
    private const MaxDeliveryCodeLength = 6;

    public function deliveries()
    {
        $user_id = auth()->user()->id;

        $deliveries = Delivery::with("order")->where("user_id", $user_id)->get();

        return view("allianceindustry::deliveries", compact("deliveries"));
    }

    public function deliveryDetails($id, Request $request)
    {
        $delivery = Delivery::with("deliveryItems")->find($id);

        if (!$delivery) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_delivery_not_found'));
            return redirect()->back();
        }

        return view("allianceindustry::deliveryDetails", compact("delivery"));
    }

    public function prepareDelivery($orderId, Request $request)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->route("allianceindustry.orderDetails", ['id' => $orderId]);
        }

        if ($order->corporation != null && $order->corporation->corporation_id != auth()->user()->main_character->affiliation->corporation_id) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_not_allowed_to_create_delivery'));
            return redirect()->route("allianceindustry.orderDetails", ['id' => $orderId]);
        }

        $items = $order->items->filter(function ($item) {
            return $item->availableQuantity() > 0;
        });

        return view("allianceindustry::prepareDelivery", compact("order", "items"));
    }

    public function addDelivery($orderId, Request $request)
    {
        $validated = $request->validate([
            "quantities" => "required|array",
            "quantities.*" => "required|integer|min:0"
        ]);

        $order = Order::find($orderId);
        if (!$order) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_order_not_found'));
            return redirect()->back();
        }

        if ($order->is_repeating) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_delivery_not_assignable_to_repeating_order'));
            return redirect()->route("allianceindustry.orders");
        }

        $quantities = $validated['quantities'];

        $delivery = new Delivery();
        $delivery->delivery_code = AllianceIndustryHelper::generateRandomString(self::MaxDeliveryCodeLength);
        $delivery->order_id = $order->id;
        $delivery->user_id = auth()->user()->id;
        $delivery->completed = false;
        $delivery->accepted = now();

        $deliveryItems = array();
        $totalQuantity = 0;

        foreach ($quantities as $id => $quantity) {
            $item = OrderItem::find($id);
            if ($item->order_id != $orderId) {
                $request->session()->flash("error", trans('allianceindustry::ai-common.error_item_order_id_does_not_match'));
                return redirect()->back();
            }

            if ($item->assignedQuantity() + $quantity > $item->quantity) {
                $request->session()->flash("error", trans('allianceindustry::ai-common.error_too_much_quantity_provided'));
                return redirect()->back();
            }

            if ($quantity > 0) {
                $model = new DeliveryItem();
                $model->order_id = $order->id;
                $model->order_item_id = $item->id;
                $model->completed = false;
                $model->accepted = now();
                $model->quantity_delivered = $quantity;

                $totalQuantity += $quantity;
                $deliveryItems[] = $model;
            }
        }

        if (empty($deliveryItems)) {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_delivery_not_assignable_to_repeating_order'));
            return redirect()->back();
        }

        $delivery->quantity = $totalQuantity;
        $delivery->save();

        foreach ($deliveryItems as $deliveryItem) {
            $deliveryItem->delivery_id = $delivery->id;
            $deliveryItem->save();
        }

        $request->session()->flash("success", trans('allianceindustry::ai-deliveries.delivery_creation_success'));
        return redirect()->route("allianceindustry.deliveryDetails", ['id' => $delivery->id]);
    }

    public function setDeliveryState($deliveryId, Request $request)
    {
        $request->validate([
            "completed" => "required|boolean"
        ]);

        $delivery = Delivery::find($deliveryId);

        Gate::authorize("allianceindustry.same-user", $delivery->user_id);

        if ($request->completed) {
            $delivery->completed_at = now();
            $delivery->completed = true;
        } else {
            $delivery->completed_at = null;
            $delivery->completed = false;
        }

        $delivery->save();

        // Mark all delivery items as completed too
        DeliveryItem::where('delivery_id', $delivery->id)
            ->update(['completed' => $delivery->completed, 'completed_at' => $delivery->completed_at]);

        return redirect()->back();
    }

    public function setDeliveryItemState($deliveryId, $itemId, Request $request)
    {
        $request->validate([
            "completed" => "required|boolean"
        ]);

        $delivery = Delivery::find($deliveryId);
        $item = DeliveryItem::find($itemId);

        Gate::authorize("allianceindustry.same-user", $delivery->user_id);

        if ($request->completed) {
            $item->completed_at = now();
            $item->completed = true;
        } else {
            $item->completed_at = null;
            $item->completed = false;
        }
        $item->save();

        $totalDelivered = DeliveryItem::where('delivery_id', $delivery->id)
            ->where('completed', true)
            ->sum('quantity_delivered');

        if ($totalDelivered < $delivery->totalQuantity()) {
            $delivery->completed_at = null;
            $delivery->completed = false;
        } else {
            $delivery->completed_at = now();
            $delivery->completed = true;
        }

        $delivery->save();

        return redirect()->back();
    }

    public function deleteDelivery($deliveryId, Request $request)
    {
        $delivery = Delivery::find($deliveryId);

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

        return redirect()->route("allianceindustry.deliveries");
    }

    public function deleteDeliveryItem($deliveryId, $itemId, Request $request)
    {
        $delivery = Delivery::find($deliveryId);
        $item = DeliveryItem::find($itemId);

        if ($item) {
            Gate::authorize("allianceindustry.same-user", $delivery->user_id);

            if ($item->completed) {
                Gate::authorize("allianceindustry.admin");
            }

            $item->delete();

            $request->session()->flash("success", trans('allianceindustry::ai-deliveries.delivery_item_removal_success'));
        } else {
            $request->session()->flash("error", trans('allianceindustry::ai-common.error_delivery_item_not_found'));
        }

        $delivery->quantity = $delivery->totalQuantity();
        $delivery->save();

        return redirect()->back();
    }
}