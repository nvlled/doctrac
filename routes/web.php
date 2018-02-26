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

Route::get('/tests', function () {
    return view('tests/api');
});

Route::middleware(['auth'])->group(function() {
    Route::get('/dispatch', function () {
        $user = Auth::user();
        if (!$user || !optional($user->office)->gateway)
            return redirect("/");
        return view('dispatch');
    });
    Route::get('/', function () {
        return view('home');
    });

    Route::get('/search', function() {
        return view('search');
    });
    Route::post('/search', function(Request $req) {
        $id = $req->trackingId;
        $doc = \App\Document::where("trackingId", $id)->first();
        if ($doc) {
            return redirect()->route("view-routes", $id);
        }
        return view('search', [
            "trackingId" => $id,
            "message"=>"invalid tracking id",
        ]);
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

        Route::get('/{trackingId}/routes', function($trackingId) {
            $doc = App\Document::where("trackingId", $trackingId)->firstOrFail();
            return view('routes', [
                "doc" => $doc,
            ]);
        })->name("view-routes");

        Route::get('/{id}/', function ($id) {
            $route = App\DocumentRoute::find($id);
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
                "doc" => $docJson,
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



