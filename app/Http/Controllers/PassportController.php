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

class PassportController extends Controller {

	public function login(Request $request) {

		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
			'password' => 'required',
			'type' => 'required|in:0,1', //1=>admin login
		]);
		if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
		}
		$credentials = [
			'email' => $request->email,
			'password' => $request->password,
			'is_verified' => 1,
		];
		
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
			$token = auth()->user()->createToken('APIAUTH')->accessToken;
			$tmp = $user->roles()->select('id', 'name')->get()->toArray();
			foreach ($tmp as $key => $row) {
				$arr[$key]['id'] = $row['id'];
				$arr[$key]['name'] = $row['name'];
			}
			$user->role_names = $arr;
			removeMetaColumn($user);
			

			//$menuList = $this->getAppMenu();
			// dd($menuList);
			$response_array = [
				"code" => "200",
				"message" => "Logged Successfully",
				"token" => $token,
				"data" => $user,
				//"menu_list" => $menuList,
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
					"message" => "User email-id is not verified",
				];
				return response()->json(self::convertNullsAsEmpty($response_array), 200);
            }
			$message = "No Records Found.";
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
		$url = "https://doccure-frontend.dreamguystech.com/resetpassword/".$user->id;
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

	public function getAppMenu() {
		if (auth()->check()) {
			$menus = [];
			$lang = MultiLanguage::where(['page_master_id'=>1, 'language_id'=>auth()->user()->language_id])->get();
			if(auth()->user()->hasRole(['company_admin'])){
				$menus = [
					'dashboard' => ucwords($lang->first(function($item) {return $item->keyword == 'dashboard';})->value),
					'appointments' => ucwords($lang->first(function($item) {return $item->keyword == 'appointments';})->value),
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
}
