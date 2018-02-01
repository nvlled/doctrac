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

// TODO: Add update routes

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
    return pos;
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

