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

    public function startingRoutes() {
        return DocumentRoute
            ::where("trackingId", $this->trackingId)
            ->whereNull("prevId")
            ->get();
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
            'trackingId'  => 'required|unique:documents',
            'userId' => 'required|exists:users,id',
        ]);
    }

    public function createParallelRoutes($officeIds, $officeId, $user) {
        foreach ($officeIds as $officeId) {
            $pathId = generateId();
            $route = new \App\DocumentRoute();
            $route->trackingId  = $this->trackingId;
            $route->officeId    = $user->officeId;
            $route->receiverId  = $user->id;
            $route->senderId    = $user->id;
            $route->pathId      = $pathId;
            $route->arrivalTime = now();
            $route->forwardTime = now();
            $route->save();

            if ($user->officeId == $officeId) {
                throw new \Exception("routes cannot point to self");
            }

            $nextRoute = new \App\DocumentRoute();
            $nextRoute->trackingId = $this->trackingId;
            // TODO: check if office id is valid
            // TODO: route and nextRoute must not be the same
            $nextRoute->officeId = $officeId;
            $nextRoute->pathId = $pathId;
            $nextRoute->final = true;
            $nextRoute->save(); // save first to get an ID

            $route->nextId     = $nextRoute->id;
            $nextRoute->prevId = $route->id;

            $route->save();
            $nextRoute->save();
        }
    }

    public function createSerialRoutes($officeIds, $officeId, $user) {
        $pathId = generateId();
        $route = new \App\DocumentRoute();
        $route->trackingId  = $this->trackingId;
        $route->officeId    = $officeId;
        $route->receiverId  = $user->id;
        $route->senderId    = $user->id;
        $route->pathId      = $pathId;
        $route->arrivalTime = now();
        $route->forwardTime = now();
        $route->save();

        $office = \App\Office::find($officeId);

        foreach ($officeIds as $officeId) {
            if ($officeId == $route->officeId) {
                throw new \Exception("routes cannot point to self");
            }

            $nextOffice = \App\Office::find($officeId);
            if (!$nextOffice) {
                throw new \Exception("office not found: $officeId");
            }
            if (!$office->isLinkedTo($nextOffice)) {
                throw new \Exception(
                    "invalid route, cannot forward {$office->complete_name}"
                    ." to {$nextOffice->complete_name}"
                );
            }

            $nextRoute = new \App\DocumentRoute();
            $nextRoute->trackingId = $this->trackingId;
            $nextRoute->officeId = $officeId;
            $nextRoute->pathId = $pathId;
            $nextRoute->save(); // save first to get an ID

            $route->nextId     = $nextRoute->id;
            $nextRoute->prevId = $route->id;

            $route->save();
            $nextRoute->save();

            $route = $nextRoute;
            $office = $nextOffice;
        }
        $route->final = true;
        $route->save();
    }
}

