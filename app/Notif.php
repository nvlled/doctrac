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
        foreach ($srcOffice->getMembers() as $user) {
            $user->notify(new DocumentAction(
                "seen",
                $srcOffice,
                $destOffice,
                $route
            ));
        }
    }

    public static function received($srcOffice, $destOffice, $route) {
        foreach ($srcOffice->getMembers() as $user) {
            $user->notify(new DocumentAction(
                "received",
                $srcOffice,
                $destOffice,
                $route
            ));
        }
    }

    public static function sent($srcOffice, $destOffice, $route) {
        foreach ($destOffice->getMembers() as $user) {
            $user->notify(new DocumentAction(
                "sent",
                $srcOffice,
                $destOffice,
                $route
            ));
        }
    }

}

