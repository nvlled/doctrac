<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    public function status() {
        if (this->timeSent == null)
            return "pending";
        if (this->timeRecv == null)
            return "intransit";
        if (!this->done)
            return "processing"
        return "done"
    }

    public function next() {
        return null;
    }
}
