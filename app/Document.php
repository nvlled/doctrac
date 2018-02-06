<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Document extends Model
{
    public function create($user, $data) {
    }
    public function finalRouteIds() {
        $sql = "
            select pathId, max(id) as id
            from `document_routes`
            where trackingId=? and final=1
            group by `pathId` order by `arrivalTime`, id desc
        ";

        $ids = [];
        $rows = \DB::select($sql, [$this->trackingId]);
        foreach ($rows as $row) {
            $ids[] = $row->id;
        }
        return $ids;
    }

    public function finalRoutes() {
        return DocumentRoute::whereIn("id", $this->finalRouteIds())->get();
    }

    public function finalOffices() {
        return mapFilter($this->finalRoutes(), function($route) {
            return $route->office;
        });
    }

    // for serial routes, there is at most one route id
    // for parallel routes, there may be more than one
    public function currentRouteIds() {
        $sql = "
            select pathId, max(id) as id
            from `document_routes`
            where trackingId=? and arrivalTime is not null
            group by `pathId` order by `arrivalTime`, id desc
        ";

        $ids = [];
        $rows = \DB::select($sql, [$this->trackingId]);
        foreach ($rows as $row) {
            $ids[] = $row->id;
        }
        return $ids;
    }

    public function currentRoutes() {
        return DocumentRoute::whereIn("id", $this->currentRouteIds())->get();
    }

    public function currentOffices() {
        return mapFilter($this->currentRoutes(), function($route) {
            return $route->office;
        });
    }

    public function nextRoutes() {
        return mapFilter($this->currentRoutes(), function($route) {
            return $route->nextRoute;
        });
    }

    public function nextOffices() {
        return mapFilter($this->nextRoutes(), function($route) {
            return $route->office;
        });
    }

    public function prevRoutes() {
        return mapFilter($this->currentRoutes(), function($route) {
            return $route->prevRoute;
        });
    }

    public function prevOffices() {
        return mapFilter($this->prevRoutes(), function($route) {
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

