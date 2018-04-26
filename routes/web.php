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
    if (Auth::user())
        return redirect()->route("doc-lists");
    return redirect()->route("search");
});

Route::get('/lists', function(Request $req) {
    $listNames = [
        "all",
        "recent",
        "delivering",
        "waiting",
        "processing",
        "finished",
    ];
    $name = $req->name ?? "all";
    $office = optional(optional(Auth::user())->office);
    $routes = collect();

    switch ($name) {
    case "all":
        $routes = $office->getAllRoutes(); break;
    case "recent":
        $routes = $office->getRecentRoutes(); break;
    case "delivering":
        $routes = $office->getDeliveringRoutes(); break;
    case "waiting":
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

    Route::post('/search', function(Request $req) {
        $id = $req->trackingId;
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

        return view('routes', [
            "doc" => $doc,
            "office" => optional($user)->office,
            "action"=> $actionRes["action"] ?? "",
            "routeLink"=> $routeLink,
            "routes" => @$routeGraph["routes"] ?? [],
            "tree"   =>  @$routeGraph["tree"] ?? [],
            "user"   => $user,
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
        return view('admin');
    });

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
            if ($route) {
                $docJson = $route->toJson();
                $trackingId = $route->trackingId;
                try { $route->seenBy(Auth::user()); } catch (Exception $e) { }
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
    return redirect()->route("login");
})->name("logout");

Route::get('/scratch', function () {
    return view("scratch");
});

Route::get('/office-list', function () {
    return view('office-list', \App\Office::all());
});
