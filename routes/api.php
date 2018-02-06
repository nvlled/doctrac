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

Route::any('/routes/list/{trackingId}', function (Request $req, $trackingId) {
    return response()->json(
     App\DocumentRoute::where("trackingId", $trackingId)->get()
    )->header("Content-Type", "application/json");
});

// ---------------------------

Route::any('/docs/rand-id', function (Request $req) {
    $randId = strtolower(str_random(5));
    $id = $req->officeId;
    $office = App\Office::find($id);

    if ($office) {
        return strtolower("{$office->id}-{$office->name}-$randId");
    }
    return $randId;
});
Route::any('/docs/get/{trackingId}', function (Request $req, $trackingId) {
    return App\Document::where("trackingId", $trackingId)->first();
});

Route::any('/docs/current-routes/{trackingId}/', function (Request $req, $trackingId) {
    $doc = App\Document::where("trackingId", $trackingId)->first();
    if (!$doc)
        return collect();
    return $doc->currentRoutes();
});

Route::any('/docs/send', function (Request $req) {
    $user = App\User::find($req->userId);
    if (!$user) {
        return ["errors"=>["user id"=>"user id is invalid"]];
    }

    $doc = new App\Document();
    $doc->userId = $user->id;
    $doc->title = $req->title;
    $doc->type = $req->type;
    $doc->details = $req->details;
    $doc->trackingId = $req->trackingId;

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
    $officeId = $req->officeId;
    if (!$officeId) {
        if (!App\Office::find($user->officeId))
            return ["errors"=>["officeId"=>"office id is invalid"]];
        $officeId = $user->officeId;
    }


    // TODO: undo DB changes on error

    $route = new App\DocumentRoute();
    $route->trackingId  = $doc->trackingId;
    $route->officeId    = $officeId;
    $route->receiverId  = $user->id;
    $route->senderId    = $user->id;
    $route->pathId      = generateId();
    $route->arrivalTime = now();
    $route->save();

    foreach ($ids as $officeId) {
        $nextRoute = new App\DocumentRoute();
        $nextRoute->trackingId = $doc->trackingId;
        // TODO: check if office id is valid
        $nextRoute->officeId = $officeId;
        $nextRoute->pathId = generateId();
        $nextRoute->save(); // save first to get an ID

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
// TODO: session is not persiting data
Route::any('/users/self', function (Request $req) {
    return ["x" => session()->get("self")];
});

Route::any('/users/self/{userId}', function (Request $req, $userId) {
    $user = App\User::findOrFail($userId);
    session()->put("self", 1234);
    return session()->get("self");
});

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

Route::post('/offices/{officeId}/action-for/{trackingId}',
    function (Request $req, $officeId, $trackingId) {
    $office = App\Office::find($officeId);
    $doc = App\Document::where("trackingId", $trackingId)->first();
    return $office->actionFor($doc);
});

Route::post('/offices/{officeId}/abort/{trackingId}',
    function (Request $req, $officeId, $trackingId) {

});

Route::post('/offices/{officeId}/can-receive/{trackingId}',
    function (Request $req, $officeId, $trackingId) {
    $office = App\Office::find($officeId);
    $doc = App\Document::where("trackingId", $trackingId)->first();
    if (!($office && $doc))
        return ["value" => false];
    return ["value" => $office->canReceiveDoc($doc)];
});

Route::any('/offices/{officeId}/can-send/{trackingId}',
    function (Request $req, $officeId, $trackingId) {
    $office = App\Office::find($officeId);
    $doc = App\Document::where("trackingId", $trackingId)->first();
    if (!($office && $doc))
        return ["value" => false];
    return ["value" => $office->canSendDoc($doc)];
});

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
