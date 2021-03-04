<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { User,Address,Appointment,Prescription,PrescriptionDetails,Country,UserFavourite,PageContent };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\MedicalRecordController;
use \Exception;
use \Throwable;



class PatientController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    
    public function profile_details($id){
        try {
            $user = User::find($id);
            $user->profile_image=getUserProfileImage($user->id);
            unset($user->roles);
            removeMetaColumn($user);
        
            if($user){
                return self::send_success_response($user,'Patient Profile Detail Fetched Successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }  
    }

    public function patientList(Request $request){
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            updateLastSeen(auth()->user());

            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            if(auth()->user()->hasrole('doctor')){ //doctors -> my patients who attended appointments
                $doctor_id = auth()->user()->id;
                $list = User::role('patient')->orderBy('created_at', $order_by);

                $list = $list->whereHas('appointments', function ($qry) use ($doctor_id) {
                    $qry->where('appointments.doctor_id',$doctor_id);
                });
            
            }else{ //for Admin -> patient list
                $list = User::role('patient')->orderBy('created_at', $order_by);
                if(auth()->user()->hasrole('company_admin')){
                    $list = $list->withTrashed();
                }
            }
            $list = $list->groupBy('users.id');
            $data = collect();
            $id = 0;
            if ($request->bearerToken()) {
                $id = auth('api')->user()->id;
            }
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($provider) use (&$data,$id) {
                $data->push($provider->patientProfile($id));
            });

            if($data){
                return self::send_success_response($data,'Patient List fetched successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function profile_update(Request $request){
      
        try{    
            $user_id = $request->user_id;
            $rules = [
                'user_id' => 'required|numeric|exists:users,id',
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'string|max:191',
                'email' => 'required|email|unique:users,email,'.$request->user_id,
                'country_code_id' => 'required|numeric|exists:countries,id',
                'mobile_number' => 'required|min:10|max:10|unique:users,mobile_number,'.$request->user_id,
                'gender'  => 'required|integer|between:1,2',
                'dob'  => 'date',
                'contact_address_line1' => 'required',
            ];
    
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return self::send_bad_request_response($validator->errors()->first());
            }
            //Save patient profile
            $patient = User::find($user_id);
            if($patient){
            $patient->fill($request->all());
            $patient->country_id = $request->country_code_id;
            $patient->currency_code = Country::getCurrentCode($request->country_code_id);
            $patient->dob = date('Y-m-d',strtotime(str_replace('/', '-', $request->dob)));
            $patient->save();
            
            /* patient Address Details */
            $get_contact_details = Address::whereUserId($user_id)->first();
            if($get_contact_details){
                $contact_details = $get_contact_details;
                $contact_details->updated_by = auth()->user()->id;
            }else{
                $contact_details = new Address();
                $contact_details->user_id = $user_id;
                $contact_details->created_by = auth()->user()->id;
            }
            
            $contact_details->line_1 = $request->contact_address_line1;
            $contact_details->line_2 = ($request->contact_address_line2)? $request->contact_address_line2 : NULL;
            $contact_details->country_id = ($request->contact_country_id)? $request->contact_country_id : NULL;
            $contact_details->state_id = ($request->contact_state_id)? $request->contact_state_id : NULL;
            $contact_details->city_id = ($request->contact_city_id)? $request->contact_city_id : NULL;
            $contact_details->postal_code = ($request->contact_postal_code) ? $request->contact_postal_code : NULL;
            $contact_details->save();
            
                return self::send_success_response([],'Patient Profile Updated Successfully');
            }else{
                return self::send_unauthorised_request_response('Incorrect User Id, Kindly check and try again.');
            }
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function patientSearchList(Request $request){
        $rules = array(
            'gender' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'country_id' => 'nullable|numeric|exists:countries,id',
            'state_id' => 'nullable|numeric|exists:states,id',
            'city_id' => 'nullable|numeric|exists:cities,id',
           // 'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'sort' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try{
           // $paginate = $request->count_per_page ? $request->count_per_page : 10;

            $data = User::role('patient');

            if($request->gender){
                $data->whereIn('gender',[$request->gender]);
            }
            if($request->blood_group){
                $data = $data->where('blood_group', $request->blood_group);
            }
            if($request->country_id){
                $country_id = $request->country_id;
                $data = $data->whereHas('homeAddress', function ($category) use ($country_id) {
                    $category->where('addresses.country_id',$country_id);
                });
            }
            
            if($request->state_id){
                $state_id = $request->state_id;
                $data = $data->whereHas('homeAddress', function ($category) use ($state_id) {
                    $category->where('addresses.state_id',$state_id);
                });
            }
            if($request->city_id){
                $city_id = $request->city_id;
                $data = $data->whereHas('homeAddress', function ($category) use ($city_id) {
                    $category->where('addresses.city_id',$city_id);
                });
            }
            
            if($request->sort == 1){ //latest
                $data = $data->orderBy('created_at', 'DESC');
            }else{
                $order_by = $request->order_by ? $request->order_by : 'desc';
                $data = $data->orderBy('created_at', $order_by);
            }

            $list = collect();
            $data->each(function ($provider) use (&$list) {
                $list->push($provider->patientProfile());
            });
            
            if(count($list)>0){
                return self::send_success_response($list,'Patient data fetched successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function patientDashboard(Request $request){
        $array = [];
            $lang_id = ($request->language_id)? $request->language_id : defaultLang();
            $array['header'] = getLangContent(8,$lang_id);
            $array['setting'] = getSettingData();
            $array['menu'] = getAppMenu();
            $array['lang_content'] = getLangContent(10,$lang_id);
            $array['footer'] = getLangContent(9,$lang_id);

        $rules = array(
            'consumer_id' => 'required|numeric|exists:users,id',
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}
            
        try {
            $user_id = auth()->user()->id;
            $user = auth()->user();
            updateLastSeen($user);
            if($user_id){
                
                $appointment_result = (new AppointmentController)->list($request,1);
                $prescription_result = (new AppointmentController)->prescriptionList($request,1);
                $medical_record_result = (new MedicalRecordController)->getList($request,1);

                $result = [ 
                    'appointment_list'=> $appointment_result,
                    'prescription_list' => $prescription_result,
                    'medical_record_list'=> $medical_record_result,
                ];
                
                return self::send_success_response($result,$common);
            }else{
                $message = "Unauthorised request.";
                return self::send_unauthorised_request_response($message);
            }
        } catch (\Exception | \Throwable $exception) {
           return self::send_exception_response($exception->getMessage(),$array);
        }
    }

    public function favouriteSave(Request $request){
        try{    
            $user_id = auth()->user()->id;
            $favourite_id = $request->favourite_id;
        
            $rules = [
                'favourite_id' => 'required|integer|exists:users,id',
            ];

            $valid = self::customValidation($request, $rules);
            if($valid){ return $valid;}

            DB::beginTransaction();

            $user = User::find($user_id);
            if($user){   
                if($request->set==0){ //unset user favourite
                    $user->userFav()->detach($favourite_id);
                }else{
                    $user->userFav()->sync($favourite_id,false);
                } 
                DB::commit();
                return self::send_success_response([],'Favourite Updated Successfully');
            }else{
                $message = "Unauthorised request.";
                return self::send_unauthorised_request_response($message);
            }
           
        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getFavouriteList(){
        try{    
            $user = auth()->user();
            if($user->hasrole('patient')){
                $list =  auth()->user()->userFav;
                
                $data = collect();
                $list->each(function ($provider) use (&$data) {
                    $data->push($provider->basicProfile());
                });

                return self::send_success_response($data,'Patient Favourite List');
            }else{
                $message = "Unauthorised request.";
                return self::send_unauthorised_request_response($message);
            }
        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

}
