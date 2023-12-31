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
    Route::post('login', 'PassportController@login')->name('MobileLogin');
    Route::post('register', 'Api\HomeController@register');

    Route::get('getList/{case}/{id?}', 'Api\HomeController@getList')->name('getGeneralList');
    Route::post('resend-verification-link', 'Api\HomeController@resendVerificationLink');
    Route::post('verification', 'Api\HomeController@verification')->name('verification');
    Route::post('password/email', 'PassportController@forgot');
    Route::post('password/reset', 'PassportController@resetPassword');
    Route::post('check-email', 'Api\HomeController@checkEmail')->name('checkEmail');
    Route::get('language/list', 'Api\LanguageController@list')->name('languageList');
    Route::get('speacility/list', 'Api\SpecialityController@getList');
    Route::get('features/list', 'Api\FeatureController@getList');
    Route::post('doctors-search', 'Api\DoctorController@doctorSearchList')->name('doctorSearch');
    Route::get('doctor/profile/{id}', 'Api\DoctorController@doctorProfile')->name('doctorProfile');
    Route::get('landing-page','Api\PageContentController@getList')->name('landingPage');
    Route::get('common-page','Api\HomeController@getCommonData');
    Route::get('page-setting','Api\SettingController@getPageSetting');
    // for blogs
    Route::get('post', 'Api\PostController@index');
    Route::get('post/view/{id}', 'Api\PostController@view');
    Route::get('allkeywords','Api\LanguageController@allKeywords')->name('languageKeyword');
    Route::get('doctors', 'Api\DoctorController@doctorList')->name('doctors');
});
Route::middleware(['CheckAuthHeader', 'auth:api', 'responseHeader'])->group(function () {
    Route::post('email-template/save','Api\EmailTemplateController@save');
    Route::post('post/save', 'Api\PostController@save');
    Route::post('settings/save','Api\SettingController@save');
});
Route::middleware(['CheckAuthHeader', 'auth:api', 'secureApi', 'responseHeader'])->group(function () {
    /* common */
    Route::get('auth-common-page','Api\HomeController@getCommonData');
    Route::post('changepassword', 'Api\HomeController@changePassword');
    Route::post('reset-password', 'Api\HomeController@resetPassword');
    Route::get('user/delete/{id}', 'Api\HomeController@destroy');
    Route::post('upload/profile-image', 'Api\HomeController@uploadProfileImage');
    /* Admin Profile */
    Route::get('admin/profile/{id}', 'Api\HomeController@adminProfile');
    Route::post('admin/saveprofile/', 'Api\HomeController@saveProfile');

    /* Patient Module */
    Route::post('patient-search', 'Api\PatientController@patientSearchList')->name('patientSearch');

    Route::get('patient/list', 'Api\PatientController@patientList')->name('patientList');
    Route::post('patient/saveprofile', 'Api\PatientController@profile_update');
    Route::get('patient/profile/{id}', 'Api\PatientController@profile_details')->name('patientProfile');

    //appointments
    Route::get('appointments/list', 'Api\AppointmentController@list')->name('appointmentList');
    Route::post('appointments/create', 'Api\AppointmentController@create')->name('appointmentCreate');
    Route::get('appointments/saved-cards','Api\AppointmentController@savedCards');
    Route::get('schedule/list', 'Api\AppointmentController@scheduleList');
    Route::post('schedule/create', 'Api\AppointmentController@scheduleCreate');
    Route::post('schedule/delete', 'Api\AppointmentController@scheduleDelete');
    Route::post('appointment/status/update', 'Api\AppointmentController@appointmentStatusUpdate');
    Route::get('appointment/calendar', 'Api\AppointmentController@calendarList');
    Route::post('call/log/save', 'Api\AppointmentController@saveCallLog')->name('saveCallLog');
    Route::post('call/log/update', 'Api\AppointmentController@updateCallLog')->name('updateCallLog');

    //invoice
    Route::get('invoice/list', 'Api\AppointmentController@invoiceList');
    Route::post('invoice/view', 'Api\AppointmentController@viewInvoice');

    /* Doctor Module */
    Route::get('doctor/dashboard', 'Api\DoctorController@dashboard');
    Route::get('doctor/list', 'Api\DoctorController@doctorList')->name('doctorList');
    Route::post('doctor/saveprofile', 'Api\DoctorController@saveProfile');
    Route::get('doctor/address-image/delete/{address_image_id}','Api\DoctorController@deleteAddressImage');

    /* Speciality */
    Route::group(['middleware' => ['can:specialization']], function () {
        Route::post('speacility/save', 'Api\SpecialityController@save');
        Route::get('speacility/delete/{id}', 'Api\SpecialityController@destroy');
    });
    /* signature */
    Route::get('signature/{id}','Api\AppointmentController@getsignature');
    /* Prescription */
    Route::post('prescription/save', 'Api\AppointmentController@savePrescription');
    Route::post('prescription/list', 'Api\AppointmentController@prescriptionList');
    Route::get('prescription/view/{pid}', 'Api\AppointmentController@prescriptionView');
    Route::get('prescription/delete/{pid}', 'Api\AppointmentController@prescription_destroy');

    /* Medical Record */
    Route::post('record/save', 'Api\MedicalRecordController@save');
    Route::post('record/list/', 'Api\MedicalRecordController@getList')->name('recordList');
    Route::get('record/view/{id}', 'Api\MedicalRecordController@getView');
    Route::get('record/delete/{id}', 'Api\MedicalRecordController@destroy');

    Route::get('logout', 'PassportController@logout')->name('mobileLogout');
    // Language
    Route::post('language/update', 'Api\LanguageController@update');
    Route::post('language/save','Api\LanguageController@save');
    Route::get('language/enable','Api\LanguageController@enableLang');

    /* Multi Language */
    Route::post('multi-language/edit','Api\LanguageController@multiLangEdit');
    Route::post('multi-language/save','Api\LanguageController@multiLangSave');

    Route::get('payment/request/list', 'Api\PaymentRequestController@list')->name('paymentList');
    Route::post('accounts/save', 'Api\PaymentRequestController@accountUpdate')->name('accountUpdate');
    Route::post('payment/request/create', 'Api\PaymentRequestController@requestPayment')->name('requestPayment');
    Route::post('payment/request/update', 'Api\PaymentRequestController@updatePaymentRequest')->name('updatePaymentRequest');
    /* Settings */
    Route::get('settings','Api\SettingController@getSetting');
    /* Dashboard */
    Route::get('admin/dashboard','Api\HomeController@adminDashboard');
    Route::get('patient/dashboard','Api\PatientController@patientDashboard');
    Route::get('patient/dashboard/count','Api\PatientController@mobileDashboard');
    Route::get('doctor/dashboard','Api\DoctorController@doctorDashboard');

    /* Reviews */
    Route::post('review/save','Api\ReviewController@save');
    Route::get('review/list','Api\ReviewController@getList')->name('reviewList');
    Route::get('review/delete/{id}','Api\ReviewController@destroy');
    Route::post('review/doctor-reply','Api\ReviewController@doctorReply');

    /*Email Template */
    Route::get('email-template/list','Api\EmailTemplateController@getList');
    Route::get('email-template/view/{id}','Api\EmailTemplateController@view');

    /* Social Media */
    Route::post('social-media/save','Api\SocialMediaController@save');
    Route::get('social-media/view/{provider_id}','Api\SocialMediaController@view');

    /* Favourite */
    Route::post('favourite/save','Api\PatientController@favouriteSave');
    Route::get('favourite/list','Api\PatientController@getFavouriteList')->name('favouriteList');

    /* Page Content */
    Route::post('page-content/save','Api\PageContentController@save');
    Route::get('page-content/list','Api\PageContentController@getList');

    //chat message
    Route::get('/message/list', 'Api\ChatController@index');
    Route::post('/message/send', 'Api\ChatController@send');

    /* Features */
    Route::post('features/save', 'Api\FeatureController@save');
    Route::get('features/delete/{id}', 'Api\FeatureController@destroy');
    /* Notification */
    Route::get('notification/list', 'Api\NotificationController@notificationList');
    Route::get('notification/read-all', 'Api\NotificationController@markNotificationsAsRead');
    
    //blogs
    Route::get('post/list', 'Api\PostController@index');
    Route::get('post/delete/{id}', 'Api\PostController@destroy');
    Route::post('post/verify', 'Api\PostController@verifyPost');
    Route::post('post/add/comment', 'Api\PostController@addComment');
    Route::get('subcategory', 'Api\PostController@getSubCategory');
    Route::get('comment/delete/{id}', 'Api\PostController@deleteComment');

    /*post category */
    Route::post('category/save', 'Api\PostCategoryController@save');
    Route::get('category/list', 'Api\PostCategoryController@getList');
    Route::get('category/delete/{id}', 'Api\PostCategoryController@destroy');

    /*post sub category */
    Route::post('sub-category/save', 'Api\PostSubCategoryController@save');
    Route::get('sub-category/list', 'Api\PostSubCategoryController@getList');
    Route::get('sub-category/delete/{id}', 'Api\PostSubCategoryController@destroy');

    Route::get('test','Api\HomeController@testing');
    
    /* Make Call */
    Route::get('make-call','Api\AppointmentController@makeCall');
    Route::get('pagetype','Api\LanguageController@getMobilePage');

    Route::post('create_setup_intent', 'Api\AppointmentController@create_setup_intent');

    Route::get('schedule/listfor/patient','Api\AppointmentController@scheduleListForPatient')->name('scheduleListPatient');

    Route::post('call-switch','Api\AppointmentController@callSwitch');

});

Route::any('{path}', function () {
    return response()->json([
        'message' => 'Route not found',
    ], 404);
})->where('path', '.*');
