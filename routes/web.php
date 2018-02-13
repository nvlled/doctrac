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

Route::get('/', function () {
    return view('home');
});

Route::get('/search', function () {
    return view('search');
});

Route::get('/dispatch', function () {
    return view('dispatch');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/admin', function () {
    return view('admin');
});

Route::get('/proto', function () {
    return view('proto');
});
