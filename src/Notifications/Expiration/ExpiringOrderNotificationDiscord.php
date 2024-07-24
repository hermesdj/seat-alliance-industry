<?php

namespace RecursiveTree\Seat\AllianceIndustry\Notifications\Expiration;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use RecursiveTree\Seat\AllianceIndustry\Helpers\AllianceIndustryHelper;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class ExpiringOrderNotificationDiscord extends AbstractDiscordNotification implements ShouldQueue
{
    use SerializesModels;

    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    protected function populateMessage(DiscordMessage $message, $notifiable)
    {
        $order = $this->order;

        $message->success()
            ->embed(function (DiscordEmbed $embed) use ($order) {
                $charId = $order->user->main_character->id;

                $embed
                    ->author($order->user->name, "https://images.evetech.net/characters/$charId/portrait?size=64")
                    ->title(trans('allianceindustry::ai-orders.notifications.expiring_order', ['code' => $order->order_id]), route('allianceindustry.orderDetails', ['id' => $order->id]))
                    ->description(trans('allianceindustry::ai-orders.notifications.expiring_message', ['remaining' => CarbonInterval::seconds(Carbon::now()->diffInSeconds($order->produce_until))]))
                    ->field(trans('allianceindustry::ai-orders.notifications.reference'), $order->reference)
                    ->field(trans('allianceindustry::ai-orders.notifications.order_price'), AllianceIndustryHelper::formatNumber($order->totalValue()) . ' ISK')
                    ->field(trans('allianceindustry::ai-orders.notifications.nb_items'), $order->items->count())
                    ->field(trans('allianceindustry::ai-orders.notifications.location'), $order->location()->name);
            });
    }
}