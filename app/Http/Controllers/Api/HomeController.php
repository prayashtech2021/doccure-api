<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { User,Country,Address,State,City,Appointment,Payment,EmailTemplate };
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
use App\PageContent;
use App\TimeZone;

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
                'mobile_number' => 'required|min:7|max:15|unique:users',
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
            if($request->timezone){
				$array['time_zone'] = $request->timezone;
			}
            $user = User::create($array);
            $user->assignRole($request->type);

            /*add a row in address table*/
            $contact_details = new Address();
            $contact_details->user_id = $user->id;
            $contact_details->country_id = $request->country_id;
            $contact_details->created_by = $user->id;
            $contact_details->save();

            DB::commit();
            
            $url = config('custom.frontend_url').'verifymail/'.$user->id.'/'.$token;

            $template = EmailTemplate::where('slug','registration')->first();
            if($template){
                $body = ($template->content); // this is template dynamic body. You may get other parameters too from database. $title = $template->title; $from = $template->from;
            
                $a1 = array('{{username}}','{{verification_code}}','{{link}}','{{config_app_name}}','{{custom_support_phone}}','{{custom_support_email}}');
                $a2 = array($request->first_name,$verification_code,$url,config('app.name'),config('custom.support_phone'),config('custom.support_email'));

                $response = str_replace($a1,$a2,$body); // this will replace {{username}} with $data['username']
                
                $mail = [
                    'body' => html_entity_decode(htmlspecialchars_decode($response)),
                    'subject' => $template->subject,
                ];

                $mailObject = new SendInvitation($mail); // you can make php artisan make:mail MyMail
                Mail::to($request->email)->send($mailObject);
            }
            $response_array = [
                "code" => "200",
                "message" => "Verification Mail has sent to your Registered Email-id",
            ];
    
            return response()->json(self::convertNullsAsEmpty($response_array), 200);

        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function newregister(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'required|string|max:191',
                'email' => 'required|email|unique:users',
                'country_id' => 'required|integer',
                'mobile_number' => 'required|min:7|max:15|unique:users',
                'password' => 'required|confirmed|min:6|string',
                'type' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return self::send_bad_request_response($validator->errors()->first());
            }
            DB::beginTransaction();
            $array=$request->toArray();
            $array['password'] = Hash::make($request->password);
            
            $array['blood_group'] = $request->bloodgroup;
            $array['gender'] = $request->gender;
            $array['dob'] = $request->dob;

            $array['created_by'] = 1; //test
            $array['country_id'] = $request->country_id;
            $array['currency_code'] = Country::getCurrentCode($request->country_id);

            $verification_code = mt_rand(100000,999999);
            $array['verification_code'] = $verification_code;
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $token = substr(str_shuffle(str_repeat($pool, 5)), 0, 20);
            $array['remember_token'] = $token;
            if($request->timezone){
				$array['time_zone'] = $request->timezone;
			}
            $user = User::create($array);
            $user->assignRole($request->type);

            /*add a row in address table*/
            $contact_details = new Address();
            $contact_details->user_id = $user->id;
            $contact_details->country_id = $request->country_id;
            $contact_details->created_by = $user->id;
            $contact_details->save();

            DB::commit();
            $url = '';
            //$url = config('custom.frontend_url').'verifymail/'.$user->id.'/'.$token;

            $template = EmailTemplate::where('slug','registration')->first();
            if($template){
                $body = ($template->content); // this is template dynamic body. You may get other parameters too from database. $title = $template->title; $from = $template->from;
            
                $a1 = array('{{username}}','{{verification_code}}','{{link}}','{{config_app_name}}','{{custom_support_phone}}','{{custom_support_email}}');
                $a2 = array($request->first_name,$verification_code,$url,config('app.name'),config('custom.support_phone'),config('custom.support_email'));

                $response = str_replace($a1,$a2,$body); // this will replace {{username}} with $data['username']
                
                $mail = [
                    'body' => html_entity_decode(htmlspecialchars_decode($response)),
                    'subject' => $template->subject,
                ];

                $mailObject = new SendInvitation($mail); // you can make php artisan make:mail MyMail
                Mail::to($request->email)->send($mailObject);
            }
            $response_array = [
                "code" => "200",
                "message" => "Verification Mail has sent to your Registered Email-id",
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

                    $url =  config('custom.frontend_url').'verifymail/'.$user->id.'/'.$token;

                    $template = EmailTemplate::where('slug','registration')->first();
                    if($template){
                        $body = ($template->content); // this is template dynamic body. You may get other parameters too from database. $title = $template->title; $from = $template->from;
                    
                        $a1 = array('{{username}}','{{verification_code}}','{{link}}','{{config_app_name}}','{{custom_support_phone}}','{{custom_support_email}}');
                        $a2 = array($user->first_name,$verification_code,$url,config('app.name'),config('custom.support_phone'),config('custom.support_email'));

                        $response = str_replace($a1,$a2,$body); // this will replace {{username}} with $data['username']
                        
                        $mail = [
                            'body' => html_entity_decode(htmlspecialchars_decode($response)),
                            'subject' => $template->subject,
                        ];

                        $mailObject = new SendInvitation($mail); // you can make php artisan make:mail MyMail
                        Mail::to($request->email)->send($mailObject);
                    }
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
                    if($request->route()->getName() == "verification"){
                        $user = $user->toArray();
                    }
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
                    $message = "Please enter a password which is not similar than current password.";
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
    
    public function getList(Request $request,$case,$id=NULL){
        try {
            if ($case) {
                switch ($case) {
                    case '1' : 
                        $country = Country::select('id','name','phone_code','currency','emoji','emojiU')->get();
                        if($request->route()->getName() == "getGeneralList"){
                            $response = $country->toArray();
                        }else{
                            $response = $country;
                        }
                        break;
                    case '2' : 
                        $state = State::select('id','name');
                        if(isset($id)){
                            $state = $state->where('country_id',$id);
                        }
                        $response = $state->get(); 
                        if($request->route()->getName() == "getGeneralList"){
                           $response = $response->toArray();
                        }
                        break;
                    case '3' : 
                        $city = City::select('id','name');
                        if(isset($id)){
                            $city = $city->where('state_id',$id);
                        }
                        $response = $city->get(); 
                        if($request->route()->getName() == "getGeneralList"){
                            $response = $response->toArray();
                        }
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
                $profile->biography = ($request->biography)? $request->biography : NULL;
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
                    $file_name = date('YmdHis') . rand(100, 999) . '_' . $user->id . '.' . $extension;
                    $path = 'images/profile-images/' . $file_name;
                    Storage::put($path, $imageData);

                    $user->profile_image = $file_name;
                    $user->save();
                } else {
                    return self::send_bad_request_response('Image Uploading Failed. Please check dimensions and try again!');
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

    public function destroy($id,$type=NULL){
        if(isset($type) && $type == 1){
            //echo $type;
            $user = User::withTrashed()->find($id);
            $user->forcedelete();
			$msg='Record Deleted successfully!';
            return self::send_success_response([],$msg);
        }else{
           return self::customDelete('\App\User', $id);
        }
    }

    public function userSoftdelete(Request $request){
        $rules = array(
            'user_id' => 'required|integer|exists:users,id', 
        );
        
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        $data = User::withTrashed()->find($request->user_id);
        if ($data->trashed()) {
            $data->deleted_at = null;
            $data->deleted_by = null;
            $data->save();
            $msg='Record Activated successfully!';
            // session()->flash('success', 'Record Activated successfully!');
        } else {
            $data->deleted_at = date('Y-m-d H:i:s');
            $data->deleted_by = auth()->user()->id;
            $data->save();
            $msg='Record Deleted successfully!';
            // session()->flash('success', 'Record Deleted successfully!');
        }
        return self::send_success_response([], $msg);
    }

    public function adminDashboard(Request $request){
        try {
            $user_id = auth()->user()->id;
            if($user_id){
                $doctor = User::role('doctor')->count();
                $patient = User::role('patient')->count();
                $appointment = Appointment::count();
                
                $revenue = Payment::join('appointments','payments.appointment_id','=','appointments.id')->whereYear('payments.created_at', date('Y'));

                $revenue_cost = $revenue->select(DB::raw('sum(CASE WHEN appointments.request_type=1 THEN (transaction_charge + tax_amount) ELSE transaction_charge END) as data'),DB::raw('YEAR(payments.created_at) year'))->get();

                // $revenue_cost = $revenue->select(DB::raw('sum(transaction_charge + tax_amount) as data'),DB::raw('YEAR(created_at) year'))->get();
                $revenue_graph = $revenue->select(DB::raw('sum(CASE WHEN appointments.request_type=1 THEN (transaction_charge + tax_amount) ELSE transaction_charge END) as `data`'),DB::raw('YEAR(payments.created_at) year, MONTH(payments.created_at) month'))->groupby('year','month')->get()->toArray();

                $patient_graph = User::role('patient')->select(DB::raw('count(id) as data'),DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
                ->whereYear('created_at', date('Y'))->groupby('year','month')->get()->makeHidden(['pid','did','age','accountstatus','membersince','gendername','doctorfees','userimage','providerspeciality','permanentaddress','officeaddress']);

                $doctor_graph = User::role('doctor')->select(DB::raw('count(id) as data'),DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
                ->whereYear('created_at', date('Y'))->groupby('year','month')->get()->makeHidden(['pid','did','age','accountstatus','membersince','gendername','doctorfees','userimage','providerspeciality','permanentaddress','officeaddress']);

                $patient_result = (new PatientController)->patientList($request);
                $doctor_result = (new DoctorController)->doctorList($request);
                $app_result = (new AppointmentController)->list($request,1);
               
                $revenue_data = array_fill(0,12,0);
                $patient_data = array_fill(0,12,0);
                $doctor_data = array_fill(0,12,0);

                foreach($revenue_graph as $rev){
                    $revenue_data[$rev['month'] - 1] = $rev['data'];
                }
                foreach($patient_graph as $pat){
                    $patient_data[$pat['month'] - 1] = $pat['data'];
                }
                foreach($doctor_graph as $doc){
                    $doctor_data[$pat['month'] - 1] = $doc['data'];
                }

                $result = [ 
                    'doctor' => $doctor, 
                    'patient' => $patient,
                    'appointment' => $appointment, 
                    'patient_list'=>$patient_result,
                    'doctor_list'=>$doctor_result,
                    'app_list'=>$app_result,
                    'revenue' => $revenue_cost,
                    'revenue_graph' => $revenue_data,
                    'patient_graph' => $patient_data, 
                    'doctor_graph' => $doctor_data, 
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

    public function getCommonData(Request $request){
        
            $common = [];
            $lang_id = ($request->language_id)? getLang($request->language_id) : defaultLang();
            $common['header'] = getLangContent(8,$lang_id);
            $common['setting'] = getSettingData();
            if (auth()->check()) {
                $common['menu'] = getAppMenu();
            }else{
                $common['page_content'] = PageContent::where('slug','login')->first();
            }
            $common['lang_content'] = getLangContent($request->page_master_id,$lang_id);
            $common['footer'] = getLangContent(9,$lang_id);
        try {
            $rules = array(
                'page_master_id' => 'required|integer|exists:page_masters,id', 
            );
            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
            }
            $valid = self::customValidation($request, $rules,$common);
            if($valid){ return $valid;}
            
            return self::send_success_response([], 'Content fetched successfully',$common);
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage(),$common);
        }
    }

    public function testing(Request $request){

      return  $this->notification($request->token, $request->title);

    }

    public function notification($token, $title)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $token=$token;

        $notification = [
            'title' => $title,
            'sound' => true,
        ];
        
        $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
            'to'        => $token, //single token
            'notification' => $notification,
            'data' => $extraNotificationData
        ];

        $headers = [
            'Authorization: key=AIzaSyDadhQvQJsezb0Jj8LkaA6NPHvZ6b3guuY',
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);

        return true;
    }
}