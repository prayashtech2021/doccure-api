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
function defaultLang(){
    $default_lang = Language::select('id')->get('is_default',1)->first();
    return $default_lang->id;
}
function getLangContent($page_master_id,$lang_id){
    $get = MultiLanguage::where('page_master_id',$page_master_id)->where('language_id',$lang_id)->get();
    $header = [];
    foreach($get as $value){
        $header[$value->keyword] = $value->value;
    }
    return $header;
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