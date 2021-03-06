<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;
use \App\SearchRetry;

Route::get('/tests', function () {
    return view('tests/api');
});

Route::get('/', function() {
    $user = Auth::user();
    if ($user) {
        if ($user->office)
            return redirect()->route("doc-lists");
        else if ($user->isAdmin())
            return redirect()->route("admin");
    }
    return redirect()->route("search");
});

Route::get('/lists', function(Request $req) {
    $listNames = [
        "recent",
        "all",
        "outgoing",
        "ingoing",
        "processing",
        "finished",
    ];
    $name = $req->name ?? "recent";
    $office = optional(optional(Auth::user())->office);
    $routes = collect();

    switch ($name) {
    case "all":
        $routes = $office->getAllRoutes(); break;
    case "recent":
        $routes = $office->getRecentRoutes(); break;
    case "outgoing":
        $routes = $office->getDeliveringRoutes(); break;
    case "ingoing":
        $routes = $office->getIncomingRoutes(); break;
    case "processing":
        $routes = $office->getProcessingRoutes(); break;
    case "finished":
        $routes = $office->getFinalRoutes(); break;
    }

    return view('doc-lists', [
        "page" => $req->page,
        "listNames" => $listNames,
        "currentName" => $name,
        "docs" => $routes,
    ]);
})->name("doc-lists");

Route
::middleware(["restrict-doc"])
->group(function() {
    Route::get('/lounge', function() {
        return view('lounge');
    })->name("search");
    Route::get('/lounge/archive', function(Request $req) {
        $page     = $req->page ?? 1;
        $pageSize = 25;
        $messages = \App\ChatMessage::orderByDesc("created_at")->paginate($pageSize);
        return view('chat-archive', [
            "page" => $page,
            "messages" => $messages
        ]);
    })->name("search");

    Route::get('/search', function() {
        return view('search');
    })->name("search");

    Route::get('/search-history', function() {
        return view('search-history');
    })->name("search-history");

    Route::post('/search', function(Request $req) {
        $id = $req->trackingId;
        if (!$id)
            return view('search');
        $doc = \App\Document::where("trackingId", $id)->first();

        $retries = SearchRetry::count();
        $msg = "invalid tracking id ($retries)";

        if (SearchRetry::canReset())
            SearchRetry::reset();

        if (SearchRetry::allowed()) {
            if ($doc) {
                SearchRetry::reset();
                return redirect()->route("view-routes", $id);
            } else {
                SearchRetry::increment();
            }
        }

        if ($retries >= \App\Config::$searchRetryLimit) {
            $min = SearchRetry::minutesLeft();
            $msg = "You have exceeded your search retries. Try again in $min minute(s)";
        }

        return view('search', [
            "trackingId" => $id,
            "message"=>$msg,
        ]);
    });

    Route::get('/{trackingId}/routes', function($trackingId) {
        $doc = App\Document::where("trackingId", $trackingId)->firstOrFail();
        $user = optional(Auth::user());
        $api = api();
        $actionRes = $api->actionFor($doc, $user->office);
        $routeGraph = $api->getRouteGraph($doc);
        $routeLink = @$actionRes["routeId"]
            ? $routeLink = route("view-document", @$actionRes["routeId"])
            : "";

        api()->readNotification(request());

        $office = $user->office;
        list($seen, $route) = $api->seeDocument($office, $doc);
        if ($seen) {
            \Flash::add("document first seen");
            $prevRoute = $route->prevRoute;
            if ($prevRoute) {
                \App\Notif::seen(
                    $prevRoute->office,
                    $route->office,
                    $route
                );
            }
        }

        $logs = $doc->routeActivityLogs();
        $currentRoute = $api->currentRoute($trackingId, $user->officeId);

        return view('routes', [
            "doc" => $doc,
            "currentRoute" => $currentRoute,
            "office" => optional($user)->office,
            "action"=> $actionRes["action"] ?? "",
            "routeLink"=> $routeLink,
            "routes" => @$routeGraph["routes"] ?? [],
            "tree"   =>  @$routeGraph["tree"] ?? [],
            "user"   => $user,
            "logs" => $logs,
        ]);
    })->name("view-routes");
});

Route::middleware(['auth'])->group(function() {
    Route::get('/dispatch', function () {
        $user = Auth::user();
        if (!$user || !optional($user->office)->gateway)
            return redirect("/");
        return view('dispatch');
    });

    Route::get('/notifications', function(Request $req) {
        $page = $req->page ?? 1;
        $notifications = api()->getNotifications($page);
        return view('notifications', [
            "notifications"=>$notifications,
        ]);
    });

    Route::get('/office/{username}', function(Request $req, $username) {
    });

    Route::get('/change-password', function (Request $req) {
        return view("change-pass", [
            "errors" => [],
        ]);
    });
    Route::post('/change-password', function (Request $req) {
        $api = api();
        $api->changeUserPassword($req->oldpass, $req->newpass1, $req->newpass2);
        $errors = $api->getErrors();
        if ($errors) {
            return view("change-pass", [
                "data"=>$req->toArray(),
                "errors"=>$errors,
            ]);
        }
        \Flash::add("password updated");
        return redirect("/settings");
    });

    Route::get('/admin', function () {
        $user = Auth::user();
        if (!$user)
            return redirect("/login");
        if (!$user->isAdmin()) {
            Flash::addError("access denied");
            return redirect("/");
        }
        $users = \App\User::all();
        return view('admin', [
            "users" => $users,
            "campuses"=>\App\Campus::all(),
            "offices"=>\App\Office::all(),
        ]);
    })->name("admin");

    Route::post('/admin', function (Request $req) {
        $user = Auth::user();
        if (!$user)
            return redirect("/login");
        if (!$user->isAdmin()) {
            Flash::addError("access denied");
            return redirect("/");
        }
        $users = \App\User::all();

        $username  = trim($req->username);
        $password  = trim($req->password);
        $password2 = trim($req->password2);

        $error = null;
        if (!$username) {
            $error = "username is required";
        }
        if (!$password) {
            $error = "password is required";
        }
        if ($password != $password2) {
            $error = "password does not match";
        }

        try {
            $newUser = new \App\User();
            $newUser->username = $username;
            $newUser->password = bcrypt($password);
            $newUser->firstname = $req->firstname ?? $username;
            $newUser->middlename = $req->middlename;
            $newUser->lastname = $req->lastname;
            $newUser->privilegeId = !!$req->admin ? 0 : -1;
            $newUser->save();
            Flash::add("account created: $username");
        } catch (\Exception $e) {
            $error = $e->getMessage();
            if ($e->errorInfo[0] === "23000" ) {
                $error = "username is taken";
            } else {
                $error = $e->errorInfo[2];
            }
        }

        return view('admin', [
            "data" => $req,
            "users" => $users,
            "error" => $error,
        ]);
    });

    Route::get('/admin/offices', function (Request $req) {
        return view("admin.offices");
    })->name("admin-offices");


    Route::get('/proto', function () {
        return view('proto');
    });

    Route::get('/proto2', function () {
        return view('proto2');
    });

    Route::get('/settings', function () {
        return view('settings');
    });

    Route::prefix("document")->group(function() {

        Route::get('/{id}/', function ($id) {
            $route = App\DocumentRoute::findOrFail($id);
            $error = "";
            $docJson = "";
            $trackingId = "";

            api()->readNotification(request());

            if ($route) {
                $docJson = $route->toJson();
                $trackingId = $route->trackingId;
                $office = optional(Auth::user())->office;
                $seen = api()->seeRoute($office, $route);
                if ($seen) {
                    \Flash::add("document first seen");
                    $prevRoute = $route->prevRoute;
                    if ($prevRoute) {
                        \App\Notif::seen(
                            $prevRoute->office,
                            $route->office,
                            $route
                        );
                    }
                }
            } else {
                $error = "document not found: $id";
            }

            return view('document', [
                "trackingId"=>$trackingId,
                "document" => $route->document,
                "doc" => $docJson,
                "route" => optional($route),
                "routeId" => optional($route)->id,
                "user" => optional(Auth::user())->toJson(),
                "error" => $error,
            ]);
        })->name("view-document");
    });
});

Route::get('/login', function () {
    return view('login');
})->name("login");

Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route("search");
})->name("logout");

Route::get('/scratch', function () {
    return view("scratch");
});

Route::get('/office-list', function () {
    return view('office-list', \App\Office::all());
});
