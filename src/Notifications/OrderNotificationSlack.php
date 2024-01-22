<?php

namespace RecursiveTree\Seat\AllianceIndustry\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use RecursiveTree\Seat\TreeLib\Helpers\PrioritySystem;
use Seat\Notifications\Notifications\AbstractSlackNotification;

class OrderNotificationSlack extends AbstractSlackNotification implements ShouldQueue
{
    use SerializesModels;

    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function populateMessage(SlackMessage $message, $notifiable)
    {
        $orders = $this->orders;

        $pings = implode(" ", array_map(function ($role) {
            return "<@&$role>";
        }, AllianceIndustrySettings::$ORDER_CREATION_PING_ROLES->get([])));

        $message
            ->success()
            ->from(trans('allianceindustry::ai-config.notification_from'));

        if ($pings !== "") {
            $message->content($pings);
        }

        $message->attachment(function ($attachment) use ($orders) {
            $attachment
                ->title(trans('allianceindustry::ai-config.seat_alliance_industry_new_order_notification'), route("allianceindustry.orders"));
            foreach ($orders as $order) {
                $item_text = OrderItem::formatOrderItemsList($order);
                $location = $order->location()->name;
                $price = number_metric($order->price);
                $totalPrice = number_metric($order->price * $order->quantity);
                $priorityName = PrioritySystem::getPriorityData()[$order->priority]["name"];
                $priority = $priorityName ? trans("allianceindustry::ai-orders.priority_$priorityName") : trans("seat.web.unknown");

                $attachment->field(function ($field) use ($item_text, $priority, $totalPrice, $price, $location) {
                    $field
                        ->long()
                        ->title($item_text)
                        ->content(trans('allianceindustry::ai-common.notifications_field_description', [
                            'priority' => $priority,
                            'price' => $price,
                            'totalPrice' => $totalPrice,
                            'location' => $location
                        ]));
                });
            }
        });
        return $message;
    }
}