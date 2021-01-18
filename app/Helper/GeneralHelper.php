<?php

use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\ActivityLog;
use Illuminate\Support\Carbon;

function getUserProfileImage($user_id)
{
    $user = User::find($user_id);
    if ($user && !empty($user->profile_image) && Storage::exists('profile-images/' . $user->profile_image)) {
        return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('profile-images/' . $user->profile_image, now()->addMinutes(5)) : Storage::url('profile-images/' . $user->profile_image);

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
