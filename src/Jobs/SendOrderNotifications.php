<?php

namespace RecursiveTree\Seat\AllianceIndustry\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use RecursiveTree\Seat\AllianceIndustry\Models\Order;
use Seat\Notifications\Models\NotificationGroup;
use Seat\Notifications\Traits\NotificationDispatchTool;


class SendOrderNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationDispatchTool;

    public function tags(): array
    {
        return ["seat-alliance-industry", "order", "notifications"];
    }

    public function handle(): void
    {
        $now = now();
        $last_notifications = AllianceIndustrySettings::$LAST_NOTIFICATION_BATCH->get();

        if ($last_notifications === null) {
            $orders = Order::where('confirmed', true)->get();
        } else {
            $orders = Order::where("created_at", ">=", $last_notifications)
                ->where('confirmed', true)
                ->get();
        }

        if (!$orders->isEmpty()) {
            foreach ($orders as $order) {
                $this->dispatchNotification($order);
            }
        }

        $last_expiring = AllianceIndustrySettings::$LAST_EXPIRING_NOTIFICATION_BATCH->get();

        if ($last_expiring === null) {
            $expiring_orders = Order::where("confirmed", true)
                ->where('completed', false)
                ->where('produce_until', '<', $now->addHours(24))
                ->get();
        } else {
            $expiring_orders = Order::where("confirmed", true)
                ->where('completed', false)
                ->where('produce_until', '<', $last_expiring->addHours(24))
                ->get();
        }

        if (!$expiring_orders->isEmpty()) {
            foreach ($expiring_orders as $order) {
                $this->dispatchExpiringOrderNotification($order);
            }
        }

        AllianceIndustrySettings::$LAST_NOTIFICATION_BATCH->set($now);
        AllianceIndustrySettings::$LAST_EXPIRING_NOTIFICATION_BATCH->set($now->addHours(24));
    }

    //stolen from https://github.com/eveseat/notifications/blob/master/src/Observers/UserObserver.php
    private function dispatchNotification($order): void
    {
        $groups = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) {
                $query->where('alert', 'seat_alliance_industry_new_order_notification');
            })->get();

        $this->dispatchNotifications("seat_alliance_industry_new_order_notification", $groups, function ($constructor) use ($order) {
            return new $constructor($order);
        });
    }

    private function dispatchExpiringOrderNotification($order): void
    {
        $groups = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) {
                $query->where('alert', 'seat_alliance_industry_expiring_order_notification');
            })->get();

        $this->dispatchNotifications("seat_alliance_industry_expiring_order_notification", $groups, function ($constructor) use ($order) {
            return new $constructor($order);
        });
    }
}