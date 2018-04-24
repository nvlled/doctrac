<?php

namespace App;
class Config {
    public static $upload_dir = "uploads";
    public static $randIDLen = 8;

    const PAGE_SIZE = 15;

    public static $searchRetryLimit = 5;
    public static $searchRetryTime = 10; // minutes

    public static $notifPageSize = 15; // minutes
}
