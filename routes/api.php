<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// TODO: functions here should be no more than 10 lines,
//       code should be reusable for web routes

Route
::prefix("routes")
->middleware(["restrict-doc"])
->group(function() {
    Route::any('/origins/{trackingId}', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if ($doc) {
            return $doc->startingRoutes();
        }
        return collect();
    });

    Route::any('/parallel/{trackingId}', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return collect();

        $routes = collect();
        foreach ($doc->startingRoutes() as $startRoute) {
            $path = $startRoute->followRoutesInPath();
            $i = 0;
            foreach ($path as $route) {
                // TODO: search for function that shifts an array element
                if ($i++ == 0)
                    continue;
                $routes->push($route);
            }
        }
        return $routes;
    });

    // TODO: it would be more efficient to take the pathId as well
    Route::any('/serial/{trackingId}', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return collect();

        $routes = collect();
        foreach ($doc->startingRoutes() as $startRoute) {
            $path = $startRoute->followRoutesInPath();
            foreach ($path as $route)
                $routes->push($route);
        }
        return $routes;
    });

    Route::any('/next/{routeId}', function (Request $req, $routeId) {
        $route = \App\DocumentRoute::find($routeId);
        return optional($route)->nextRoute;
    });

    Route::any('/next-offices/{trackingId}', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return collect();

        $routes = collect();
        foreach ($doc->startingRoutes() as $startRoute) {
            $path = $startRoute->followRoutesInPath();
            foreach ($path as $route) {
                if ( ! $route->isDone())
                    $routes->push($route);
            }
        }
        return $routes->map(function($r) {
            return $r->office;
        });
    });

    Route::any('/{routeId}/forward', function (Request $req, $routeId) {
        $route = App\DocumentRoute::find($routeId);
        if (!$route)
            return ["errors"=>["doc"=>"invalid tracking id"]];
        $user = Auth::user();
        if (!$user)
            return ["errors"=>["user"=>"invalid user"]];

        if (!$user->office)
            return ["errors"=>["user"=>"user has no valid office"]];

        if ( ! $route->canBeSentBy($user->office)) {
            return ["errors"=>["doc"=>"user cannot send document"]];
        }

        $office = $user->office;

        if ($route->final) {
            return ["errors"=>["doc"=>
                "cannot forward document on path={$route->$pathId},
                route={$route->id}, destination is final"
            ]];
        }

        $destOfficeId = $req->officeId;
        if (!$destOfficeId) {
            return ["errors"=>["doc"=>
                "no next destination specified"
            ]];
        }
        if ($destOfficeId == $route->officeId) {
            return ["errors"=>["doc"=>
                "cannot forward documents to the same place"
            ]];
        }
        $nextOffice = \App\Office::find($destOfficeId);
        if (!$office->isLinkedTo($nextOffice)) {
            $nextOfficeName = $nextOffice->complete_name ?? "unknown office";
            return ["errors"=>["doc"=>
                "invalid route, cannot forward {$office->complete_name}"
                ." to {$nextOfficeName}"
            ]];
        }

        $route->senderId = $user->id;
        $route->forwardTime = ngayon();

        $nextRoute = $route->nextRoute;
        $annotations = $req->annotations;
        if (!$nextRoute) {
            $nextRoute = new App\DocumentRoute();
            $nextRoute->trackingId = $route->trackingId;
            $nextRoute->officeId = $destOfficeId;
            $nextRoute->pathId = $route->pathId;
            $nextRoute->annotations = $annotations;
            $nextRoute->save();
            $route->nextId = $nextRoute->id;
            $nextRoute->prevId = $route->id;
            $route->save();
            $nextRoute->save();
        } else if ($destOfficeId == $nextRoute->officeId) {
            $nextRoute->annotations = $annotations;
            $route->save();
            $nextRoute->save();
        } else {
            $shortcut = $route->findNextRoute($destOfficeId);
            if ($shortcut) {
                // TODO: delete skipped routes

                // take a shortcut route
                $route->nextId = $shortcut->id;
                $shortcut->prevId = $route->id;
                $shortcut->annotations = $annotations;
                $route->save();
                $shortcut->save();
            } else {
                // insert a detour route
                $detour = new App\DocumentRoute();
                $detour->trackingId = $route->trackingId;
                $detour->officeId = $destOfficeId;
                $detour->pathId = $route->pathId;
                $detour->annotations = $annotations;
                $detour->save(); // save first to get an ID

                $route->nextId = $detour->id;
                $detour->prevId = $route->id;
                if ($nextRoute) {
                    $detour->nextId = $nextRoute->id;
                    $nextRoute->prevId = $detour->id;
                }

                $route->save();
                $detour->save();
                if ($nextRoute)
                    $nextRoute->save();
            }
        }
        \Flash::add("document forwarded: {$route->trackingId}");
        \Notif::sent($office, $nextOffice, $nextRoute);
    });
});


Route
::prefix("docs")
->middleware(["auth"])
->group(function() {
    Route::any('/search', function (Request $req) {
        $q = "%{$req->q}%";
        return App\Document::where("trackingId", "like", $q)
            ->orWhere("title", "like", $q)
            ->limit(10)
            ->get();
    });

    Route::any('/rand-id', function (Request $req) {
        $randId = strtolower(str_random(5));
        $id = $req->officeId;
        $office = App\Office::find($id);

        if ($office) {
            return strtolower("{$office->id}-{$office->name}-$randId");
        }
        return $randId;
    });

    Route::any('/get/{trackingId}', function (Request $req, $trackingId) {
        return App\Document::where("trackingId", $trackingId)->first();
    });

    Route::any('/next-route/{trackingId}/path/{pathId}',
        function (Request $req, $trackingId, $pathId) {
            $doc = App\Document::where("trackingId", $trackingId)->first();
            if (!$doc)
                return null;
            foreach ($doc->nextRoutes() as $route) {
                if ($pathId == $route->pathId)
                    return $route;
            }
            return null;
        });

    Route::any('/current-routes/{trackingId}/', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return collect();
        return $doc->currentRoutes();
    });

    Route::any('/next-routes/{trackingId}/', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return collect();
        return $doc->nextRoutes();
    });

    Route::any('/receive/{trackingId}', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return ["errors"=>["doc"=>"invalid tracking id"]];
        $user = Auth::user();
        if (!$user)
            return ["errors"=>["user"=>"invalid user"]];

        if (!$user->office)
            return ["errors"=>["user"=>"user has no valid office"]];

        if (!$user->office->canReceiveDoc($doc)) {
            return ["errors"=>["doc"=>"cannot receive document"]];
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
            //\Notif::received($prevRoute);
            Notif::received($prevRoute->office, $route->office, $prevRoute);
        }
        \Flash::add("document received: {$doc->trackingId}");
    });

    Route::any('/forward/{trackingId}', function (Request $req, $trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->first();
        if (!$doc)
            return ["errors"=>["doc"=>"invalid tracking id"]];
        $user = Auth::user();
        if (!$user)
            return ["errors"=>["user"=>"invalid user"]];

        if (!$user->office)
            return ["errors"=>["user"=>"user has no valid office"]];

        if (!$user->office->canSendDoc($doc)) {
            return ["errors"=>["doc"=>"user cannot send document"]];
        }

        $errors = [];
        foreach ($doc->currentRoutes() as $route) {
            $office = $user->office;
            if ($office->id != $route->officeId)
                continue;

            if ($route->final) {
                $errors[] =
                    "cannot forward document on path={$route->$pathId},
                    route={$route->id}, destination is final";
                continue;
            }

            $destOfficeId = $req->officeId;
            $nextRoute = $route->nextRoute;
            if (!$destOfficeId || !$nextRoute) {
                $errors[] =
                    "no next destination specified";
                continue;
            }
            if ($destOfficeId == $route->officeId) {
                $errors[] =
                    "cannot forward documents to the same place";
                continue;
            }
            $nextOffice = \App\Office::find($destOfficeId);
            if (!$office->isLinkedTo($nextOffice)) {
                $nextOfficeName = $nextOffice->complete_name ?? "unknown office";
                $errors[] =
                    "invalid route, cannot forward {$office->complete_name}"
                    ." to {$nextOfficeName}";
                continue;
            }

            $route->senderId = $user->id;
            $route->forwardTime = ngayon();

            $annotations = $req->annotations;
            $notifyOffice = null;
            if ($destOfficeId == $nextRoute->officeId) {
                $nextRoute->annotations = $annotations;
                $route->save();
                $nextRoute->save();
                $destRoute = $nextRoute;
            } else {
                $shortcut = $route->findNextRoute($destOfficeId);
                if ($shortcut) {
                    // TODO: delete skipped routes

                    // take a shortcut route
                    $route->nextId = $shortcut->id;
                    $shortcut->prevId = $route->id;
                    $shortcut->annotations = $annotations;
                    $destRoute = $shortcut;
                    $route->save();
                    $shortcut->save();
                } else {
                    // insert a detour route
                    $detour = new App\DocumentRoute();
                    $detour->trackingId = $doc->trackingId;
                    $detour->officeId = $destOfficeId;
                    $detour->pathId = $route->pathId;
                    $detour->annotations = $annotations;
                    $detour->save(); // save first to get an ID

                    $route->nextId = $detour->id;
                    $detour->prevId = $route->id;
                    if ($nextRoute) {
                        $detour->nextId = $nextRoute->id;
                        $nextRoute->prevId = $detour->id;
                    }

                    $route->save();
                    $detour->save();
                    $destRoute = $detour;
                    if ($nextRoute)
                        $nextRoute->save();
                }
            }
            \Notif::sent($office, $destRoute->office, $destRoute->id);
        }
        if ($errors) {
            return ["errors"=>["forward"=>$errors]];
        }
        \Flash::add("document forwarded: {$doc->trackingId}");
    });

    Route::any('/{trackingId}/set-attachment',
        function (Request $req, $trackingId) {
            $doc = App\Document::where("trackingId", $trackingId)->first();
            if (!$doc)
                return ["errors"=>["doc"=>"invalid tracking id"]];

            $file = $req->file("filedata");
            if ( ! $req->hasFile("filedata")) {
                return ["errors"=>["file"=>"no file is provided: {$req->filedata}"]];
            }

            if ( ! $file->isValid()) {
                return ["errors"=>["file"=>"upload failed"]];
            }


            $path = $file->store("uploads");
            $filename = $req->filename
                ??  str_slug($doc->title)."-".$file->extension();

            $file = new \App\File();
            $file->name = $filename;
            $file->path = $path;
            $file->size = disk()->size($path);
            $file->save();

            $doc->attachmentId = $file->id;
            $doc->save();

            return $file->id;
        });

    // TODO: rename to create
    Route::any('/send', function (Request $req) {
        $user = Auth::user();
        if (!$user) {
            return ["errors"=>["user id"=>"user id is invalid"]];
        }

        if (!$user->office) {
            return ["errors"=>["office"=>"user does not have an office"]];
        }

        if (!$user->isKeeper()) {
            return ["errors"=>["office"=>"user does belong to records office"]];
        }

        $doc = new App\Document();
        $doc->userId = $user->id;
        $doc->title = $req->title;
        $doc->type = $req->type;
        $doc->details = $req->details;
        $doc->trackingId = $user->office->generateTrackingID();
        $annotations = $req->annotations;
        $doc->classification = $req->classification;

        $v = $doc->validate();
        if ($v->fails()) {
            return ["errors"=>$v->errors()];
        }

        // TODO: should only upload file later after validations has been made
        if ($req->hasFile("attachment")) {
            $attachment = $req->file("attachment");
            if ( ! $attachment->isValid())
                return ["errors"=>["attachment"=>"failed to upload file"]];
            $filename = $attachment->store(\App\Config::$upload_dir);
            $doc->attachmentFilename = $filename;
        }

        // at least one destination must be given
        $ids = $req->officeIds;
        if (!$ids) {
            $msg = "select at least one destination";
            return ["errors"=>["officeIds"=>$msg]];
        }
        // if there is no source office id given,
        // use the office id of the user
        $officeId = $req->officeId;
        if (!$officeId) {
            if (!App\Office::find($user->officeId)) {
                return ["errors"=>["officeId"=>"office id is invalid"]];
            }
            $officeId = $user->officeId;
        }

        DB::transaction(function() use ($doc, $ids, $user, $officeId, $annotations) {
            $doc->save();

            if ($doc->type == "serial") {
                $routes = $doc->createSerialRoutes($ids, $user, $annotations);
                $nextRoute = $routes[1];
                \Notif::sent($user->office, $nextRoute->office, $nextRoute);
            } else {
                $routes = $doc->createParallelRoutes($ids, $user, $annotations);
                foreach ($routes as $nextRoute)
                    \Notif::sent($user->office, $nextRoute->office, $nextRoute);
            }
        });

        \Flash::add("document sent: {$doc->trackingId}");
        return $doc;
    });
});


Route::any('/users/login', function (Request $req) {
    $username = $req->username;
    $password = $req->password;
    if (Auth::attempt(["username"=>$username, "password"=>$password])) {
        return Auth::user();
    }
    return null;
});

Route::any('/users/logout', function (Request $req) {
    Auth::logout();
    \Flash::add("You are now logged out");
    return "logout";
});


Route::any('/users/self', function (Request $req) {
    return Auth::user();
});

Route
::prefix("users")
->middleware(["auth"])
->group(function() {
    Route::any('/update', function(Request $req) {
        $user = Auth::user();
        if (! $user)
            return;
        if ($req->phone_number)
            $user->phone_number = $req->phone_number;
        if ($req->email)
            $user->email = $req->email;

        $user->save();
        return $user;
    });

    Route::any('/read-notification', function(Request $req) {
        $user = Auth::user();
        if (! $user)
            return;
        $user->notifications->where('id', $req->id)->markAsRead();
    });

    Route::any('/doc-notifications', function(Request $req) {
        $user = Auth::user();
        if (! $user)
            return collect();
        $notifications = collect();
        foreach ($user->notifications as $notif) {
            if ($notif->type != "App\Notifications\DocumentAction") {
                continue;
            }
            $data = $notif->data;
            $data['diff'] = (new \Carbon\Carbon($notif["date"]))->diffForHumans();
            $data['url'] = route("view-document", $data["routeId"]);
            $data['id'] = $notif->id;
            $data['unread'] = $notif->read_at == null;
            $notifications->push($data);
        }
        return $notifications;
    });

    Route::any('/{userId}/see-route/{routeId}', 
        function (Request $req, $userId, $routeId) {
            $user  = App\User::find($userId);
            $route = App\DocumentRoute::find($routeId);
            if (!$user || !$route)
                return null;

            try {
                $route->seenBy($user);
                $prevRoute = $route->prevRoute;
                \Notif::seen($prevRoute->office, $route->office, $prevRoute);
                return "okay";
            } catch (Exception $e) {
                return $e->getMessage();
            }
    });

    Route::any('/{userId}/seen-routes', function (Request $req, $userId) {
        $user = App\User::find($userId);
        if (!$user)
            return null;
        $ids = collect();
        foreach ($user->seenRoutes() as $sr) {
            if (@$ids[$sr->routeId] == null) {
                $ids[$sr->routeId] = collect();
            }
            $ids[$sr->routeId]->push($sr->status);
        }
        return $ids;
    });

    Route::any('/search', function (Request $req) {
        $id = $req->q;
        if (is_numeric($id)) {
            $user = App\User::find($id);
            if ($user)
                return collect([$user]);
        }
        $q = "%{$req->q}%";
        $limit = 20;

        return App\User::query()
            ->where("firstname", "like", $q)
            ->orWhere("lastname", "like", $q)
            ->orWhereHas("office", function($query) use ($q) {
                $query->where("name", "like", $q)
                    ->orWhereHas("campus", function($query) use ($q) {
                        $query->where("name", "like", $q);
                    });
            })
            ->limit($limit)
            ->get();
    });

    Route::any('/self/clear', function (Request $req) {
        $user = Auth::user();
        if ($user)
            Auth::logout($user);
    });
    Route::any('/self/{userId}', function (Request $req, $userId) {
        $user = App\User::find($userId);
        if ($user)
            Auth::login($user);
        return $user;
    })->middleware(\App\Http\Middleware\LocalEnv::class);

    Route::post('/del/{id}', function (Request $req, $id) {
        $user = App\User::findOrFail($id);
        $user->delete();
        return "okay";
    });

    Route::post('/add', function (Request $req) {
        $user = new App\User();
        $user->username = $req->username;
        $user->firstname = $req->firstname;
        $user->middlename = $req->middlename;
        $user->lastname = $req->lastname;
        $user->password = bcrypt($req->password);
        $user->positionId = $req->positionId;
        $user->privilegeId = $req->privilegeId;
        $user->officeId = $req->officeId;

        $v = $user->validate();
        if ($v->fails())
            return ["errors"=>$v->errors()];

        $user->save();
        return $user;
    });

    Route::get('/list', function (Request $req) {
        return App\User::all();
    });

    Route::any('/get/{id}', function (Request $req, $id) {
        return \App\User::find($id)
            ?? \App\User::where("username", $id)->first();
    });

});


Route
::prefix("privileges")
->middleware(["auth"])
->group(function() {
    Route::post('/del/{id}', function (Request $req, $id) {
        $priv = App\Privilege::findOrFail($id);
        $priv->delete();
        return $priv;
    });

    Route::post('/add', function (Request $req) {
        $priv = new App\Privilege();
        $priv->name = $req->name;

        $v = $priv->validate();
        if ($v->fails())
            return ["errors"=>$v->errors()];

        $priv->save();
        return $priv;
    });

    Route::get('/list', function (Request $req) {
        return App\Privilege::all();
    });
});


Route
::prefix("positions")
->middleware(["auth"])
->group(function() {
    Route::post('/del/{id}', function (Request $req, $id) {
        $pos = App\Position::findOrFail($id);
        $pos->delete();
        return $pos;
    });

    Route::post('/add', function (Request $req) {
        $pos = new App\Position();
        $pos->name = $req->name;

        $v = $pos->validate();
        if ($v->fails())
            return ["errors"=>$v->errors()];

        $pos->save();
        return $pos;
    });

    Route::get('/list', function (Request $req) {
        return App\Position::all();
    });

});


Route
::prefix("offices")
->middleware(["auth"])
->group(function() {
    Route::any('/self', function (Request $req) {
        if (Auth::user())
            return Auth::user()->office;
    });

    Route::any('/{officeId}/update-contact-info', function (Request $req, $officeId) {
        $officeId = $req->officeId;
        $office = App\Office::find($officeId);
        if (!$office)
            return ["errors"=>["officeId"=>"office id is invalid"]];

        $office->setPrimaryContactInfo($req->email, $req->phoneno);
        $office->setOtherEmails($req->emails);
        return $office->setOtherPhoneNumbers($req->phonenumbers);
    });

    Route::any('/{officeId}/incoming', function (Request $req) {
        $officeId = $req->officeId;
        $office = App\Office::find($officeId);
        if (!$office)
            return collect();
        return $office->getReceivingRoutes();
    });


    Route::any('/{officeId}/held', function (Request $req) {
        $officeId = $req->officeId;
        $office = App\Office::find($officeId);
        if (!$office)
            return collect();
        return $office->getActiveRoutes();
    });

    Route::any('/{officeId}/dispatched', function (Request $req) {
        $officeId = $req->officeId;
        $office = App\Office::find($officeId);
        if (!$office)
            return collect();
        return $office->getDispatchedRoutes();
    });

    Route::any('/{officeId}/final', function (Request $req) {
        $officeId = $req->officeId;
        $office = App\Office::find($officeId);
        if (!$office)
            return collect();
        return $office->getFinalRoutes();
    });

    Route::any('/search', function (Request $req) {
        $id = $req->q;
        $except = $req->except ?? [];
        $q = "%{$req->q}%";
        $offices = App\Office
            ::whereNotIn("id", $except)
            ->where("name", "like", $q)
            ->orWhereHas("Campus", function($query) use ($q) {
                $query->where("name", "like", $q);
            })
            ->orderBy("name")
            ->limit(10)
            ->get();
        return $offices;
    });

    Route::post('/{officeId}/action-for/{trackingId}',
        function (Request $req, $officeId, $trackingId) {
            $office = App\Office::find($officeId);
            $doc = App\Document::where("trackingId", $trackingId)->first();
            if ($office)
                return $office->actionFor($doc);
            return "";
        });

    Route::post('/{officeId}/action-for-route/{routeId}',
        function (Request $req, $officeId, $routeId) {
            $office = App\Office::find($officeId);
            $route = App\DocumentRoute::find($routeId);
            if ($office)
                return $office->actionForRoute($route);
            return "";
        });

    Route::any('/{officeId}/next-offices', function($officeId) {
        $office = \App\Office::find($officeId);
        if ($office)
            return $office->nextOffices();
        return collect();
    });

    Route::post('/{officeId}/can-receive/{trackingId}',
        function (Request $req, $officeId, $trackingId) {
            $office = App\Office::find($officeId);
            $doc = App\Document::where("trackingId", $trackingId)->first();
            if (!($office && $doc))
                return ["value" => false];
            return ["value" => $office->canReceiveDoc($doc)];
        });

    Route::any('/{officeId}/can-send/{trackingId}',
        function (Request $req, $officeId, $trackingId) {
            $office = App\Office::find($officeId);
            $doc = App\Document::where("trackingId", $trackingId)->first();
            if (!($office && $doc))
                return ["value" => false];
            return ["value" => $office->canSendDoc($doc)];
        });

    Route::post('/del/{id}', function (Request $req, $id) {
        $pos = App\Office::findOrFail($id);
        $pos->delete();
        return $pos;
    });

    Route::post('/get', function (Request $req) {
        return \App\Office::query()
            ->where("name",     $req->name)
            ->where("campusId", $req->campusId)
            ->first();

    });

    Route::post('/add', function (Request $req) {
        $office = new App\Office();
        $office->name = $req->name;
        $office->gateway = $req->gateway ?? 0;
        $office->campusId = $req->campusId;

        if ($req->campus_code && !$office->campusId) {
            $campus = \App\Campus::where("code", $req->campus_code)->first();
            if ($campus)
                $office->campusId = $campus->id;
        }

        $v = $office->validate();
        if ($v->fails())
            return ["errors"=>$v->errors()];

        $office->save();
        return $office;
    });

    Route::get('/list', function (Request $req) {
        return App\Office::all();
    });

});


Route
::prefix("campuses")
->middleware(["auth"])
->group(function() {
    Route::any('/{code}/get', function (Request $req, $code) {
        $campus = \App\Campus::find($code);
        if ($campus)
            return $campus;
        $campus = \App\Campus::where("code", $code)->first();
        return $campus;
    });

    Route::any('/add', function (Request $req) {
        $campus = new \App\Campus();
        $campus->code = $req->code;
        $campus->name = $req->name;
        $v = $campus->validate();
        if ($v->fails())
            return ["errors"=>$v->errors()];
        $campus->save();
        return $campus;
    });

    Route::any('/list', function (Request $req) {
        return \App\Campus::all();
    });

    Route::any('/{campusId}/offices', function (Request $req, $campusId) {
        return \App\Office::where("campusId", $campusId)->get();
    });

    Route::any('/search', function (Request $req) {
        $id = $req->q;
        $except = $req->except ?? [];
        $q = "%{$req->q}%";
        $campuses = App\Campus
            ::whereNotIn("id", $except)
            ->orderBy("name")
            ->limit(10)
            ->get();
        return $campuses;
    });
});

// -----------------------

Route
::prefix("dev")
->middleware([\App\Http\Middleware\LocalEnv::class])
->group(function() {
    Route::any('/create-dev-user', function (Request $req) {
        $username = "ronald";
        $user = \App\User::where("username", $username)->first();
        if ($user) {
            Auth::login($user);
            return $user;
        }

        $user = new \App\User();
        $user->username = $username;
        $user->password = "";
        $user->firstname = "ronald";
        $user->lastname = "casili";
        $user->positionId = 0;
        $user->privilegeId = 0;
        $user->officeId = 0;
        $user->save();
        Auth::login($user);
        return $user;
    });

    Route::any('/clean-db', function (Request $req) {
        return \App\Maint::cleanDB();
    });

    Route::any('/reset-tracking-id', function (Request $req) {
        // TODO: authorize admin
        \App\TrackingCounter::reset();
    });
});

// -----------------------

Route
::prefix("files")
->middleware(["auth"])
->group(function() {
    Route::any('/info/{id}', function (Request $req, $id) {
        return \App\File::find($id);
    });

    Route::any('/download/{id}/{filename?}',
        function (Request $req, $id, $filename="") {
            $file = \App\File::find($id);
            if (!$file)
                return abort();
            $path = disk()->path($file->path);
            return response()->file($path);
        })->name("download-file");

    Route::any('/upload', function (Request $req) {
        $fileData = $req->file("filedata");
        if ( ! $req->hasFile("filedata")) {
            return ["errors"=>["file"=>"no file is provided: {$req->filedata}"]];
        }

        if ( ! $fileData->isValid()) {
            return ["errors"=>["file"=>"upload failed"]];
        }
        $path = $fileData->store("uploads");
        $file = new \App\File();
        $file->name = $req->filename;
        $file->path = $path;
        $file->size = disk()->size($path);
        $file->save();

        return $file->id;
    });
});


Route
::prefix("globe-sms")
->group(function() {
    Route::any('/subscribe', function (Request $req) {
        \Log::debug("new globe api subscription" .$req->getContent());
        $data = json_decode($req->getContent());
        $subscriberNumber = $data->subscriberNumber;

        $number = \App\SubscribedNumber
            ::where("subscriberNumber", $subscriberNumber);

        if ( ! $number) {
            $number = new \App\SubscribedNumber();
        }

        $number->accessToken      = $data->access_token;
        $number->subscriberNumber = $subscriberNumber;
        $number->active = true;
        $number->save();
    });

    Route::any('/notify', function (Request $req) {
        // TODO: REMOVE THIS LATER
        \Log::debug("received globe api notification : " .$req->getContent());
        $data = json_decode($req->getContent());

        $messages = optional(@$data->inboundSMSMessageList)->inboundSMSMessage;
        if ( ! $messages)
            return;

        foreach ($messages as $data) {
            GlobeAPI::execute($data);
        }
    });
});

Route::any('/util/url-for/{routeName}', function (Request $req, $routeName) {
    try {
        $params = $req->toArray();
        unset($params["routeName"]);
        return [
            "url" => route($routeName, $params)
        ];
    } catch (\Exception $e) {
        return [
            "errors"=>["url"=>$e->getMessage()]
        ];
    }
});
