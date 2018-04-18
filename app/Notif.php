<?php

namespace App;

use App\Notifications\DocumentAction;

class Notif {
    public static function countUnread() {
        $user = \Auth::user();
        if (!$user)
            return 0;
        return $user->unreadNotifications()->count();
    }

    public static function seen($srcOffice, $destOffice, $route) {
        $msg = new DocumentAction(
            "seen",
            $srcOffice,
            $destOffice,
            $route
        );
        if ( ! env("DISABLE_SMS_NOTICE"))
            $srcOffice->notifySMS($msg);
        foreach ($srcOffice->getMembers() as $user) {
            $user->notify($msg);
        }
    }

    public static function received($srcOffice, $destOffice, $route) {
        $msg = new DocumentAction(
            "received",
            $srcOffice,
            $destOffice,
            $route
        );
        if ( ! env("DISABLE_SMS_NOTICE"))
            $srcOffice->notifySMS($msg);
        foreach ($srcOffice->getMembers() as $user) {
            $user->notify($msg);
        }
    }

    public static function sent($srcOffice, $destOffice, $route) {
        $msg = new DocumentAction(
            "sent",
            $srcOffice,
            $destOffice,
            $route
        );
        if ( ! env("DISABLE_SMS_NOTICE"))
            $destOffice->notifySMS($msg);
        foreach ($destOffice->getMembers() as $user) {
            $user->notify($msg);
        }
    }

    public static function rejected($srcOffice, $destOffice, $route) {
        $msg = new DocumentAction(
            "rejected",
            $srcOffice,
            $destOffice,
            $route
        );
        if ( ! env("DISABLE_SMS_NOTICE"))
            $destOffice->notifySMS($msg);
        foreach ($destOffice->getMembers() as $user) {
            $user->notify($msg);
        }
    }

    public static function completed($doc) {
        $path = \App\DoctracAPI::new()->followMainRoute($doc);
        foreach ($path as $route) {
            $msg = new DocumentAction(
                "completed",
                $route->office,
                $route->office,
                $route
            );
            if ( ! env("DISABLE_SMS_NOTICE"))
                $route->office->notifySMS($msg);
            foreach ($route->office->getMembers() as $user) {
                $user->notify($msg);
            }
        }
    }
}
