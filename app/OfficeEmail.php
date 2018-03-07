<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficeEmail extends Model
{
    //
    public function office() {
        return $this->belongsTo("App\Office", "officeId");
    }
}
