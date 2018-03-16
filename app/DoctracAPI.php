<?php

namespace App;

class DoctracAPI {

    public $errors = collect();

    public function setErrors($errors) {
        $this->errors = ["errors"=>$errors];
        return null;
    }

    public function hasErrors() {
        return !!$this->errors;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function buildRoute($officeIds, $user, $annotations, $route) {
        $doc = $route->document;
        $doc->createSerialRoutes($officeIds, $user, $annotations, $route);
    }

    public function receiveDocument($user, $trackingId) {
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        $doc = \App\Document::where("trackingId", $trackingId)->first();

        if (!$doc)
            return $this->setErrors(["doc"=>"invalid tracking id"]);
        if (!$user)
            return $this->setErrors(["user"=>"invalid user"]);

        if (!$user->office)
            return $this->setErrors(["user"=>"user has no valid office"]);

        if (!$user->office->canReceiveDoc($doc)) {
            return $this->setErrors(["doc"=>"cannot receive document"]);
        }

        // TODO: handle parallel routes
        $errors = [];
        foreach ($doc->nextRoutes() as $route) {
            $office = $user->office;
            if ($office->id != $route->officeId)
                continue;
            $prevRoute = $route->prevRoute;
            if (!$prevRoute)
                continue;
            $route->receiverId = $user->id;
            $route->arrivalTime = ngayon();
            $route->save();
        }
        return $doc;
    }

    public function getRoute($routeOrId) {
        if ($routeOrId instanceof \App\DocumentRoute)
            return $routeOrId;
        if (is_integer($routeOrId))
            return \App\DocumentRoute::find($routeOrId);
        return null;
    }

    public function origin($trackingId) {
        $doc = \App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return null;

        return DocumentRoute
            ::where("trackingId", $doc->trackingId)
            ->whereNull("prevId")
            ->get();
    }

    public function startRoute($routeId) {
        $route = $this->getRoute($routeId);
        $path  = $this->traceRoute($routeId);
        $count = $path->count();
        if ($count > 0)
            return $path[0];
        return null;
    }

    public function endRoute($routeId) {
        $route = $this->getRoute($routeId);
        $path  = $this->followRoute($routeId);
        $count = $path->count();
        if ($count > 0)
            return $path[$count-1];
        return null;
    }

    public function finalRoute($routeId) {
        $route = $this->endRoute($routeId);
        if ($route->final)
            return $route;
        return null;
    }

    public function allEndRoutes($trackingId) {
        $doc = \App\Document::where("trackingId", $trackingId)->first();
        if ( ! $doc) {
            return collect();
        }
        $origin    = $this->origin($trackingId);
        $routes    = collect([$origin]);
        $endRoutes = collect();

        while ($routes->count() > 0) {
            $routes_ = collect();
            foreach($routes as $route) {
                $nextRoutes = $route->nextRoutes();
                if ($nextRoutes->isEmpty()) {
                    $endRoutes->push($route);
                } else {
                    $routes_->concat($nextRoutes);
                }
            }
            $routes = $routes_;
        }
        return $endRoutes;
    }

    public function allFinalRoutes($routeId) {
        return filter($this->endRoutes($routeId), function($route) {
            return $route->final;
        });
    }

    /* returns all the route from the first route
     * up to the given route
     */
    public function traceRoute($routeId) {
        $route = $this->getRoute($routeId);
        $routes = collect();
        while ($route) {
            $routes->push($route);
            $route = $route->prevRoute;
        }
        return $routes->reverse();
    }

    /* returns all the route from the given route
     * up to the last route
     */
    public function followRoute($routeId) {
        $route = $this->getRoute($routeId);
        $routes = collect();
        while ($route) {
            $routes->push($route);
            $route = $route->nextRoute;
        }
        return $routes;
    }

    // Note: not yet sent
    public function serialConnect($route, $officeIds) {
        // TODO: delete old routes

        $doc = $route->document;
        if (!$doc)
            return;

        $offices = collect($officeIds)->map(function($id) {
            return App\Office::find($id);
        });
        $offices = rejectNull($offices);
        if ($offices->count() == 0) {
            $this->appendError("must have at least one destination office");
            return;
        }

        $routes = $offices->map(function($office) use ($doc) {
             $route = new \App\Route();
             $route->officeId   = $office->id;
             $route->trackingId = $doc->trackingId;
             return $route;
        });
        $routes->prepend($route);

        $okay = DB::transaction(function() use ($routes) { // check if nested transaction if allowed
            for ($i = 0; $i < $routes->count()-1; $i++) {
                $route     = $routes[$i];
                $nextRoute = $routes[$i+1];
                $okay = connectRoute($route, $nextRoute);
                if (!$okay)
                    return false;
            }
            return $okay;
        });

        if ($okay) {
            return $routes->map(function($route) {
                return $route->id;
            });
        }
        return collect();
    }

    public function parallelConnect($route, $officeIds) {
        // TODO: delete old routes

        $doc = $route->document;
        if (!$doc)
            return;

        $offices = collect($officeIds)->map(function($id) {
            return App\Office::find($id);
        });
        $offices = rejectNull($offices);
        $nextRoutes = $offices->map(function($office) use ($doc) {
             $route = new \App\Route();
             $route->officeId   = $office->id;
             $route->trackingId = $doc->trackingId;
             return $route;
        });

        connectRoutes($route, $nextRoutes);

        return $nextRoutes->map(function($route) {
            return $route->id;
        });
    }

    public function sendToRoute($srcRoute, $dstRoute, $user, $annotations=null) {
        $srcRoute->senderId = $user->id;
        $srcRoute->forwardTime = ngayon();
        $dstRoute->annotations = $annotations;
    }

    public function connectRoute($srcRoute, $dstRoute) {
        if ($srcRoute->isLinkedTo($dstRoute)) {
            $this->appendError(\
                "cannot pass documents from {$srcRoute->office->complete_name}".
                "to {$dstRoute->office->complete_name}"
            );
            return false;
        }
        DB::transaction(function() use ($srcRoute, $dstRoute) {
            $srcRoute->nextId = $dstRoute->id;
            $dstRoute->prevId = $srcRoute->id;
            $srcRoute->save();
            $dstRoute->save();
        });
        return true;
    }

    public function connectRoutes($srcRoute, $dstRoutes) {
        DB::transaction(function() use ($srcRoute, $dstRoutes) {
            $moreNextId = generateId();
            foreach ($dstRoutes as $route) {
                $nr = new \App\NextRoute();
                $nr->moreNextId = $moreNextId;
                $nr->routeId    = $route->id;
                $nr->save();
            }
            $srcRoute->moreNextId = $moreNextId;
            $srcRoute->save();
        });
    }

    public function serialDispatchDocument($user, $doc, $officeIds) {
        $origin = $this->createOriginRoute($user, $doc);
        $routeIds = $this->serialConnect($origin, $officeIds);
    }

    public function createOriginRoute($user, $doc) {
        $route = new \App\DocumentRoute();
        $route->trackingId  = $doc->trackingId;
        $route->officeId    = $user->officeId;
        $route->receiverId  = $user->id;
        $route->approvalState = "accepted";
        $route->arrivalTime = now();
        $route->save();
        return $route;
    }

    public function dispatchDocument($user, $docData) {
        $docData = arrayObject($docData);
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        if (!$user) {
            return $this->setErrors(["user id"=>"user id is invalid"]);
        }

        if (!$user->office) {
            return $this->setErrors(["office"=>"user does not have an office"]);
        }

        if (!$user->isKeeper()) {
            return $this->setErrors(["office"=>"user does belong to records office"]);
        }

        $doc = new \App\Document();
        $doc->userId         = $user->id;
        $doc->title          = $docData->title;
        $doc->type           = $docData->type;
        $doc->details        = $docData->details;
        $doc->trackingId     = $user->office->generateTrackingID();
        $annotations         = $docData->annotations;
        $doc->classification = $docData->classification;

        $v = $doc->validate();
        if ($v->fails()) {
            return $this->setErrors($v->errors());
        }

        // at least one destination must be given
        $ids = $docData->officeIds;
        if (!$ids) {
            $msg = "select at least one destination";
            return $this->setErrors(["officeIds"=>$msg]);
        }

        if (!$user->office) {
            return $this->setErrors(["officeId"=>"office id is invalid"]);
        }
        $officeId = $user->officeId;

        if ($docData == "parallel") {
            $this-> //!!!!!!!!!!!!
        } else {
        }

        return $doc;
    }

    public function createDocument($user, $docData) {
        $docData = arrayObject($docData);
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        if (!$user) {
            return $this->setErrors(["user id"=>"user id is invalid"]);
        }

        if (!$user->office) {
            return $this->setErrors(["office"=>"user does not have an office"]);
        }

        if (!$user->isKeeper()) {
            return $this->setErrors(["office"=>"user does belong to records office"]);
        }

        $doc = new \App\Document();
        $doc->userId         = $user->id;
        $doc->title          = $docData->title;
        $doc->type           = $docData->type;
        $doc->details        = $docData->details;
        $doc->trackingId     = $user->office->generateTrackingID();
        $annotations         = $docData->annotations;
        $doc->classification = $docData->classification;

        $v = $doc->validate();
        if ($v->fails()) {
            return $this->setErrors($v->errors());
        }

        // at least one destination must be given
        $ids = $docData->officeIds;
        if (!$ids) {
            $msg = "select at least one destination";
            return $this->setErrors(["officeIds"=>$msg]);
        }

        if (!$user->office) {
            return $this->setErrors(["officeId"=>"office id is invalid"]);
        }
        $officeId = $user->officeId;

        \DB::transaction(function() use ($doc, $ids, $user, $officeId, $annotations) {
            $doc->save();

            if ($doc->type == "serial") {
                $doc->createSerialRoutes($ids, $user, $annotations);
            } else {
                $doc->createParallelRoutes($ids, $user, $annotations);
            }
        });

        return $doc;
    }

    public function appendError($msg) {
        $this->errors->push($msg);
    }
}
