<?php
namespace App;

class Enums {
    public static $privilege = ["admin", "office", "agent"];

    public static $classification = ["open", "confidential"];
    public static $docState = ["ongoing", "disapproved", "completed"];
    public static $approvateState = ["accepted", "rejected"];
}
