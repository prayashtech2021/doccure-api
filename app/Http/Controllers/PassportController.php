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
			return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
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
			// dd($arr);
			return response()->json([
				'success' => true, 'code' => 200,
				'token' => $token,
				'user' => $user,
			]);
		} else {
			$user = User::withTrashed()->where('email', $request->email)->first();
			if ($user && $user->trashed()) {
				return response()->json(['success' => false, 'code' => 401, "error" => 'Your account is not activated.']);
			}
			return response()->json(['success' => false, 'code' => 401, 'error' => 'UnAuthorised']);
		}
	}

	public function logout() {
		if (auth()->check()) {
			auth()->user()->token()->revoke();
		}
		return response()->json(['success' => true, 'code' => 200, 'message' => 'Logged out Successfully']);
	}

	public function forgot(Request $request) {

		$validator = Validator::make($request->all(), [
			'email' => 'required|email|exists:users,email',
		]);
		if ($validator->fails()) {
			return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
		}
		$user = User::withTrashed()->where('email', $request->email)->first();
		if ($user->trashed()) {
			return response()->json(['success' => false, 'code' => 401, "error" => 'Your account is not activated.']);
		}
		$url = "https://taxidemo.dreamguystech.com/reset/password/" . $user->id;
		Mail::to($user->email)->send(new PasswordReset(['url' => $url]));

		return response()->json(['success' => true, 'code' => 200, "message" => 'Reset password link sent on your email id.']);
	}

	public function resetPassword(Request $request) {
		$input = $request->all();

		$rules = array(
			'user_id' => 'required|exists:users,id',
			'new_password' => 'required|min:6',
			'confirm_password' => 'required|same:new_password',
		);
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
		} else {
			try {
				User::where('id', $input['user_id'])->update(['password' => Hash::make($input['new_password'])]);
				return response()->json(['success' => true, 'code' => 200, "message" => "Password reset successfully."]);
			} catch (\Exception | \Throwable $exception) {
				return response()->json(['success' => false, 'code' => 500, 'error' => $exception->getMessage()]);
			}
		}
	}

}
