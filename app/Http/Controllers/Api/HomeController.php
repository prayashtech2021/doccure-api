<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { User,Country,Address,State,City,Appointment };
use App\Mail\SendInvitation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use DB;
use Hash;
use Storage;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;


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
            $array['country_id'] = $request->country_id;
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
                    DB::beginTransaction();

                    User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                    if (auth()->check()) {
                        auth()->user()->token()->revoke();
                    }
                    DB::commit();
                    $response_array = [
                        "code" => "200",
                        "message" => "Password updated successfully.",
                    ];
            
                    return response()->json(self::convertNullsAsEmpty($response_array), 200);
                }
            } catch (\Exception | \Throwable $exception) {
                DB::rollback();
                return self::send_exception_response($exception->getMessage());
            }
        }
    }
    
    public function getList($case,$id=NULL){
        try {
            if ($case) {
                switch ($case) {
                    case '1' : 
                        $response = Country::select('id','name','phone_code','currency','emoji','emojiU')->get();
                        break;
                    case '2' : 
                        $state = State::select('id','name');
                        if(isset($id)){
                            $state = $state->where('country_id',$id);
                        }
                        $response = $state->get(); 
                        break;
                    case '3' : 
                        $city = City::select('id','name');
                        if(isset($id)){
                            $city = $city->where('state_id',$id);
                        }
                        $response = $city->get(); 
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
                    return self::send_bad_request_response('Email-Id Exists');
                }else{
                    return self::send_success_response('Email-Id Not Exists');
                }
            } catch (Exception | Throwable $exception) {
                return self::send_exception_response($exception->getMessage());
            }
        }
    }

    public function adminProfile($user_id){
        try{
            if($user_id){
                $list = User::select('id','first_name','last_name','email','mobile_number','profile_image','biography')
                ->whereId($user_id)->first();
                
                $data['profile'] = $list;
                if($data['profile']){            
                    $data['address'] = Address::with('country','state','city')->where('user_id',$user_id)->first();

                    return self::send_success_response($data,'Admin Profile Details Fetched Successfully');
                }else{
                    return self::send_unauthorised_request_response('Incorrect User Id, Kindly check and try again.');
                }
            }else{
                return self::send_bad_request_response('User Id not Exists');
            }
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    
    public function saveProfile(Request $request){
        try{    
            $user_id = $request->user_id;
            $rules = [
                'user_id' => 'required|integer|exists:users,id',
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'string|max:191',
                'email' => 'required|email|unique:users,email,'.$request->user_id,
                'country_id' => 'nullable|numeric|exists:countries,id',
                'state_id' => 'nullable|numeric|exists:states,id',
                'city_id' => 'nullable|numeric|exists:cities,id',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return self::send_bad_request_response($validator->errors()->first());
            }
            DB::beginTransaction();
            //Save admin profile
            $profile = User::find($user_id);
            if($profile){
                $profile->first_name = $request->first_name;
                $profile->last_name = $request->last_name;
                $profile->email = $request->email;
                $profile->biography = ($request->biography)? $request->biography : '';
                $profile->save();
                $get_address = Address::whereUserId($user_id)->whereNull('name')->first();

                if($get_address){
                    $address = $get_address;
                    $address->updated_by = auth()->user()->id;
                }else{
                    $address = new Address();
                    $address->user_id = $user_id;
                    $address->created_by = auth()->user()->id; 
                }
                $address->country_id = $request->country_id;
                $address->state_id = ($request->state_id) ? $request->state_id : '';
                $address->city_id = ($request->city_id) ? $request->city_id : '';
                $address->save();
                DB::commit();
                return self::send_success_response([],'Admin Profile Updated Successfully');
            }else{
                return self::send_unauthorised_request_response('Incorrect User Id, Kindly check and try again.');
            }
        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
        
    }

    public function uploadProfileImage(Request $request, $user_id = null)
    {
        $rules = array(
            'profile_image' => 'required',
        );
        
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();

            if ($user_id) {
                $user = User::find($user_id);
            } else {
                $user = User::find(auth()->user()->id);
            }

            if (!empty($request->profile_image)) {

                if(!empty($user->profile_image)){
                    if (Storage::exists('images/profile-images/' . $user->profile_image)) {
                        Storage::delete('images/profile-images/' . $user->profile_image);
                    }
                }
                if (preg_match('/data:image\/(.+);base64,(.*)/', $request->profile_image, $matchings)) {
                    $imageData = base64_decode($matchings[2]);
                    $extension = $matchings[1];
                    $file_name = date('YmdHis') . rand(100, 999) . '_' . $request->user_id . '.' . $extension;
                    $path = 'images/profile-images/' . $file_name;
                    Storage::put($path, $imageData);

                    $user->profile_image = $file_name;
                    $user->save();
                } else {
                    return self::send_bad_request_response('Image Uploading Failed. Please check and try again!');
                }
            }
            
            $user->updated_by = auth()->user()->id;
            $user->save();
            DB::commit();

            return self::send_success_response([],'Image updated Successfully');


        } catch (Exception | Throwable $e) {
            DB::rollback();
            return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);

        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\User', $request->id);
    }

    public function adminDashboard(Request $request){
        try {
            $user_id = auth()->user()->id;
            if($user_id){
                $doctor = User::role('doctor')->count();
                $patient = User::role('patient')->count();
                $appointment = Appointment::count();

                
                $myRequest = new Request();
                $myRequest->request->add([
                    
                    'count_per_page' => ($request->count_per_page)? $request->count_per_page : '', 
                    'page'=> ($request->page)? $request->page : '', 
                    'order_by'=> ($request->order_by)? $request->order_by : '', 
                    'appointment_status'=> ($request->appointment_status)? $request->appointment_status : '', 
                    'request_type'=> ($request->request_type)? $request->request_type : '', 
                    'appointment_date'=> ($request->appointment_date)? $request->appointment_date : '', 
                ]);

                $patient_result = (new PatientController)->patientList($myRequest);
                $doctor_result = (new DoctorController)->doctorList($myRequest);
                $app_result = (new AppointmentController)->list($myRequest);

                $result = [ 
                    'doctor' => $doctor, 
                    'patient' => $patient,
                    'appointment' => $appointment, 
                    'patient_list'=>$patient_result,
                    'doctor_list'=>$doctor_result,
                    //'app_list'=>$app_result
                ];
                return self::send_success_response($result);
            }else{
                $message = "Unauthorised request.";
                return self::send_unauthorised_request_response($message);
            }
        } catch (\Exception | \Throwable $exception) {
           return self::send_exception_response($exception->getMessage());
        }
    }
}
