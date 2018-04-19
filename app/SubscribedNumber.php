<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscribedNumber extends Model
{
    public static function isNumberActive($number) {
        $number = \App\GlobeAPI::trimPrefix($number);
        return optional(self::where("subscriberNumber", $number)->first())->active;
    }
}
