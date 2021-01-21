<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { User,Address,Appointment,Prescription,PrescriptionDetails };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class PatientController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    

    public function profile_details($id){
        $data['profile'] = User::find($id);
        $data['address'] = Address::with('country','state','city')->where('user_id',$id)->first();
        $list = Appointment::whereUserId($id)->orderBy('created_at', 'DESC');
        /*$array = collect();
        $list->getCollection()->each(function ($appointment) use (&$array) {
            $array->push($appointment->getData());
        });*/
        $data['appointments'] = $list->get();

        $prescription = Prescription::with('prescriptionDetails')->whereUserId($id)->orderBy('created_at', 'DESC')->get();
        
        $data['prescription'] = $prescription;

        return self::send_success_response($data);
    }

    public function patientList(Request $request){

        $paginate = $request->count_per_page ? $request->count_per_page : 10;

        $order_by = $request->order_by ? $request->order_by : 'desc';

        $list = User::role('patient')->orderBy('created_at', $order_by)->get();
        $list->append('pid','age','accountstatus','gendername');
        //$list->paginate($paginate)
        return self::send_success_response($list);
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

    

}
