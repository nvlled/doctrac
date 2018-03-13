<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Document extends Model
{
    public $appends = [
        "attachment_filename",
        "attachment_size",
        "attachment_url",
    ];
    public $hidden = [
        "attachment",
    ];

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

    public function startingRoute() {
        return $this->startingRoutes()->first();
    }

    public function finalRoutes() {
        return DocumentRoute::whereIn("id", $this->finalRouteIds())->get();
    }

    public function finalOffices() {
        return mapFilter($this->finalRoutes(), function($route) {
            return $route->office;
        });
    }

    public function followTrail($start=true) {
        $route = null;
        if ($start)
            $route = $this->startingRoute();
        else
            $route = $this->currentRoute();
        $routes = collect();
        while ($route) {
            $routes->push($route);
            $route = $route->nextRoute;
        }
        return $routes;
    }

    static function track($trackingId) {
        return self::where("trackingId", $trackingId)->first();
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

    public function currentRoute() {
        return $this->currentRoutes()->first();
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

    public function nextRoute() {
        return $this->nextRoutes()->first();
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

    public function createParallelRoutes($officeIds, $user, $annotations) {
        $routes = [];
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
            $route->approvalState = "accepted";
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
            $nextRoute->annotations = $annotations;
            $nextRoute->save(); // save first to get an ID
            $routes []= $nextRoute;

            $route->nextId     = $nextRoute->id;
            $nextRoute->prevId = $route->id;

            $route->save();
            $nextRoute->save();
        }
        return $routes;
    }

    public function createSerialRoutes($officeIds, $user, $annotations, $route=null) {
        $routes = [];

        if ( ! $route) {
            $pathId = generateId();
            $route = new \App\DocumentRoute();
            $route->trackingId  = $this->trackingId;
            $route->officeId    = $user->officeId;
            $route->receiverId  = $user->id;
            $route->pathId      = $pathId;
            $route->approvalState = "accepted";
        }
        $pathId = $route->pathId;
        $route->senderId    = $user->id;
        $route->arrivalTime = now();
        $route->forwardTime = now();
        $route->save();

        $routes []= $route;

        $office = \App\Office::find($user->officeId);

        $annotate = true;
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

            if ($annotate) {
                $nextRoute->annotations = $annotations;
                $annotate = false;
            }

            $nextRoute->save(); // save first to get an ID

            $route->nextId     = $nextRoute->id;
            $nextRoute->prevId = $route->id;

            $route->save();
            $nextRoute->save();
            $routes []= $nextRoute;

            $route = $nextRoute;
            $office = $nextOffice;
        }
        $route->save();
        return $routes;
    }

    public function attachment() {
        return $this->hasOne("App\File", "id", "attachmentId");
    }

    public function getAttachmentFilenameAttribute() {
        return optional($this->attachment)->name;
    }

    public function getAttachmentSizeAttribute() {
        return optional($this->attachment)->size;
    }

    public function getAttachmentUrlAttribute() {
        $attachment = $this->attachment;
        if ( ! $attachment)
            return "";
        return route("download-file", [
            "id"      => $attachment->id,
            "filename"=> $attachment->name,
        ]);
    }

    public function isSerial() {
        return $this->type == "serial";
    }
    public function isParallel() {
        return $this->type == "parallel";
    }

    public function isDone() {
        $state = $this->state;
        return $state == "completed";
    }
}
