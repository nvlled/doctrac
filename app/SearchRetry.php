<?php
namespace App;

class SearchRetry {
    public static function reset() {
        \Session::put("search-retries", 0);
        \Session::put("search-retry-time", null);
    }

    public static function canReset() {
        return self::count() >= \App\Config::$searchRetryLimit
            && now()->diffInSeconds(self::time())/60 >= \App\Config::$searchRetryTime;
    }

    public static function increment() {
        $count = self::count();
        \Session::put("search-retries", ++$count);
        if (!self::time())
            \Session::put("search-retry-time", now());
        return $count;
    }

    public static function count() {
        return \Session::get("search-retries") ?? 0;
    }

    public static function time() {
        return \Session::get("search-retry-time");
    }

    public static function minutesLeft() {
        $min = now()->diffInSeconds(self::time())/60;
        $time = \App\Config::$searchRetryTime - $min;
        return round($time, 2);
    }

    public static function allowed() {
        return self::count() < \App\Config::$searchRetryLimit
            || now()->diffInMinutes(self::time()) > \App\Config::$searchRetryTime;
    }
}
