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

    public static function seen($srcRoute) {
        if (!$srcRoute)
            return;
        $dstRoute = $srcRoute->nextRoute;
        $office = $dstRoute->office;
        foreach ($office->getMembers() as $user) {
            $user->notify(new DocumentAction(
                "seen",
                $srcRoute,
                $office
            ));
        }
    }

    public static function received($srcRoute) {
        if (!$srcRoute)
            return;
        $dstRoute = $srcRoute->nextRoute;
        $office = $dstRoute->office;
        foreach ($office->getMembers() as $user) {
            $user->notify(new DocumentAction(
                "received",
                $srcRoute,
                $office
            ));
        }
    }

    public static function sent($srcRoute) {
        if (!$srcRoute)
            return;
        $dstRoute = $srcRoute->nextRoute;
        $office = $srcRoute->office;
        foreach ($office->getMembers() as $user) {
            $user->notify(new DocumentAction(
                "sent",
                $dstRoute,
                $office
            ));
        }
    }

}

