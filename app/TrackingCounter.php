<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrackingCounter extends Model
{
    const NUM_DIGITS = 4;
    static function nextId() {
        $counter = new TrackingCounter();
        $counter->save();
        return str_pad($counter->id, self::NUM_DIGITS, "0", STR_PAD_LEFT);
    }

    static function reset() {
        $success = false;
        \DB::transaction(function() use ($success) {
            $counter = new TrackingCounter();
            $tableName = $counter->getTable();
            \DB::delete("delete from $tableName");
            \DB::statement("alter table $tableName auto_increment = 1");
            $success = true;
        });
        return $success;
    }
}
