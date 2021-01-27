<?php

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

Route::middleware(['secureApi', 'responseHeader'])->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Page Not Found'], 404);
    });
    Route::post('login', 'PassportController@login');
    Route::post('register', 'Api\HomeController@register');

    Route::get('getList/{case}/{id?}', 'Api\HomeController@getList')->name('getList');
    Route::post('resend-verification-link', 'Api\HomeController@resendVerificationLink');
    Route::post('verification', 'Api\HomeController@verification');
    Route::post('password/email', 'PassportController@forgot');
    Route::post('password/reset', 'PassportController@resetPassword');
    Route::post('check-email', 'Api\HomeController@checkEmail')->name('checkEmail');
    Route::get('language/list', 'Api\LanguageController@list');
    Route::get('speacility/list', 'Api\SpecialityController@getList');
    Route::post('doctors-search', 'Api\DoctorController@doctorSearchList');
    Route::get('doctor/profile/{id}', 'Api\DoctorController@doctorProfile');
});

Route::middleware(['CheckAuthHeader', 'auth:api', 'secureApi', 'responseHeader'])->group(function () {
    /* common */
    Route::post('changepassword', 'Api\HomeController@changePassword');
    Route::post('reset-password', 'Api\HomeController@resetPassword');
    Route::get('user/delete/{id}', 'Api\HomeController@destroy');
    Route::post('upload/profile-image', 'Api\HomeController@uploadProfileImage');
    /* Admin Profile */
    Route::get('admin/profile/{id}', 'Api\HomeController@adminProfile');
    Route::post('admin/saveprofile/', 'Api\HomeController@saveProfile');

    /* Patient Module */
    Route::post('patient-search', 'Api\PatientController@patientSearchList');

    Route::get('patient/list', 'Api\PatientController@patientList');
    Route::post('patient/saveprofile', 'Api\PatientController@profile_update');
    Route::get('patient/profile/{id}', 'Api\PatientController@profile_details');

    //appointments
    Route::get('appointments/list', 'Api\AppointmentController@list');
    Route::post('appointments/create', 'Api\AppointmentController@create');
    Route::get('appointments/saved-cards','Api\AppointmentController@savedCards');
    Route::get('schedule/list', 'Api\AppointmentController@scheduleList');
    Route::post('schedule/create', 'Api\AppointmentController@scheduleCreate');
    Route::post('appointment/status/update', 'Api\AppointmentController@appointmentStatusUpdate');

    //invoice
    Route::get('invoice/list', 'Api\AppointmentController@invoiceList');


    /* Doctor Module */
    Route::get('doctor/dashboard', 'Api\DoctorController@dashboard');
    Route::get('doctor/list', 'Api\DoctorController@doctorList');

    Route::post('doctor/saveprofile', 'Api\DoctorController@saveProfile');

    /* Speciality */
    Route::group(['middleware' => ['can:specialization']], function () {
        Route::post('speacility/save', 'Api\SpecialityController@save');
        Route::get('speacility/delete/{id}', 'Api\SpecialityController@destroy');
    });
    /* signature */
    Route::get('signature/{id}','Api\AppointmentController@getsignature');
    /*Prescription */
    Route::post('prescription/save', 'Api\AppointmentController@savePrescription');
    Route::post('prescription/list', 'Api\AppointmentController@prescriptionList');
    Route::get('prescription/view/{pid}', 'Api\AppointmentController@prescriptionView');

    /* Medical Record */
    Route::post('record/save', 'Api\MedicalRecordController@save');
    Route::post('record/list/', 'Api\MedicalRecordController@getList');
    Route::get('record/view/{id}', 'Api\MedicalRecordController@getView');
    Route::get('record/delete/{id}', 'Api\MedicalRecordController@destroy');
    Route::get('record/download/{id}', 'Api\MedicalRecordController@getdownload');


    Route::get('logout', 'PassportController@logout');
    // Language
    Route::post('language/update', 'Api\LanguageController@update');

    Route::get('payment/request/list', 'Api\PaymentRequestController@list')->name('paymentList');
    Route::post('accounts/save', 'Api\PaymentRequestController@accountUpdate')->name('accountUpdate');
    Route::post('payment/request/create', 'Api\PaymentRequestController@requestPayment')->name('requestPayment');
    Route::post('payment/request/update', 'Api\PaymentRequestController@updatePaymentRequest')->name('updatePaymentRequest');
});

Route::any('{path}', function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
})->where('path', '.*');
