<?php

use Illuminate\Support\Facades\Route;

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

Route::any('/', function () {
    // return abort(404);
});

Route::get('/send', function () {
    // event(new \App\Events\SendMessage());
    event(new \App\Events\SendMessage('score update'));
    dd('Event Run Successfully.');
});

Route::get('/list', function () {
return view('welcome');
});

