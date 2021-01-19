<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { User,Country };
use App\Mail\SendInvitation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use DB;
use Hash;

class HomeController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'required|string|max:191',
                'email' => 'required|email|unique:users',
                'country_id' => 'required|integer',
                'mobile_number' => 'required|min:10|max:10|unique:users',
                'password' => 'required|confirmed|min:6|string',
                'type' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return self::send_bad_request_response($validator->errors()->first());
            }
            DB::beginTransaction();
            $array=$request->toArray();
            $array['password'] = Hash::make($request->password);
            $array['created_by'] = 1; //test
            $array['currency_code'] = Country::getCurrentCode($request->country_id);

            $verification_code = mt_rand(100000,999999);
            $array['verification_code'] = $verification_code;
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $token = substr(str_shuffle(str_repeat($pool, 5)), 0, 20);
            $array['remember_token'] = $token;
            $user = User::create($array);
            $user->assignRole($request->type);
            DB::commit();
            
            $url = "https://doccure-frontend.dreamguystech.com/verifymail/".$user->id.'/'.$token;

            $mail = [
                'url' => $url,
                'verification_code' => $verification_code,
            ];
            Mail::to($request->email)->send(new SendInvitation($mail));

            $response_array = [
                "code" => "200",
                "message" => "Registered Successfully",
            ];
    
            return response()->json(self::convertNullsAsEmpty($response_array), 200);

        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function resendVerificationLink(Request $request){
        $rules = array(
            'email' => 'required|email',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        } else {
            try {
                DB::beginTransaction();
                $user = User::where('email',$request->email)->first();
                if($user && ($user->is_verified == 0)){

                    $verification_code = mt_rand(100000,999999);
                    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
                    $token = substr(str_shuffle(str_repeat($pool, 5)), 0, 20);
                    $user->remember_token = $token;
                    $user->verification_code = $verification_code;
                    $user->save();

                    $url =  "https://doccure-frontend.dreamguystech.com/verifymail/".$user->id.'/'.$token;

                    $mail = [
                        'url' => $url,
                        'verification_code' => $verification_code,
                    ];
                    Mail::to($request->email)->send(new SendInvitation($mail));

                    DB::commit();
                    
                    return self::send_success_response([],'Resent Verification Mail Sucessfully');
                }elseif($user && ($user->is_verified == 1)){
                    return self::send_bad_request_response('User Email id Already Verified');
                }else{
                    return self::send_bad_request_response('User not found');
                }
            } catch (Exception | Throwable $exception) {
                DB::rollback();
                return self::send_exception_response($exception->getMessage());
            }
        }
    }

    public function verification(Request $request){
        $rules = array(
            'user_id' => 'required',
            'verification_code' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        } else {
            try {
                DB::beginTransaction();
                $user = User::whereId($request->user_id)->where('verification_code',$request->verification_code)->first();
                if($user){
                    $user->is_verified = 1;
                    $user->save();
                    DB::commit();
                    return self::send_success_response($user,'User Email Verified Sucessfully');
                }else{
                    return self::send_bad_request_response('Invalid User id or verification code provided');
                }
            } catch (Exception | Throwable $exception) {
                DB::rollback();
                return self::send_exception_response($exception->getMessage());
            }
        }

    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $userid = auth()->user()->id;
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6|string',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        } else {
            try {
                
                if ((Hash::check(request('old_password'), auth()->user()->password)) == false) {
                    $message = "Check your old password.";
                    return self::send_bad_request_response($message);
                } else if ((Hash::check(request('new_password'), auth()->user()->password)) == true) {
                    $message = "Please enter a password which is not similar then current password.";
                    return self::send_bad_request_response($message);
                } else {
                    User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                   
                    $response_array = [
                        "code" => "200",
                        "message" => "Password updated successfully.",
                    ];
            
                    return response()->json(self::convertNullsAsEmpty($response_array), 200);
                }
            } catch (\Exception | \Throwable $exception) {
                return self::send_exception_response($exception->getMessage());
            }
        }
    }
    
    public function getList($case){
        try {
            if ($case) {
                switch ($case) {
                    case '1' : 
                        $response = Country::select('id','name','phone_code','currency','emoji','emojiU')->get();
                        break;
                    case '2' : 
                        $response = State::pluck('id','name')->get(); 
                        break;
                    case '3' : 
                        $response = City::pluck('id','name')->get(); 
                        break;
                    default : 
                        $response = ['case' => $case, 'status' => 'Action not found']; 
                        break;
                }
            } else {
                $response = ['status' => 'invalid request'];
            }
    
            return self::send_success_response($response);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }

    }

    public function checkEmail(Request $request){
        $rules = array(
            'email' => 'required|email',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        } else {
            try{
                $check_user = User::where('email',$request->email)->first();
                if($check_user){
                    return self::send_success_response([],'Email-Id Exists');
                }else{
                    return self::send_unauthorised_request_response('Email-Id Not Exists');
                }
            } catch (Exception | Throwable $exception) {
                return self::send_exception_response($exception->getMessage());
            }
        }
    }
    
    public function destroy(Request $request)
    {
        return self::customDelete('\App\User', $request->id);
    }
}
