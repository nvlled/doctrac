<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficeMobileNumber extends Model
{
    public function office() {
        return $this->belongsTo("App\Office", "officeId");
    }
}
