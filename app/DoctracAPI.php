<?php

namespace App;

class DoctracAPI {

    public $errors = null;

    public function __construct() {
        $this->errors = collect();
    }

    public function setErrors($errors) {
        $this->errors = collect($errors);
        return null;
    }

    public function hasErrors() {
        return $this->errors && $this->errors->count() > 0;
    }

    public function clearErrors() {
        return $this->errors = collect();
    }

    public function getErrors() {
        if ($this->hasErrors())
            return ["errors" => $this->errors->toArray()];
        return [];
    }

    public function buildRoute($officeIds, $user, $annotations, $route) {
        $doc = $route->document;
        $doc->createSerialRoutes($officeIds, $user, $annotations, $route);
    }

    public function finalizeDocument($user, $trackingId) {
    }

    public function rejectDocument($user, $trackingId) {
    }

    public function forwardDocument($user, $trackingId, $officeIds) {
        // for non gateways, office count($officeIds) must be 1
        // remove old routes
    }

    public function receiveDocument($user, $trackingId) {
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        $doc = $this->getDocument($trackingId);

        if (!$doc)
            return $this->appendError("invalid tracking id", "doc");
        if (!$user)
            return $this->appendError("invalid user", "user");

        if (!$user->office)
            return $this->appendError("user has no valid office", "user");

        $office = $user->office;
        $recvRoute = null;
        foreach ($this->allWaitingRoutes($doc) as $route) {
            if ($office->id != $route->officeId)
                continue;

            $prevRoute = $route->prevRoute;
            if (!$prevRoute)
                continue;

            $recvRoute = $this->receiveRoute($route, $user);
        }

        if (!$recvRoute) {
            return $this->appendError("cannot receive document", "doc");
        }

        return $recvRoute;
    }

    public function dispatchDocument($user, $docData) {
        $docData = arrayObject($docData);
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        if (!$user) {
            return $this->appendError("user id is invalid", "userId");
        }

        if (!$user->office) {
            return $this->appendError("user does not have an office", "office");
        }

        if (!$user->isKeeper()) {
            return $this->appendError("user does belong to records office", "office");
        }

        $doc = new \App\Document();
        $doc->userId         = $user->id;
        $doc->title          = $docData->title;
        $doc->type           = $docData->type ?? "serial";
        $doc->details        = $docData->details;
        $doc->trackingId     = $user->office->generateTrackingID();
        $annotations         = $docData->annotations;
        $doc->classification = $docData->classification ?? "open";

        $v = $doc->validate();
        if ($v->fails()) {
            return $this->setErrors($v->errors());
        }

        // at least one destination must be given
        $ids = $docData->officeIds;
        if (!$ids) {
            $msg = "select at least one destination";
            return $this->appendError($msg, "officeIds");
        }

        if (!$user->office) {
            return $this->appendError("user has no office", "officeId");
        }

        $doc->save();
        if ($docData == "parallel") {
            $this->parallelDispatchDocument($user, $doc, $ids);
        } else {
            dump("dispatching");
            $this->serialDispatchDocument($user, $doc, $ids);
        }

        if ($this->hasErrors())
            return null;

        return $doc;
    }

    // 1
    //   2
    //   3
    //     5
    //   4
    public function getTree($routeOrId) {
        $route = $this->getRoute($routeId);
        if ( ! $route)
            return [];
    }

    public function getDocument($docId) {
        if ($docId instanceof \App\Document)
            return $docId;
        if (is_string($docId))
            return \App\Document::find($docId);
        return null;
    }

    public function getRoute($routeOrId) {
        if ($routeOrId instanceof \App\DocumentRoute)
            return $routeOrId;
        if (is_integer($routeOrId))
            return \App\DocumentRoute::find($routeOrId);
        return null;
    }

    public function origin($trackingId) {
        $doc = $this->getDocument($trackingId);
        if (!$doc)
            return null;

        return DocumentRoute
            ::where("trackingId", $doc->trackingId)
            ->whereNull("prevId")
            ->first();
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
        return $this->searchRoutes($trackingId, true, 
            function($route) {
                return $route->allNextRoutes()->isEmpty();
            });
    }

    public function allCurrentRoutes($trackingId) {
        return $this->searchRoutes($trackingId, true, 
            function($route) {
                return $route->isCurrent();
            });
    }

    public function allProcessingRoutes($trackingId) {
        return $this->searchRoutes($trackingId, true, 
            function($route) {
                return $route->isProcessing();
            });
    }

    public function allWaitingRoutes($trackingId) {
        return $this->searchRoutes($trackingId, true, 
            function($route) {
                return $route->isWaiting();
            });
    }

    public function allDeliveringRoutes($trackingId) {
        return $this->searchRoutes($trackingId, true, 
            function($route) {
                return $route->isDelivering();
            });
    }

    public function allNextRoutes($trackingId) {
        $nextRoutes = $this->allCurrentRoutes($trackingId)
            ->map(function($route) {
                return $route->nextRoute;
            });
        return filter($nextRoutes, rejectNull);
    }

    public function searchRoutes($trackingId, $stopOnMatch, $pred) {
        $doc = $this->getDocument($trackingId);
        if (!$doc) {
            return collect();
        }
        $origin    = $this->origin($trackingId);
        $routes    = collect([$origin]);
        $matchedRoutes = collect();

        while ($routes->count() > 0) {
            $routes_ = collect();
            foreach($routes as $route) {
                $nextRoutes = $route->allNextRoutes();
                $matched = $pred($route);
                if ($matched)
                    $matchedRoutes->push($route);

                if (!$stopOnMatch || !$matched) {
                    $routes_->concat($nextRoutes);
                }
            }
            $routes = $routes_;
        }
        return $matchedRoutes;
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

    public function traceRouteIds($routeId) {
        return $this->traceRoute($routeId)->map(function($route) {
            return $route->id;
        });
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

    public function followRouteIds($routeId) {
        return $this->followRoute($routeId)->map(function($route) {
            return $route->id;
        });
    }

    public function followRouteNames($routeId) {
        return $this->followRoute($routeId)->map(function($route) {
            return $route->office_name;
        });
    }

    // Note: not yet sent
    public function serialConnect($route, $officeIds) {
        // TODO: delete old routes

        $doc = $route->document;
        if (!$doc) {
            return $this->appendError("route has no document");
        }

        $offices = collect($officeIds)->map(function($id) {
            return \App\Office::find($id);
        });
        $offices = rejectNull($offices);
        if ($offices->count() == 0) {
            return $this->appendError("must have at least one destination office");
        }

        $routes = $offices->map(function($office) use ($doc) {
             $route = new \App\DocumentRoute();
             $route->officeId   = $office->id;
             $route->trackingId = $doc->trackingId;
             $route->save();
             return $route;
        });
        $routes->prepend($route);

        $okay = \DB::transaction(function() use ($routes) { // check if nested transaction if allowed
            for ($i = 0; $i < $routes->count()-1; $i++) {
                $route     = $routes[$i];
                $nextRoute = $routes[$i+1];
                $okay = $this->connectRoute($route, $nextRoute);
                dump("***", $route->id, $nextRoute->id, $okay);
                if (!$okay)
                    return false;
            }
            return $okay;
        });

        if ($okay) {
            return $routes;
        }
        return collect();
    }

    public function parallelConnect($route, $officeIds) {
        // TODO: delete old routes

        $doc = $route->document;
        if (!$doc)
            return;

        $offices = collect($officeIds)->map(function($id) {
            return \App\Office::find($id);
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

    public function canSendReceive($route) {
        if (! $route) 
            return $this->appendError("invalid route") ?? false;
        $prevroute = $route->prevRoute;
        if (! $prevRoute)
            return $this->appendError("no previous route") ?? false;
        if (! $prevRoute->nextId != $route->id)
            return $this->appendError("cannot transfer document to route") ?? false;
        return true;
    }

    public function sendToRoute($route, $user, $annotations=null) {
        $prevroute = $route->prevRoute;
        if ( ! $this->canSendReceive($route))
            return null;

        $status = $prevRoute->status;
        if ($prevRoute->status != "processing")
            return $this->appendError("cannot sent to route, previous route is `$status`");

        $prevRoute->senderId = $user->id;
        $prevRoute->forwardTime = ngayon();
        $route->annotations = $annotations;
        $prevRoute->save();
        $route->save();
    }

    public function receiveRoute($route, $user) {
        $prevroute = $route->prevRoute;
        if ( ! $this->canSendReceive($route))
            return null;
        $status = $prevRoute->status;
        if ($prevRoute->status != "waiting")
            return $this->appendError("cannot sent to route, previous route is `$status`");

        $route->receiverId = $user->id;
        $route->arrivalTime = ngayon();
        $route->save();
    }

    public function connectRoute($srcRoute, $dstRoute) {
        if ( ! $srcRoute->office->isLinkedTo($dstRoute->office)) {
            $this->appendError(
                "cannot pass documents from {$srcRoute->office->complete_name} ".
                "to {$dstRoute->office->complete_name}"
            );
            return false;
        }
        \DB::transaction(function() use ($srcRoute, $dstRoute) {
            $srcRoute->nextId = $dstRoute->id;
            $dstRoute->prevId = $srcRoute->id;
            dump("*****next prev", $srcRoute->nextId, $dstRoute->prevId, "****");
            $srcRoute->save();
            $dstRoute->save();
        });
        return true;
    }

    public function connectRoutes($srcRoute, $dstRoutes) {
        \DB::transaction(function() use ($srcRoute, $dstRoutes) {
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
        $routes = $this->serialConnect($origin, $officeIds);
        if ($routes && $routes->count() > 0) {
            //$this->sendToRoute($origin, $routes[1], $user);
            $this->sendToRoute($routes[1], $user);
            return $routes;
        }
        return null;
    }

    public function parallelDispatchDocument($user, $doc, $officeIds) {
        $origin = $this->createOriginRoute($user, $doc);
        $routeIds = $this->parallelConnect($origin, $officeIds);
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

    /** @deprecated */
    public function createDocument($user, $docData) {
        $docData = arrayObject($docData);
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        if (!$user) {
            return $this->appendError("user id is invalid", "userId");
        }

        if (!$user->office) {
            return $this->appendError("user does not have an office", "office");
        }

        if (!$user->isKeeper()) {
            return $this->appendError("user does belong to records office", "office");
        }

        $doc = new \App\Document();
        $doc->userId         = $user->id;
        $doc->title          = $docData->title;
        $doc->type           = $docData->type ?? "serial";
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
            return $this->appendError($msg, "officeIds");
        }

        if (!$user->office) {
            return $this->appendError("office id is invalid", "officeId");
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

    public function appendError($msg, $name = "*") {
        $err = $this->errors[$name];
        if (! isset($err))
            $this->errors[$name] = collect();
        else if (is_string($err))
            $this->errors[$name] = collect($err);

        $this->errors[$name]->push($msg);
        return null;
    }
}
