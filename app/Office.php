<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Office extends Model
{
    protected $appends = ["campus_name", "campus_code"];
    protected $hidden = ["campus"];

    function campus() {
        return $this->hasOne("App\Campus", "id", "campusId");
    }

    function getCampusCodeAttribute() {
        return optional($this->campus)->code;
    }

    function getCampusNameAttribute() {
        return optional($this->campus)->name;
    }

    function holdsDoc($doc) {
        foreach ($doc->currentOffices() as $office) {
            if ($this->id == $office->id)
                return true;
        }
        return false;
    }

    function actionForStatus($status) {
        switch ($status) {
            case "processing": return "send";
            case "delivering": return "abort";
            case "waiting": return "recv";
        }
        return "";
    }

    public function actionFor($doc) {
        foreach($doc->currentRoutes() as $route) {
            if ($this->id == $route->officeId) {
                $action = $this->actionForStatus($route->status);
                if ($action)
                    return $action;
            }
        }
        foreach($doc->nextRoutes() as $route) {
            if ($this->id == $route->officeId) {
                $action = $this->actionForStatus($route->status);
                if ($action)
                    return $action;
            }
        }
        return "";
    }

    public function getReceivingRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->status == "waiting")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function getDispatchedRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->status == "delivering")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function getFinalRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->final && $route->status == "done")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function getActiveRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->status == "processing")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function isFinal($doc) {
        foreach ($doc->finalRoutes() as $route) {
            if ($this->officeId == $route->officeId)
                return true;
        }
        return false;
    }

    public function canAbortSend($doc) {
        foreach ($doc->currentRoutes() as $route) {
            $nextRoute = $route->nextRoute;
            if ($route->officeId == $this->id &&
                $nextRoute && $nextRoute->receiverId == null)
                return true;
        }
        return false;
    }

    public function canSendDoc($doc) {
        return $this->holdsDoc($doc)
            && !$this->isFinal($doc);
    }

    public function canReceiveDoc($doc) {
        foreach ($doc->currentRoutes() as $route) {
            $next = $route->nextRoute;
            if (optional($next)->officeId == $this->id)
                return true;
        }
        return false;
    }

    public function getCompleteNameAttribute() {
        return "{$this->campus_name} {$this->name}";
    }

    public function validate() {
        return Validator::make($this->toArray(), [
            'name'     => 'required',
            'campusId' => 'required',
        ]);
    }

    // Note: I'm not sure if the 4 digit tracking number
    // should be shared across campuses
    public function generateTrackingID() {
        $now = now();
        $num = TrackingCounter::nextId();
        return "{$this->campus_code}-{$now->year}-$num";
    }

    public function isLinkedTo($office) {
        if (!$office)
            return false;
        return ($this->gateway && $office->gateway)
            || ($this->campusId == $office->campusId);
    }

    public function nextOffices() {
        $campusId = $this->campusId;
        if ($this->gateway) {
            // get all local offices and
            // all gateway (i.e. records) offices
            // except the current one
            return self::query()
                ->where("id", "<>", $this->id)
                ->where(function($query) use ($campusId) {
                    $query->where("campusId", $campusId)
                          ->orWhere("gateway", true);
                })
                ->get();
        } else {
            // get all local offices
            // except the current one
            return self::query()
                ->where("id", "<>", $this->id)
                ->where("campusId", $campusId)
                ->get();
        }
    }
}


