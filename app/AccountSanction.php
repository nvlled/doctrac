<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;

class AccountSanction extends Model
{
    function getIsExpiredAttribute() {
        $expire_at = $this->expire_at;
        if ( ! $expire_at)
            return false;
        $isExpired = Carbon::now()->greaterThan(new Carbon($expire_at));
        return $isExpired;
    }

    function getMinutesLeftAttribute() {
         $minutes = (new Carbon($this->expire_at))->diffInSeconds(Carbon::now())/60;
         return round($minutes, 2);
    }


    static function getAll($username) {
        return $sanctions = AccountSanction
            ::where("username", $username)
            ->get();
    }

    static function getActive($username, $ip) {
        $sanctions = AccountSanction
            ::where("username", $username)
            ->where("active", true)
            ->orWhere("ipaddr", $ip)
            ->get();

        $active = collect();
        foreach ($sanctions as $sanc) {
            if ($sanc->isExpired) {
                $sanc->active = false;
                $sanc->save();
            } else {
                $active->push($sanc);
            }
        }
        return $active;
    }

    static function isDisabled($username) {
        return AccountSanction::getActive($username)->count() > 0;
    }

    static function disable($username, $reason, $ipaddr="127.0.0.1", $minutes=NULL) {
        if (!$minutes) {
            $minutes = \App\Config::$accountBanMinutes;
        }
        $sanction = new AccountSanction();
        $sanction->username  = $username;
        $sanction->reason    = $reason;
        $sanction->active    = true;
        $sanction->ipaddr    = $ipaddr;
        $sanction->expire_at = Carbon::now()->addMinutes($minutes);
        $sanction->save();
    }
}
