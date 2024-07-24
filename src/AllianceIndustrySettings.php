<?php

namespace RecursiveTree\Seat\AllianceIndustry;

use Illuminate\Database\QueryException;
use RecursiveTree\Seat\TreeLib\Helpers\Setting;

class AllianceIndustrySettings
{
    public static $LAST_NOTIFICATION_BATCH;

    public static $LAST_EXPIRING_NOTIFICATION_BATCH;

    public static $MINIMUM_PROFIT_PERCENTAGE;
    public static $ORDER_CREATION_PING_ROLES;
    public static $ALLOW_PRICES_BELOW_AUTOMATIC;
    public static $DEFAULT_ORDER_LOCATION;
    public static $DEFAULT_PRICE_PROVIDER;
    public static $ALLOW_PRICE_PROVIDER_SELECTION;
    public static $REMOVE_EXPIRED_DELIVERIES;

    public static $DEFAULT_PRIORITY;


    //used in an earlier iteration of the notification system, still used in migrations
    public static $NOTIFICATION_COMMAND_SCHEDULE_ID;

    public static function init()
    {
        self::$LAST_NOTIFICATION_BATCH = Setting::create("allianceindustry", "notifications.batch.last", true);
        self::$DEFAULT_ORDER_LOCATION = Setting::create("allianceindustry", "order.location.default", true);
        self::$DEFAULT_PRICE_PROVIDER = Setting::create("allianceindustry", "order.price.provider.default", true);
        self::$ALLOW_PRICE_PROVIDER_SELECTION = Setting::create("allianceindustry", "order.price.provider.change.allowed", true);
        self::$REMOVE_EXPIRED_DELIVERIES = Setting::create("allianceindustry", "deliveries.expired.remove", true);
        self::$DEFAULT_PRIORITY = Setting::create("allianceindustry", "order.priority.default", true);

        //with manual key because it is migrated from the old settings system
        self::$MINIMUM_PROFIT_PERCENTAGE = Setting::createFromKey("recursivetree.allianceindustry.minimumProfitPercentage", true);
        self::$ORDER_CREATION_PING_ROLES = Setting::createFromKey("recursivetree.allianceindustry.orderCreationPingRoles", true);
        self::$ALLOW_PRICES_BELOW_AUTOMATIC = Setting::createFromKey("recursivetree.allianceindustry.allowPricesBelowAutomatic", true);
        self::$NOTIFICATION_COMMAND_SCHEDULE_ID = Setting::createFromKey("recursivetree.allianceindustry.notifications_schedule_id", true);

        // when migrating fresh, this error might trigger
        try {
            $price_provider = self::$DEFAULT_PRICE_PROVIDER->get();
            if (!is_numeric($price_provider)) {
                self::$DEFAULT_PRICE_PROVIDER->set(null);
            }
        } catch (QueryException $_) {
        }
    }
}