<?php

namespace App\Http\Controllers;

use App\Mail\PasswordReset;
use App\User;
use App\MultiLanguage;
use App\EmailTemplate;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use App\TimeZone;
class PassportController extends Controller {

	public function login(Request $request) {
		
		$validator = Validator::make($request->all(), [
			'email' => 'required',
			'password' => 'required',
			'type' => 'required|in:0,1', //1=>admin login
		]);
		
		if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
		}
		
		if(is_numeric($request->email)){
			$credentials = ['mobile_number'=>$request->email,'password'=>$request->password, 'is_verified' => 1];
		}elseif (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
			$credentials = ['email' => $request->email, 'password'=>$request->password, 'is_verified' => 1];
		}else{
			return self::send_bad_request_response('Mobile number or Email is invalid');
		}
		
		if (auth()->attempt($credentials)) {
			$user = auth()->user();
			if($request->type==0 && $user->hasRole('company_admin')){
                return self::send_bad_request_response('You are not authorized to login here.');
			}
			// $chktoken = $user->accessToken(function($qry){
			// 	$qry->where('revoked',0);
			// });
			// if($chktoken){
			// 	$userTokens = $user->tokens;
			// 	foreach($userTokens as $token) {
			// 		$token->revoke();
			// 	}
			// }
			$user->last_seen_time=Carbon::now();
			if($request->route()->getName() == 'MobileLogin'){
				if($request->device_id && $request->device_type){
					$user->device_id = $request->device_id;
					$user->device_type = $request->device_type;
				}else{
					return self::send_bad_request_response('Device Id and Device Type are required');
				}
			}
			if($request->timezone){
				$time = TimeZone::where('name',$request->timezone)->first();
				$user->time_zone_id = $time->id;
			}
			$user->save();
			$token = auth()->user()->createToken('APIAUTH')->accessToken;
			$tmp = $user->roles()->select('id', 'name')->get()->toArray();
			foreach ($tmp as $key => $row) {
				$arr[$key]['id'] = $row['id'];
				$arr[$key]['name'] = $row['name'];
			}
			$user->role_names = $arr;
			$user->time_zone_name = ($user->time_zone_id) ? $user->timezone->name : '';

			removeMetaColumn($user);
			if($request->route()->getName() == 'MobileLogin'){
				$user->permanentaddress_mobile = ($user->getPermanentAddressAttribute()) ? $user->getPermanentAddressAttribute(1) : (object)[];
				$data = $user->toArray();
			}else{
				$data = $user;
			}
			
			$response_array = [
				"code" => "200",
				"message" => "Logged In Successfully",
				"token" => $token,
				"data" => $data,
			];
			unset($user->tokens);
			unset($user->roles);
			return response()->json(self::convertNullsAsEmpty($response_array), 200);
			
		} else {
			$user = User::withTrashed()->where('email', $request->email)->first();
			if ($user && $user->trashed()) {
				$message = "Your account is not activated.";
                return self::send_bad_request_response($message);
			}elseif($user && ($user->is_verified ==0)){
				$response_array = [
					"code" => "201",
					"message" => "User email-id is not yet verified. Check your registered email to verify.",
				];
				return response()->json(self::convertNullsAsEmpty($response_array), 200);
            }
			$message = "User is not Registered.";
            return self::send_unauthorised_request_response($message);
		}
	}

	public function logout() {
		if (auth()->check()) {
			auth()->user()->token()->revoke();
		}

		$response_array = [
			"code" => "200",
			"message" => "Logged out Successfully",
			
		];
		return response()->json(self::convertNullsAsEmpty($response_array), 200);
	}

	public function forgot(Request $request) {

		$validator = Validator::make($request->all(), [
			'email' => 'required|email|exists:users,email',
		]);
		if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
		}
		$user = User::withTrashed()->where('email', $request->email)->first();
		if ($user->trashed()) {
			$message = "Your account is not activated.";
            return self::send_bad_request_response($message);
		}
		$url = config('custom.frontend_url').'resetpassword/'.$user->id;
		$template = EmailTemplate::where('id',2)->first();
		if($template){
			$body = ($template->content); // this is template dynamic body. You may get other parameters too from database. $title = $template->title; $from = $template->from;
		
			$a1 = array('{{username}}','{{link}}','{{config_app_name}}','{{custom_support_phone}}','{{custom_support_email}}');
			$a2 = array($user->first_name,$url,config('app.name'),config('custom.support_phone'),config('custom.support_email'));

			$response = str_replace($a1,$a2,$body); // this will replace {{username}} with $data['username']
			
			$mail = [
				'body' => html_entity_decode(htmlspecialchars_decode($response)),
				'subject' => $template->subject,
			];

			$mailObject = new PasswordReset($mail); // you can make php artisan make:mail MyMail
			Mail::to($user->email)->send($mailObject);
		}
		$response_array = [
			"code" => "200",
			"message" => "Reset password link sent on your email id.",
		];
		return response()->json(self::convertNullsAsEmpty($response_array), 200);
	}

	public function resetPassword(Request $request)
	{
        $input = $request->all();
        
        $rules = array(
	    	'user_id' => 'required|integer',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        } else {
            try {
				$userid = $request->user_id;
				if (User::where('id', $userid)->exists()) {
						User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
						$response_array = [
							"code" => "200",
							"message" => "Password reset successfully.",
						];
						return response()->json(self::convertNullsAsEmpty($response_array), 200);
				} else {
					$message = "User not found";
					return self::send_bad_request_response($message);
		    	}
            } catch (\Exception | \Throwable $exception) {
                return self::send_exception_response($exception->getMessage());
            }
        }
    }

	
}
