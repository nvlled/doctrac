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

Route::middleware(['auth'])->group(function() {
    Route::get('/dispatch', function () {
        $user = Auth::user();
        if (!$user || !optional($user->office)->gateway)
            return redirect("/");
        return view('dispatch');
    });
    Route::get('/', function () {
        return view('home');
    })->middleware("auth");

    Route::get('/tests', function () {
        return view('tests/api');
    });

    Route::get('/search', function() {
        return view('search');
    })->middleware("auth");

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

    Route::get('/search/{trackingId?}', function ($trackingId="") {
        $doc = \App\Document::where("trackingId", $trackingId)->first();
        $json = null;
        if ($doc)
            $json = $doc->toJson();
        return view('search', [
            "doc"=>$json,
            "trackingId"=>$trackingId,
        ]);
    })->name("view-routes")->middleware("auth");

    Route::get('/document/{id}/', function ($id) {
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
            "user" => optional(Auth::user())->toJson(),
            "error" => $error,
        ]);
    })->name("view-document")->middleware("auth");
});

Route::get('/login', function () {
    return view('login');
});


