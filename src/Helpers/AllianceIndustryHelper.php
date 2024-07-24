<?php

namespace RecursiveTree\Seat\AllianceIndustry\Helpers;

use Seat\Services\Settings\Profile;

class AllianceIndustryHelper
{
    public static function generateRandomString(int $length = 25): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function formatNumber($value, $decimals = 2): string
    {
        $thousandSeparator = Profile::get('thousand_separator');
        $decimalSeparator = Profile::get('decimal_separator');

        return number_format($value, $decimals, $decimalSeparator, $thousandSeparator);
    }
}