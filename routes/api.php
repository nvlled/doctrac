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
Route::post('/users/del/{id}', function (Request $req, $id) {
    $pos = App\User::find($id);
    $pos->delete();
    return "okay";
});

Route::post('/users/add', function (Request $req) {
    $pos = new App\User();
    $pos->firstname = $req->firstname;
    $pos->middlename = $req->middlename;
    $pos->lastname = $req->lastname;
    $pos->positionId = $req->positionId;
    $pos->privilegeId = $req->privilegeId;
    $pos->officeId = $req->officeId;

    // TODO: validate
    $errors = [];
    if (!$pos->name)
        $errors[] = "name is required";

    $pos->save();
    return $pos;
});

Route::get('/users/list', function (Request $req) {
    // TODO: lookup json serialization
    return App\User::all();
});


// ---------------------------
Route::post('/privileges/del/{id}', function (Request $req, $id) {
    $pos = App\Privilege::find($id);
    $pos->delete();
    return "okay";
});

Route::post('/privileges/add', function (Request $req) {
    $pos = new App\Privilege();
    $pos->name = $req->name;

    $errors = [];
    if (!$pos->name)
        $errors[] = "name is required";

    $pos->save();
    return $pos;
});

Route::get('/privileges/list', function (Request $req) {
    return App\Privilege::all();
});

// ---------------------------

Route::post('/positions/del/{id}', function (Request $req, $id) {
    $pos = App\Position::find($id);
    $pos->delete();
    return "okay";
});

Route::post('/positions/add', function (Request $req) {
    $pos = new App\Position();
    $pos->name = $req->name;

    $errors = [];
    if (!$pos->name)
        $errors[] = "name is required";

    $pos->save();
    return $pos;
});

Route::get('/positions/list', function (Request $req) {
    return App\Position::all();
});

// -----------------------
Route::post('/offices/del/{id}', function (Request $req, $id) {
    $pos = App\Office::find($id);
    $pos->delete();
    return "okay";
});

Route::post('/offices/add', function (Request $req) {
    $office = new App\Office();
    $office->name = $req->name;
    $office->campus = $req->campus;

    $errors = [];
    if (!$office->name)
        $errors[] = "name is required";
    if (!$office->campus)
        $errors[] = "campus is required";

    $office->save();
    return $office;
});

Route::get('/offices/list', function (Request $req) {
    return App\Office::all();
});

// -----------------------

