<?php

namespace App;

class DoctracAPI {
    // I should have done this sooner, but mutating route state
    // based on (trackingId, officeId) isn't safe since
    // there could be more than once instance.
    // I mean, I knew there could be more than once instance,
    // but I couldn't find a concrete example that could
    // happen in practice (business-rules-wise). But it's
    // better to remove potential errors and just use
    // routeIds instead.
    // But for testing conveniences, I shall allow
    // such parameters.

    public $errors = null;
    public $user = null;
    public $debug = false;

    public function __construct(\App\User $user) {
        $this->user = $user;
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

    public function finalizeByOffice($doc, $office) {
        $doc = $this->getDocument($doc);
        $office = $this->getOffice($office);

        if ( ! $office->gateway) {
            return $this->appendError(
                "non-records office cannot reject document"
            );
        }
        $routes = $this->findProcessingRoutes($doc, $office);
        if ($routes->count() == 0) {
            return $this->appendError("office cannot reject document");
        }
        foreach ($routes as $route) {
            $this->finalizeDocument($route);
        }
        return $routes;
    }

    public function finalizeDocument($route) {
        $route = $this->getRoute($route);
        if ( ! $route->office->gateway) {
            return $this->appendError(
                "non-records office cannot finalize document"
            );
        }
        $doc = $route->document;
        $route->final = true;
        $route->nextId = null;
        $doc->state = "completed";
        $doc->save();
        $route->save();
    }

    public function rejectByOffice($doc, $office) {
        $doc = $this->getDocument($doc);
        $office = $this->getOffice($office);
        $routes = $this->findProcessingRoutes($doc, $office);
        if ($routes->count() == 0) {
            return $this->appendError("office cannot reject document");
        }
        foreach ($routes as $route) {
            $this->rejectDocument($route);
        }
        return $routes;
    }

    public function rejectDocument($route) {
        if ($route->document->state == "disapproved") {
            return $this->appendError(
                "document is already rejected"
            );
        }

        $route = $this->getRoute($route);
        $routes = $route->traceOriginPath();
        $officeIds = collect($routes)->slice(1)->map(function($r) {
            return $r->officeId;
        });

        $routes = $this->serialForwardDocument($route, $officeIds);

        $route->senderId    = $this->user->id;
        $route->forwardTime = ngayon();
        $route->approvalState = "rejected";
        $route->save();

        $route->document->state = "disapproved";
        $route->document->save();

        return $route;
    }

    public function forwardDocument(array $args) {
        $office         = @$args["office"];
        $doc            = @$args["document"];
        $officeIds      = @$args["officeIds"];
        $route          = @$args["route"];
        $annotations    = @$args["annotations"] ?? "";//TODO
        $type           = @$args["type"] ?? "";

        $doc = $this->getDocument($doc);
        $office = $this->getOffice($office);

        if ( ! $route) {
            $routes = $this->findProcessingRoutes($doc, $office);
            if ($routes->count() > 1) {
                return $this->appendError(
                    "ambiguous office route, specify with routeId instead"
                );
            }
            $route = $routes[0] ?? null;
        }

        if ( ! $route) {
            return $this->appendError("invalid route");
        }
        $route = $this->getRoute($route);
        $route->approvalState = "accepted";
        return $this->forwardRoute($route, $type, $officeIds);
    }

    public function forwardRoute($route, $type, $officeIds) {
        if ($route->document->state == "disapproved"
             && !is_empty($officeIds)) {
            $nextRoute = $route->nextRoute;
            if ($nextRoute->officeId != $officeIds[0])
                return $this->appendError(
                    "cannot detour rejected documents"
                );
        }

        if ($type == "parallel")
            $this->parallelForwardDocument($route, $officeIds);
        else
            $this->serialForwardDocument($route, $officeIds);
    }

    public function receiveFromOffice($doc, $office) {
        $doc = $this->getDocument($doc);
        $office = $this->getOffice($office);
        $routes = $this->findWaitingRoutes($doc, $office);
        if ($routes->count() > 1) {
            return $this->appendError(
                "ambiguous office reception route, specify with routeId instead"
            );
        } else if ($routes->count() == 0) {
            return $this->appendError("office cannot receive document");
        }
        return $this->receiveDocument($routes[0]);
    }

    public function forwardToOffice($doc, $office) {
        $doc = $this->getDocument($doc);
        $office = $this->getOffice($office);
        $routes = $this->findSendableRoutes($doc, $office);
        if ($routes->count() > 1) {
            return $this->appendError(
                "ambiguous office destination route, specify with routeId instead"
            );
        } else if ($routes->count() == 0) {
            return $this->appendError("no office to receive");
        }

        return $this->forwardDocument([
            "route" => $routes[0],
            "officeIds" => [$office->id],
        ]);
    }

    public function routeStatus($doc, $office) {
        $routes = $this->allOfficeRoutes($doc, $office);
        foreach ($routes->reverse() as $route) {
            $status = $route->status;
            $nextRoute = $route->nextRoute;
            if ($status && $status != "*") {
                if (!$nextRoute)
                    return $status;
                if (!$nextRoute->isDone())
                    return $status;
            }
        }
        return optional($routes->last())->status ?? "";
    }

    /**
     * @param $route App\DocumentRoute|int
     */
    public function receiveDocument($route) {
        $route = $this->getRoute($route);
        $user = $this->user;
        $prevRoute = $route->prevRoute;
        if ( ! $this->canReceive($route))
            return null;

        $status = $prevRoute->status;
        if ($status != "delivering")
            return $this->appendError("cannot receive to route, previous route is `$status`");

        $doc = $route->document;
        if ($doc->state == "disapproved") {
            $origin = $this->origin($doc);
            if ($origin->officeId == $route->officeId) {
                $route->nextId = null;
                $route->final = true;
            }
        }

        $route->receiverId = $user->id;
        $route->arrivalTime = ngayon();
        $route->save();
        return $route;
    }

    public function dispatchDocument($docData) {
        $docData = arrayObject($docData);

        $user = $this->user;

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

        $doc->save();
        if ($docData->type == "parallel") {
            $this->parallelDispatchDocument($doc, $ids);
        } else {
            $this->serialDispatchDocument($doc, $ids);
        }

        if ($this->hasErrors())
            return null;

        return $doc;
    }

    public function dumpTree($routeOrId, $formatter = null) {
        dump($this->getTree($routeOrId, $formatter));
    }

    public function getTree($routeOrId, $formatter = null) {
        $formatter = $formatter ?? function($route) {
            return "({$route->id}) {$route->office_name} {$route->status}";
        };

        $route = null;
        if ($routeOrId instanceof \App\Document)
            $route = $this->origin($routeOrId);
        else
            $route = $this->getRoute($routeOrId);

        if ( ! $route)
            return null;

        $next = $route->allNextRoutes()->map(
            function($route) use ($formatter) {
                return $this->getTree($route, $formatter);
            }
        );

        $result = [
            "val"  => $formatter($route),
            "prevId" => $route->prevId,
        ];
        if ($route->approvalState) {
            $result["approval"] = $route->approvalState;
            $result["next"] = $next->toArray();
        } else {
            $result["next"] = $next->toArray();
        }
        return $result;
    }

    public function getOffice($obj) {
        if ($obj instanceof \App\Office)
            return $obj;
        if ($obj instanceof \App\User)
            return $obj->office;
        if (is_integer($obj))
            return \App\Office::find($obj);
        if (is_string($obj))
            return optional(\App\User::where("username", $obj)->first())->office;
        return null;
    }

    public function getDocument($docId) {
        if ($docId instanceof \App\Document)
            return $docId;
        if (is_integer($docId))
            return \App\Document::find($docId);
        if (is_string($docId))
            return \App\Document::where("trackingId", $docId)->first();
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

    public function findSendableRoutes($trackingId, $office) {
        return $this->allProcessingRoutes($trackingId)
            ->filter(function($route) use ($office) {
                return $route->office->isLinkedTo($office);
            });
    }

    public function findWaitingRoutes($trackingId, $office) {
        return filter(
            $this->allWaitingRoutes($trackingId),
            function($route) use ($office) {
                return $route->officeId == $office->id;
            }
        );
    }

    public function findProcessingRoutes($trackingId, $office) {
        return $this->allProcessingRoutes($trackingId)
            ->filter(function($route) use ($office) {
                return $route->officeId == $office->id;
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

    public function allOfficeRoutes($doc, $office) {
        $doc = $this->getDocument($doc);
        $office = $this->getOffice($office);
        return $this->searchRoutes($doc, false,
            function($route) use ($office) {
                return $route->officeId == $office->id;
            });
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

                if ($stopOnMatch && $matched)
                    break;
                else if (!$stopOnMatch || !$matched) {
                    $routes_ = $routes_->concat($nextRoutes);
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

    public function followMainRoute($doc) {
        $doc = $this->getDocument($doc);
        $route = $this->origin($doc);
        return $this->followRoute($route);
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

        $offices = collect();
        foreach ($officeIds as $id) {
            $office = \App\Office::find($id);
            if (!$office)
                return $this->appendError("invalid office id: $id");
            $offices->push($office);
            // !!!!!!
            //if ($office->gateway)
            //    break;
        }

        if ($offices->count() == 0) {
            return $this->appendError("must have at least one destination office");
        }

        $allOffices = collect([$route->office])->concat($offices);
        for ($i = 0; $i < $allOffices->count() - 1; $i++) {
            if ($allOffices[$i]->id != $allOffices[$i+1]->id)
                continue;
            return $this->appendError("cannot send documents to self");
        }

        $routes = $offices->map(function($office) use ($doc) {
             $route = new \App\DocumentRoute();
             $route->officeId   = $office->id;
             $route->trackingId = $doc->trackingId;
             $route->save();
             return $route;
        });
        $routes->prepend($route);

        // check if nested transaction if allowed
        $okay = \DB::transaction(function() use ($routes) {
            for ($i = 0; $i < $routes->count()-1; $i++) {
                $route     = $routes[$i];
                $nextRoute = $routes[$i+1];
                $okay = $this->connectRoute($route, $nextRoute);
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

        $office = $route->office;
        $offices = rejectNull($offices);
        foreach ($offices as $destOffice) {
            if ($office->id != $destOffice->id)
                continue;
            return $this->appendError("cannot send documents to self");
        }

        $nextRoutes = $offices->map(function($office) use ($doc, $route) {
             $nextRoute = new \App\DocumentRoute();
             $nextRoute->officeId   = $office->id;
             $nextRoute->trackingId = $doc->trackingId;
             $nextRoute->prevId = $route->id;
             $nextRoute->save();
             return $nextRoute;
        });

        $this->connectRoutes($route, $nextRoutes);

        return $nextRoutes;
    }

    public function canSend($route) {
        if (! $route)
            return $this->appendError("invalid route") ?? false;
        $prevRoute = $route->prevRoute;
        if (! $prevRoute)
            return $this->appendError("no previous route") ?? false;
        if (! $prevRoute->nextId != $route->id)
            return $this->appendError("cannot transfer document to route") ?? false;
        return $route->status == "processing";
    }

    public function canReceive($route) {
        if (! $route)
            return $this->appendError("invalid route") ?? false;
        $prevRoute = $route->prevRoute;
        if (! $prevRoute)
            return $this->appendError("no previous route") ?? false;

        if ( ! $prevRoute->isNext($route))
            return $this->appendError(
                "cannot transfer document from route {$prevRoute->id}
                 to route {$route->id}"
            ) ?? false;

        $status = $route->status;
        if ($status != "waiting")
            return $this->appendError(
                "cannot receive, invalid route state: $status"
            ) ?? false;
        return true;
    }

    public function setSender($route, $annotations = null) {
        $user = $this->user;
        $prevRoute = $route->prevRoute;
        $status = $prevRoute->status;
        if ($prevRoute->status != "processing")
            return $this->appendError("cannot send to route, previous route is `$status`");

        $prevRoute->senderId = $user->id;
        $prevRoute->forwardTime = ngayon();
        $route->annotations = $annotations;
        $prevRoute->save();
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

    public function checkDestinationIds($officeIds) {
        if (is_empty($officeIds)) {
            $this->appendError("select at least one destination", "officeIds");
            return false;
        }

        if (!$this->user->gateway && count($officeIds) > 1) {
            return $this->appendError(
                "non-records office can only forward to one destination",
                "officeIds"
            );
            return false;
        }
        return true;
    }

    public function serialDispatchDocument($doc, $officeIds) {
        $origin = $this->createOriginRoute($doc);
        return $this->serialForwardDocument($origin, $officeIds);
    }

    public function serialForwardDocument($route, $officeIds) {
        $nextRoute = $route->nextRoute;
        if (is_empty($officeIds) && $nextRoute) {
            $this->setSender($nextRoute);
            return;
        }

        if ( ! $this->checkDestinationIds($officeIds))
            return;

        $routes = $this->serialConnect($route, $officeIds);
        if ($this->hasErrors())
            return null;
        if ($routes && $routes->count() > 0) {
            $this->setSender($routes[1]);
            return $routes;
        }
        return null;
    }

    public function parallelDispatchDocument($doc, $officeIds) {
        $origin = $this->createOriginRoute($doc);
        return $this->parallelForwardDocument($origin, $officeIds);
    }

    public function parallelForwardDocument($route, $officeIds) {
        $routes = $this->parallelConnect($route, $officeIds);
        if ($this->hasErrors())
            return null;

        if ($routes && $routes->count() > 0) {
            $route->senderId = $this->user->id;
            $route->forwardTime = ngayon();
            $route->save();
            // TODO: handle annotations
            return $routes;
        }
        return null;
    }

    public function createOriginRoute($doc) {
        $route = new \App\DocumentRoute();
        $route->trackingId  = $doc->trackingId;
        $route->officeId    = $this->user->officeId;
        $route->receiverId  = $this->user->id;
        $route->approvalState = "accepted";
        $route->arrivalTime = now();
        $route->save();
        return $route;
    }

    /** @deprecated */
    public function createDocument($user, $docData) {
        throw new Exception("deprecated");
        /*
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
        */
    }

    public function appendError($msg, $name = "*") {
        $err = $this->errors[$name] ?? null;
        if (! $err)
            $this->errors[$name] = collect();
        else if (is_string($err))
            $this->errors[$name] = collect($err);

        if ($this->debug) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $filename = basename($caller["file"]);
            $lineno   = $caller["line"];
            $msg = "[$filename@$lineno] $msg";
        }
        $this->errors[$name]->push($msg);
        return null;
    }

    public function dumpErrors() {
        if ($this->hasErrors())
            dump($this->getErrors());
        else
            dump("*no errors*");
    }
}
