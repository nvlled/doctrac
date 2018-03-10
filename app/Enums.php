<?php
namespace App;

class Enums {
    public static $privilege = ["admin", "office", "agent"];

    public static $classification = ["open", "confidential"];

    public static function approvalStates() {
        return [
            newObject(
                "icon", "✓",
                "name", "affirmed"
            ),
            newObject(
                "icon", "✗",
                "name", "rejected"
            ),
        ];
    }
}
