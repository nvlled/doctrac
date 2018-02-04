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

// ---------------------------

Route::any('/routes/{trackId}', function (Request $req, $trackId) {
    return response()->json(
     App\DocumentRoute::where("trackingId", $trackId)->get()
    )->header("Content-Type", "application/json");
});

// ---------------------------
Route::any('/docs/send', function (Request $req) {
    $user = App\User::find($req->userId);
    if (!$user) {
        return ["errors"=>["user id"=>"user id is invalid"]];
    }

    $doc = new App\Document();
    $doc->title = $req->title;
    //$doc->type = $req->type;
    $doc->details = $req->details;
    $doc->trackingId = $req->trackingId;
    $doc->userId = $req->userId;

    $v = $doc->validate();
    if ($v->fails())
        return ["errors"=>$v->errors()];

    // at least one destination must be given
    $ids = $req->officeIds;
    if (!$ids) {
        $msg = "select at least one destination";
        return ["errors"=>["officeIds"=>$msg]];
    }

    $doc->save();

    // if there is no source office id given,
    // use the office id of the user
    $srcOfficeId = $req->srcOfficeId;
    if (!$srcOfficeId) {
        if (!App\Office::find($user->officeId))
            return ["errors"=>["srcOfficeId"=>"office id is invalid"]];
        $srcOfficeId = $user->officeId;
    }


    // TODO: undo DB changes on error
    
    $route = new App\DocumentRoute();
    $route->trackingId = $doc->trackingId;
    $route->srcOfficeId = $srcOfficeId;
    $route->srcUserId  = $user->id;
    $route->timeSent = now();
    $route->save();

    foreach ($ids as $officeId) {
        $nextRoute = new App\DocumentRoute();
        $nextRoute->trackingId = $doc->trackingId;
        // TODO: check if office id is valid
        $nextRoute->srcOfficeId = $officeId;
        $nextRoute->save(); // save first to get an ID

        $route->dstOfficeId = $officeId;
        $route->nextId     = $nextRoute->id;
        $nextRoute->prevId = $route->id;

        $route->save();
        $nextRoute->save();

        $route = $nextRoute;
    }
    $route->final = true;
    $route->save();

    return $doc;
});


// ---------------------------
Route::post('/users/del/{id}', function (Request $req, $id) {
    $user = App\User::findOrFail($id);
    $user->delete();
    return "okay";
});

Route::post('/users/add', function (Request $req) {
    $user = new App\User();
    $user->firstname = $req->firstname;
    $user->middlename = $req->middlename;
    $user->lastname = $req->lastname;
    $user->password = $req->password;
    $user->positionId = $req->positionId;
    $user->privilegeId = $req->privilegeId;
    $user->officeId = $req->officeId;

    $v = $user->validate();
    if ($v->fails())
        return ["errors"=>$v->errors()];

    $user->save();
    return $user;
});

Route::get('/users/list', function (Request $req) {
    return App\User::all();
});

Route::any('/users/get/{id}', function (Request $req, $id) {
    return App\User::find($id);
});


// ---------------------------
Route::post('/privileges/del/{id}', function (Request $req, $id) {
    $priv = App\User::findOrFail($id);
    $priv->delete();
    return $priv;
});

Route::post('/privileges/add', function (Request $req) {
    $priv = new App\Privilege();
    $priv->name = $req->name;

    $v = $priv->validate();
    if ($v->fails())
        return ["errors"=>$v->errors()];

    $priv->save();
    return $priv;
});

Route::get('/privileges/list', function (Request $req) {
    return App\Privilege::all();
});

// ---------------------------

Route::post('/positions/del/{id}', function (Request $req, $id) {
    $pos = App\Position::findOrFail($id);
    $pos->delete();
    return $pos;
});

Route::post('/positions/add', function (Request $req) {
    $pos = new App\Position();
    $pos->name = $req->name;

    $v = $pos->validate();
    if ($v->fails())
        return ["errors"=>$v->errors()];

    $pos->save();
    return $pos;
});

Route::get('/positions/list', function (Request $req) {
    return App\Position::all();
});

// -----------------------
Route::post('/offices/del/{id}', function (Request $req, $id) {
    $pos = App\Office::findOrFail($id);
    $pos->delete();
    return $pos;
});

Route::post('/offices/add', function (Request $req) {
    $office = new App\Office();
    $office->name = $req->name;
    $office->campus = $req->campus;

    $v = $office->validate();
    if ($v->fails())
        return ["errors"=>$v->errors()];

    $office->save();
    return $office;
});

Route::get('/offices/list', function (Request $req) {
    return App\Office::all();
});


// -----------------------
