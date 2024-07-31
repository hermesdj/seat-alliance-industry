<?php

namespace RecursiveTree\Seat\AllianceIndustry\Api;

use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class AllianceIndustryApi
{
    public static function create_orders($data){
        $location_id = $data["location"] ?? 60003760;

        // TODO toMultibuy ??
        $multibuy = $data["items"]->toMultibuy();

        $stations = UniverseStation::all();
        $structures = UniverseStructure::all();
        $mpp = AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->get(2.5);

        $default_price_provider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get();

        return view("allianceindustry::createOrder",compact("stations","default_price_provider", "structures","mpp","location_id","multibuy"));
    }
}