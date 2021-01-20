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
    Route::post('login', 'PassportController@login');
    Route::post('register', 'Api\HomeController@register');
    
    Route::get('getList/{id}','Api\HomeController@getList')->name('getList');
    Route::post('resend-verification-link','Api\HomeController@resendVerificationLink');
    Route::post('verification','Api\HomeController@verification');
    Route::post('password/email', 'PassportController@forgot');
    Route::post('password/reset', 'PassportController@resetPassword');
    Route::post('check-email','Api\HomeController@checkEmail')->name('checkEmail');
    Route::get('language/list', 'Api\LanguageController@list');
});

    Route::middleware(['CheckAuthHeader','auth:api','secureApi','responseHeader'])->group(function () {
        /* common */
        Route::post('changepassword', 'Api\HomeController@changePassword');
        Route::post('reset-password', 'Api\HomeController@resetPassword');
        Route::get('user/delete/{id}','Api\HomeController@destroy');

        /* Admin Profile */
        Route::get('admin/Profile/{id}','Api\HomeController@adminProfile');
        Route::post('admin/saveProfile/','Api\HomeController@saveProfile');
        /* Patient Module */
        Route::get('patient/list','Api\PatientController@list');
        Route::post('patient/saveProfile','Api\PatientController@profile_update');
        Route::get('patient/profile/{id}','Api\PatientController@profile_details');
        
        //appointments
        Route::get('appointments/list','Api\AppointmentController@list');
        Route::post('appointments/create','Api\AppointmentController@create');

        /* Doctor Module */
        Route::get('doctor/dashboard','Api\DoctorController@dashboard')->name('Doctor.Dashboard');
        Route::get('doctor/Profile','Api\DoctorController@doctorProfile')->name('Doctor.Profile');
        Route::post('doctor/saveProfile','Api\DoctorController@saveProfile')->name('Doctor.saveProfile');
        Route::post('doctors-search','Api\DoctorController@doctorList')->name('Doctor.List');

        /* Speciality */
        Route::group(['middleware' => ['can:specialization']], function () {
            Route::post('speacility/save','Api\SpecialityController@save')->name('Speciality.save');
            Route::get('speacility/list','Api\SpecialityController@getList')->name('Speciality.getList');
            Route::get('speacility/delete/{id}','Api\SpecialityController@destroy')->name('Speciality.delete');
        });
        /*Prescription */
        Route::post('prescription/save','Api\AppointmentController@savePrescription');
        Route::post('prescription/list','Api\AppointmentController@savePrescription');
            
        Route::get('logout', 'PassportController@logout');        
        // Language 
        Route::post('language/update', 'Api\LanguageController@update');
    });

Route::any('{path}', function() {
    return response()->json([
        'message' => 'Route not found'
    ], 404);
})->where('path', '.*');

