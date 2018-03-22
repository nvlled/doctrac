<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NextRoute extends Model
{
    protected $guarded = [];
    public function route() {
        return $this->hasOne("App\DocumentRoute", "id", "routeId");
    }
    //
}
