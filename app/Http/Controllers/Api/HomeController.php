<?php

namespace App\Http\Controllers;

use Validator;
use App\ { User,Country };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            $get_currency_code = Country::whereId($request->country_id)->get()->pluck('currency');
            if($get_currency_code){
                $array['currency_code'] = $get_currency_code[0];
            }
            $user = User::create($array);
            $user->assignRole($request->type);
            DB::commit();

            $response_array = [
                "code" => "200",
                "message" => "Registered Successfully",
                //"data" => $data,
            ];
    
            return response()->json(self::convertNullsAsEmpty($response_array), 200);

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $userid = auth()->user()->id;
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6',
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
    

}
