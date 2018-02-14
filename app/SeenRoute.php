<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeenRoute extends Model
{
    public $appends = ["full_name"];
    public $hidden = ["route", "user"];

    public function user() {
        return $this->hasOne("App\User", "id", "userId");
    }

    public function route() {
        return $this->hasOne("App\DocumentRoute", "id", "routeId");
    }

    public function getFullNameAttribute() {
        return optional($this->user)->full_name;
    }
    //
}
