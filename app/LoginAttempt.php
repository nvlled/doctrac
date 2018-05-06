<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    static function record($username, $ipaddr, $success) {
        $attempt = new \App\LoginAttempt();
        $attempt->username = $username;
        $attempt->ipaddr = $ipaddr;
        $attempt->success = $success;
        $attempt->save();
    }

    static function fail($username, $ipaddr) {
        self::record($username, $ipaddr, false);
    }

    static function success($username, $ipaddr) {
        self::record($username, $ipaddr, true);
    }

    static function countFailed($username, $ipaddr) {
        $attempts = \App\LoginAttempt
            ::where("username", $username)
            ->where("ipaddr",   $ipaddr)
            ->orderByDesc("created_at")
            ->limit(1024)
            ->get();

        $count = 0;
        foreach ($attempts as $att) {
            if ($att->success)
                break;
            $count++;
        }
        return $count;
    }
}
