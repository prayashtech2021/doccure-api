<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::middleware(['secureApi','responseHeader'])->group(function () {
    Route::get('/',function(){
        return response()->json(['message' => 'Page Not Found'], 404);
    });
    Route::post('register', 'HomeController@register');
    Route::post('login', 'PassportController@login');


});

Route::middleware(['CheckAuthHeader','auth:api','secureApi','responseHeader'])->group(function () {
    Route::post('change-password', 'Api\UserController@changePassword');
    Route::post('reset-password', 'Api\UserController@resetPassword');

    Route::get('patient/list','PatientController@list');
    Route::post('patient/profile_update','PatientController@profile_update');
    Route::get('patient/profile/{id}','PatientController@profile_details');

    //appointments
    Route::get('appointments/list','AppointmentController@list');
    Route::post('appointments/create','AppointmentController@create');


    Route::get('getList','HomeController@getList');
    Route::get('logout', 'PassportController@logout');

    //Route::middleware('role:doctor')->group(function () {
       Route::get('doctor/dashboard','DoctorController@dashboard')->name('Doctor.Dashboard');
    //});
});

Route::any('{path}', function() {
    return response()->json([
        'message' => 'Route not found'
    ], 404);
})->where('path', '.*');

