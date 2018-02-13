<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Office extends Model
{
    public function holdsDoc($doc) {
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

    // TODO:
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

    public function validate() {
        return Validator::make($this->toArray(), [
            'name'   => 'required',
            'campus' => 'required',
        ]);
    }
}
