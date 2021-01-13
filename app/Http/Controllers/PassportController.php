<?php

namespace App\Http\Controllers;

use App\Mail\PasswordReset;
use App\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;
use Auth;

class PassportController extends Controller {

	public function login(Request $request) {

		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
			'password' => 'required',
		]);
		if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
		}
		$credentials = [
			'email' => $request->email,
			'password' => $request->password,
		];

		if (auth()->attempt($credentials)) {

			$user = auth()->user();
			$chktoken = $user->accessToken(function($qry){
				$qry->where('revoked',0);
			});
			if($chktoken){
				$userTokens = $user->tokens;
				foreach($userTokens as $token) {
					$token->revoke();
				}
			}
			$token = auth()->user()->createToken('APIAUTH')->accessToken;
			$tmp = $user->roles()->select('id', 'name')->get()->toArray();
			foreach ($tmp as $key => $row) {
				$arr[$key]['id'] = $row['id'];
				$arr[$key]['name'] = $row['name'];
			}
			$user->roles = $arr;
			$user->profile_image = getUserProfileImage($user->id);
			removeMetaColumn($user);
			dd($user->hasRole('doctor'));
			$menuList = $this->getMenu();
			dd($menuList);
			$response_array = [
				"code" => "200",
				"message" => "Logged Successfully",
				"data" => $user,
				"token" => $token,
			];
	
			return response()->json(self::convertNullsAsEmpty($response_array), 200);
			
		} else {
			$user = User::withTrashed()->where('email', $request->email)->first();
			if ($user && $user->trashed()) {
				$message = "Your account is not activated.";
                return self::send_bad_request_response($message);
			}
			$message = "Your account is not activated.";
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
		$url = "https://doccure-reactdemo.dreamguystech.com/reset/password/" . $user->id;
		
		Mail::to($user->email)->send(new PasswordReset(['url' => $url]));
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

	public function getMenu() {
		if (auth()->check()) {
			if(auth()->user()->hasRole(['company_admin'])){
				dd(2);
			}
		}

		$response_array = [
			"code" => "200",
			"message" => "Logged out Successfully",
			
		];
		return response()->json(self::convertNullsAsEmpty($response_array), 200);
	}
}
