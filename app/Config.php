<?php

namespace App;
class Config {
    public static $upload_dir = "uploads";
    public static $randIDLen = 8;

    public static $searchRetryLimit = 3;
    public static $searchRetryTime = 10; // minutes
}
