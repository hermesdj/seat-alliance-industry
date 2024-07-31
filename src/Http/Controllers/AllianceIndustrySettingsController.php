<?php

namespace RecursiveTree\Seat\AllianceIndustry\Http\Controllers;

use Illuminate\Http\Request;
use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Web\Http\Controllers\Controller;

class AllianceIndustrySettingsController extends Controller
{
    public function settings()
    {
        $stations = UniverseStation::all();
        $structures = UniverseStructure::all();

        $defaultOrderLocation = AllianceIndustrySettings::$DEFAULT_ORDER_LOCATION->get(60003760);
        $mpp = AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->get(2.5);
        $orderCreationPingRoles = implode(" ", AllianceIndustrySettings::$ORDER_CREATION_PING_ROLES->get([]));
        $allowPriceBelowAutomatic = AllianceIndustrySettings::$ALLOW_PRICES_BELOW_AUTOMATIC->get(false);


        $default_price_provider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get();
        //dd($default_price_provider);

        $removeExpiredDeliveries = AllianceIndustrySettings::$REMOVE_EXPIRED_DELIVERIES->get(false);

        $allowedPriceProviders = AllianceIndustrySettings::$ALLOWED_PRICE_PROVIDERS->get([$default_price_provider]);

        return view(
            "allianceindustry::settings",
            compact(
                "removeExpiredDeliveries",
                "default_price_provider",
                "mpp",
                "orderCreationPingRoles",
                "allowPriceBelowAutomatic",
                "stations",
                "structures",
                "defaultOrderLocation",
                "allowedPriceProviders"
            )
        );
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            "minimumprofitpercentage" => "required|numeric",
            "pingRolesOrderCreation" => "string|nullable",
            "allowPriceBelowAutomatic" => "nullable|in:on",
            "defaultLocation" => "required|integer",
            "defaultPriceProvider" => "required|integer",
            "removeExpiredDeliveries" => "nullable|in:on",
            "priceProviderWhitelist.*" => "integer"
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
        AllianceIndustrySettings::$REMOVE_EXPIRED_DELIVERIES->set(boolval($request->removeExpiredDeliveries));
        AllianceIndustrySettings::$ALLOWED_PRICE_PROVIDERS->set($request->priceProviderWhitelist);

        $request->session()->flash("success", trans('allianceindustry::ai-settings.update_settings_success'));
        return redirect()->route("allianceindustry.settings");
    }
}