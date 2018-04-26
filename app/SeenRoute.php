<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeenRoute extends Model
{
    public $appends = ["full_name"];
    public $hidden = ["route", "user"];

    public function srcRoute() {
        return $this->hasOne("App\DocumentRoute", "id", "srcRouteId");
    }

    public function dstRoute() {
        return $this->hasOne("App\DocumentRoute", "id", "dstRouteId");
    }

    public function getOfficeAttribute() {
        return $this->dstRoute->office;
    }

    public function getFullNameAttribute() {
        return optional($this->user)->full_name;
    }
    //
}
