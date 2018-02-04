<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Document extends Model
{
    public function create($user, $data) {
    }

    public function finalRoutes() {
        return App\DocumentRoute
            // TODO: I don't think a final flag is
            // needed if I could establish the following invariants:
            // prevId of start of routes is always null
            // next   of final of routes is always null
            ::where("final", true)
            ->groupBy("routeId")
            ->get();
    }

    public function currentRoutes() {
        return App\DocumentRoute
            ::whereNull("userId")
            ->whereNull("arrivalTime")
            ->orderBy("arrivalTime", "desc")
            ->groupBy("routeId");
            ->get();
    }

    public function nextRoutes() {
        return $this->currentRoutes(function($route) {
            return $route->next();
        });
    }

    public function currentOffices() {
        return $this->currentRoutes(function($route) {
            return $route->office;
        });
    }

    public function nextOffices() {
        return $this->nextRoutes(function($route) {
            return $route->office;
        });
    }

    public function validate() {
        return Validator::make($this->toArray(), [
            'title'  => 'required',
            'trackingId'  => 'required',
            'userId' => 'required|exists:users,id',
        ]);
    }
}
