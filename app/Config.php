<?php

namespace App;
class Config {
    // TODO: make these constants
    public static $upload_dir = "uploads";
    public static $randIDLen = 8;

    const PAGE_SIZE = 15;

    public static $accountBanMinutes = 60;
    public static $loginAttempts = 3;
    public static $searchRetryLimit = 5;
    public static $searchRetryTime = 10; // minutes

    public static $notifPageSize = 15; // minutes
}
