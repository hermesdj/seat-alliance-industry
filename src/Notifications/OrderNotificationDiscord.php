<?php

namespace RecursiveTree\Seat\AllianceIndustry\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use RecursiveTree\Seat\TreeLib\Helpers\PrioritySystem;
use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Notifications\AbstractNotification;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbedField;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class OrderNotificationDiscord extends AbstractDiscordNotification implements ShouldQueue
{
    use SerializesModels;

    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }


    protected function populateMessage(DiscordMessage $message, $notifiable)
    {
        $message->content(trans('allianceindustry::ai-config.new_orders_are_available'));

        $displayed = $this->orders;
        $showMoreLink = false;
        if ($this->orders->count() > 10) {
            $showMoreLink = true;
            $displayed = $this->orders->take(9);
        }

        $message->embed(function (DiscordEmbed $embed) use ($showMoreLink, $displayed) {
            foreach ($displayed as $order) {
                $item_text = OrderItem::formatOrderItemsList($order);
                $location = $order->location()->name;
                $price = number_metric($order->price);
                $totalPrice = number_metric($order->price * $order->quantity);
                $priorityName = PrioritySystem::getPriorityData()[$order->priority]["name"];
                $priority = $priorityName ? trans("allianceindustry::ai-orders.priority_$priorityName") : trans("seat.web.unknown");

                $embed->field(function (DiscordEmbedField $field) use ($totalPrice, $price, $priority, $item_text, $location) {
                    $value = trans('allianceindustry::ai-common.notifications_field_description', [
                        'priority' => $priority,
                        'price' => $price,
                        'totalPrice' => $totalPrice,
                        'location' => $location
                    ]);

                    $field->name($item_text);
                    $field->value($value);
                    $field->long();
                });
            }

            if ($showMoreLink) {
                $embed->field(function (DiscordEmbedField $field) {
                    $field->name(trans('allianceindustry::ai-common.notification_more_items'));
                    $field->value(route("allianceindustry.orders"));
                    $field->long();
                });
            }
        });
    }
}