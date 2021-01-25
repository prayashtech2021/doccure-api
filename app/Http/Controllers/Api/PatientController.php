<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { User,Address,Appointment,Prescription,PrescriptionDetails };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
        $user = User::find($id);
        $user->profile_image=getUserProfileImage($user->id);
        unset($user->roles);
        removeMetaColumn($user);
        $data['profile']=$user;
        $data['address'] = Address::with('country','state','city')->where('user_id',$id)->first();

        return self::send_success_response($data);
    }

    public function patientList(Request $request){

        $paginate = $request->count_per_page ? $request->count_per_page : 10;

        $order_by = $request->order_by ? $request->order_by : 'desc';

        if(auth()->user()->hasrole('doctor')){ //doctors -> my patients who attended appointments
            $doctor_id = auth()->user()->id;
            $list = User::with('homeAddresses')->orderBy('created_at', $order_by);
                
            $list = $list->whereHas('userAppointment', function ($category) use ($doctor_id) {
                $category->where('appointments.doctor_id',$doctor_id);
            });

        }else{ //for Admin -> patient list
            $list = User::role('patient')->withTrashed()->with('homeAddresses')->orderBy('created_at', $order_by);
        }
        $list = $list->get();

        //$list->paginate($paginate)
        if($list){
            return self::send_success_response($list,'Patient List fetched successfully');
        }else{
            return self::send_success_response($list,'No Records Found');
        }
    }

    public function profile_update(Request $request){
      
        try{    
            $user_id = $request->user_id;
            $rules = [
                'user_id' => 'required|integer',
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'string|max:191',
                'email' => 'required|email|unique:users,email,'.$request->user_id,
                'country_code_id' => 'required|integer',
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
            $contact_details->line_2 = ($request->contact_address_line2)? $request->contact_address_line2 : '';
            $contact_details->country_id = $request->contact_country_id;
            $contact_details->state_id = $request->contact_state_id;
            $contact_details->city_id = $request->contact_city_id;
            $contact_details->postal_code = $request->contact_postal_code;
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
            'keywords' => 'nullable|string',
            'gender' => 'nullable|string',
            'country_id' => 'nullable|numeric|exists:countries,id',
            'state_id' => 'nullable|numeric|exists:states,id',
            'city_id' => 'nullable|numeric|exists:cities,id',
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'sort' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try{
            $paginate = $request->count_per_page ? $request->count_per_page : 10;


            $data = User::role('patient')->with('homeAddresses');

            if($request->gender){
                $gender = explode(',',$request->gender);
                $data->whereIn('gender',$gender);
            }
            if($request->blood_group){
                $data = $data->where('blood_group', $request->blood_group);
            }
            if($request->country_id){
                $country_id = $request->country_id;
                $data = $data->whereHas('homeAddresses', function ($category) use ($country_id) {
                    $category->where('homeAddresses.country_id',$country_id);
                });
            }
            
            if($request->state_id){
                $state_id = $request->state_id;
                $data = $data->whereHas('homeAddresses', function ($category) use ($state_id) {
                    $category->where('homeAddresses.state_id',$state_id);
                });
            }
            if($request->city_id){
                $city_id = $request->city_id;
                $data = $data->whereHas('homeAddresses', function ($category) use ($city_id) {
                    $category->where('homeAddresses.city_id',$city_id);
                });
            }
            
            if($request->sort == 1){ //latest
                $data = $data->orderBy('created_at', 'DESC');
            }else{
                $order_by = $request->order_by ? $request->order_by : 'desc';
                $data = $data->orderBy('created_at', $order_by);
            }
 
            $data = $data->get();
            if($data){
                return self::send_success_response($data,'Patient data fetched successfully');
            }else{
                return self::send_success_response($data,'No Records Found');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

}