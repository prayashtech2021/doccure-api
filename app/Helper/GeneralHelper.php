<?php

use App\User;
use App\Country;
use App\City;
use App\State;
use App\Language;
use App\MultiLanguage;
use App\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\ActivityLog;
use Illuminate\Support\Carbon;

function getUserProfileImage($user_id)
{
    $user = User::find($user_id);
    if ($user && !empty($user->profile_image) && Storage::exists('images/profile-images/' . $user->profile_image)) {
        return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/profile-images/' . $user->profile_image, now()->addMinutes(5)) : Storage::url('app/public/images/profile-images/' . $user->profile_image);

    } else {
        return URL::asset('img/profile_image.jpg');
    }
}


function getList($case){
    	if ($case) {
    		switch ($case) {
                case 'get_country' : 
                    $response = Country::select('id','name','phone_code','currency','emoji','emojiU')->toArray();
                    break;
                case 'get_states' : 
                    $response = State::pluck('id','name')->toArray(); 
                    break;
                case 'get_cities' : 
                    $response = City::pluck('id','name')->toArray(); 
                    break;
                default : 
                    $response = ['case' => $case, 'status' => 'Action not found']; 
                    break;
	    	}

	    } else {
            $response = ['status' => 'invalid request'];
        }

        return response()->json($response, 200);
}

function removeMetaColumn($model){
    $model->makeHidden(['created_by', 'updated_by', 'updated_at', 'deleted_by']);
}

/**
 * time conversion
 */

function convertToUTC(Carbon $date, $timezone = null, $format = null)
{
    if (!$timezone) $timezone = config('app.timezone');
    $datetime = Carbon::parse($date, $timezone)->timezone(new DateTimeZone('UTC'));
    return $format ? $datetime->format($format) : $datetime;
}

function convertToLocal(Carbon $date, $timezone = null, $format = null)
{
    if (!$timezone) $timezone = config('app.timezone');
    $datetime = Carbon::parse($date, new DateTimeZone('UTC'))->timezone($timezone);
    return $format ? $datetime->format($format) : $datetime;
}

function generateReference($user_id, $last_id, $prefix = '#', $length = 8)
{
    $next = $last_id + 1;
    $ref_length = $length - strlen($prefix . $user_id . '0' . $next);
    if ($ref_length <= 0) {
        return generateReference($prefix, $user_id, $last_id, $length + 4);
    }
    $pad_length = $length - strlen($prefix);
    return $prefix . str_pad($user_id . '0' . $next, $pad_length, "0", STR_PAD_LEFT);
}

function getLang($lang_id){
    $id = Language::find($lang_id);
    return ($id)? $lang_id : defaultLang();
}

function defaultLang(){
    $default_lang = Language::select('id')->get('is_default',1)->first();
    return $default_lang->id;
}
function getLangContent($page_master_id,$lang_id){
    $get = MultiLanguage::where('page_master_id',$page_master_id)->where('language_id',$lang_id)->get();
    $data = [];
    foreach($get as $value){
        $data[$value->keyword] = $value->value;
    }
    return $data;
}
function getSettingData(){
    $result = Setting::whereIn('slug',['general_settings','social_link'])->get();
    $setting = [];
    foreach($result as $data){
        if(($data->keyword=='company_logo') || ($data->keyword=='footer_logo') || ($data->keyword=='favicon') ){
            $setting[$data->keyword] = getSettingImage($data->value);
        }else{
            $setting[$data->keyword] = $data->value;
        }
    }
    return $setting;
}

function getSettingImage($image){
    if (!empty($image) && Storage::exists('images/company-images/' . $image)) {
        $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/company-images/' . $image, now()->addMinutes(5)) : Storage::url('app/public/images/company-images/' . $image);
    } else {
        $path = url('img/logo.png');
    }
    return $path;
}

function updateLastSeen($user){
    try{
        $user->last_seen_time=Carbon::now();
        $user->save();
        return true;
    } catch (\Exception | \Throwable $exception) {
        return false;
    }
}

function getAppMenu() {
    if (auth()->check()) {
        $menus = [];
        $lang = MultiLanguage::where(['page_master_id'=>1, 'language_id'=>auth()->user()->language_id])->get();
        if(auth()->user()->hasRole(['company_admin'])){
            $menus = [
                'dashboard' => ucwords($lang->first(function($item) {return $item->keyword == 'dashboard';})->value),
                'appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'appointments';})->value),
                'all_appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'all_appointments';})->value),
                'specialization' => ucwords($lang->first(function($item) {return $item->keyword == 'specialization';})->value),
                'doctors' => ucwords($lang->first(function($item) {return $item->keyword == 'doctors';})->value),
                'patients' => ucwords($lang->first(function($item) {return $item->keyword == 'patients';})->value),
                'payment_requests' => ucwords($lang->first(function($item) {return $item->keyword == 'payment_requests';})->value),
                'settings' => ucwords($lang->first(function($item) {return $item->keyword == 'settings';})->value),
                'email_template' => ucwords($lang->first(function($item) {return $item->keyword == 'email_template';})->value),
                'cms' => ucwords($lang->first(function($item) {return $item->keyword == 'cms';})->value),
                'language' => ucwords($lang->first(function($item) {return $item->keyword == 'language';})->value),
                'my_profile' => ucwords($lang->first(function($item) {return $item->keyword == 'my_profile';})->value),
                'change_password' => ucwords($lang->first(function($item) {return $item->keyword == 'change_password';})->value),
                'logout' => ucwords($lang->first(function($item) {return $item->keyword == 'logout';})->value),
            ];
        }elseif(auth()->user()->hasRole(['doctor'])){
            $menus = [
                'dashboard' => ucwords($lang->first(function($item) {return $item->keyword == 'dashboard';})->value),
                'appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'appointments';})->value),
                'all_appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'all_appointments';})->value),
                'my_patients' => ucwords($lang->first(function($item) {return $item->keyword == 'my_patients';})->value),
                'schedule_timings' => ucwords($lang->first(function($item) {return $item->keyword == 'schedule_timings';})->value),
                'calendar' => ucwords($lang->first(function($item) {return $item->keyword == 'calendar';})->value),
                'invoice' => ucwords($lang->first(function($item) {return $item->keyword == 'invoice';})->value),
                'accounts' => ucwords($lang->first(function($item) {return $item->keyword == 'accounts';})->value),
                'reviews' => ucwords($lang->first(function($item) {return $item->keyword == 'reviews';})->value),
                'chat' => ucwords($lang->first(function($item) {return $item->keyword == 'chat';})->value),
                'social_media' => ucwords($lang->first(function($item) {return $item->keyword == 'social_media';})->value),
                'patient_search' => ucwords($lang->first(function($item) {return $item->keyword == 'patient_search';})->value),
                'my_profile' => ucwords($lang->first(function($item) {return $item->keyword == 'my_profile';})->value),
                'change_password' => ucwords($lang->first(function($item) {return $item->keyword == 'change_password';})->value),
                'logout' => ucwords($lang->first(function($item) {return $item->keyword == 'logout';})->value),
            ];
        }elseif(auth()->user()->hasRole(['patient'])){
            $menus = [
                'dashboard' => ucwords($lang->first(function($item) {return $item->keyword == 'dashboard';})->value),
                'appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'appointments';})->value),
                'all_appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'all_appointments';})->value),
                'calendar' => ucwords($lang->first(function($item) {return $item->keyword == 'calendar';})->value),
                'invoice' => ucwords($lang->first(function($item) {return $item->keyword == 'invoice';})->value),
                'accounts' => ucwords($lang->first(function($item) {return $item->keyword == 'accounts';})->value),
                'chat' => ucwords($lang->first(function($item) {return $item->keyword == 'chat';})->value),
                'doctor_search' => ucwords($lang->first(function($item) {return $item->keyword == 'doctor_search';})->value),
                'my_profile' => ucwords($lang->first(function($item) {return $item->keyword == 'my_profile';})->value),
                'change_password' => ucwords($lang->first(function($item) {return $item->keyword == 'change_password';})->value),
                'logout' => ucwords($lang->first(function($item) {return $item->keyword == 'logout';})->value),
            ];
        }

        return $menus;
    }
    return [];
}
    
function getPostImage($image){
    if (!empty($image) && Storage::exists('images/blogs/' . $image)) {
        $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/blogs/' . $image, now()->addMinutes(5)) : Storage::url('app/public/images/blogs/' . $image);
    } else {
        $path = url('img/default_blog.png');
    }
    return $path;
}