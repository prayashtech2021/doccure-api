<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Validator;
use App\ { User, Speciality, EducationDetail, Service,Country, State, City, Address, AddressImage, UserSpeciality, ExperienceDetail, AwardDetail, MembershipDetail, RegistrationDetail };
use Illuminate\Http\Request;
use DB;

class DoctorController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function dashboard(Request $request) {
        try {
            $user_id = $request->user()->id;
            if($user_id){
                $patient = User::get()->count();
                $data = [ 'total_patient' => $patient ];
                return self::send_success_response($data);
            }else{
                $message = "Your account is not activated.";
                return self::send_unauthorised_request_response($message);
            }
        } catch (\Exception | \Throwable $exception) {
           return self::send_exception_response($exception->getMessage());
        }
    }

    public function doctorList(Request $request){

        $order_by = $request->order_by ? $request->order_by : 'desc';

        $data = User::role('doctor')->withTrashed()->with(['doctorSpecialization','addresses','homeAddresses'])->orderBy('created_at', $order_by)->get();
        $data->append('did','accountstatus','gendername');
        return self::send_success_response($data);
    }

    public function doctorProfile($user_id){
        try {
            
            $doctor['profile'] = User::with('doctorSpecialization','doctorService','doctorEducation','doctorExperience','doctorAwards','doctorMembership','doctorRegistration')->find($user_id);
            $doctor['doctor_contact_info'] = User::userAddress($user_id);
            $doctor['doctor_clinic_info'] = User::doctorClinicInfo($user_id);
            $doctor['doctor_clinic_images'] = User::doctorClinicImage($user_id);
            $doctor['feedback'] = [];
            $doctor['ratings'] = [];
            $doctor['book_appointment'] = '';
            $doctor['chat'] = '';
            $doctor['call'] = '';
            $doctor['video_call'] = '';
            $doctor['wishlist'] = '';
         
            return self::send_success_response($doctor);
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function saveProfile(Request $request){
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
            'price_type' => 'required|between:1,2',
            'amount' => 'numeric',

            'contact_address_line1' => 'required',
            
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }
        
        //Save doctor profile
        $doctor = User::find($user_id);
        if($doctor){
        $doctor->fill($request->all());
        $doctor->country_id = $request->country_code_id;
        $doctor->currency_code = Country::getCurrentCode($request->country_code_id);
        $doctor->dob = date('Y-m-d',strtotime(str_replace('/', '-', $request->dob)));
        $doctor->save();
      
        /* Doctor Address Details */
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
        
        /* Doctor Clinic Info */
        if($request->clinic_address_id){
            $get_clinic_details = Address::find($request->clinic_address_id)->first();
        }
        
        if($request->clinic_address_id && (isset($get_clinic_details))){
            $clinic_details = $get_clinic_details;
            $clinic_details->updated_by = auth()->user()->id;
        }else{
            $clinic_details = new Address();
            $clinic_details->user_id = $user_id;
            $clinic_details->created_by = auth()->user()->id;    
        }
        
        $clinic_details->name = ($request->clinic_name)? $request->clinic_name : '';
        $clinic_details->line_1 = ($request->clinic_address_line1)? $request->clinic_address_line1 : '';
        $clinic_details->line_2 = ($request->clinic_address_line2)? $request->clinic_address_line2 : '';
        $clinic_details->country_id = ($request->clinic_country_id)? $request->clinic_country_id : '';
        $clinic_details->state_id = ($request->clinic_state_id)? $request->clinic_state_id : '';
        $clinic_details->city_id = ($request->clinic_city_id)? $request->clinic_city_id : '';
        $clinic_details->postal_code = ($request->clinic_postal_code)? $request->clinic_postal_code : '';
        $clinic_details->save();

        /* Clinic Images */

        $images=array();

        if($files=$request->file('clinic_images')){
            $clinic_img = AddressImage::whereUserId($user_id)->where('clinic_information_id',$clinic_details->id)->delete();

            foreach($files as $file){
                $new_clinic_img = new AddressImage();
                $new_clinic_img->user_id = $user_id;
                $new_clinic_img->clinic_information_id = $clinic_details->id;
                $new_clinic_img->created_by = auth()->user()->id;

                if (Storage::exists('images/address_images/'.$clinic_details->id.'/')) {
                    Storage::delete('images/address_images/'.$clinic_details->id.'/');
                }
                if (!empty($file)) {
                    $extension = $file->getClientOriginalExtension();
                    $file_name = date('YmdHis') . '_' . auth()->user()->id . '.png';
                    $path = 'images/address_images'.$clinic_details->id.'/';
                    $store = $request->file('image')->storeAs($path, $file_name);
                }else{
                    $file_name = '';
                }

                $new_clinic_img->clinic_image = $file_name;
                $new_clinic_img->save();
                
                /*if (preg_match('/data:image\/(.+);base64,(.*)/', $file, $matchings)) {
                    $imageData = base64_decode($matchings[2]);
                    $extension = $matchings[1];
                    $file_name = date('YmdHis') . rand(100,999). '_' . $clinic_details->id . '.' . $extension;
                    $path = 'clinic_images/'.$clinic_details->id.'/'.$file_name;
                    Storage::put($path , $imageData);    
                }*/
            }
        }

        /* Doctor Specialization */
        $doctor->doctorSpecialization()->detach();
        $doctor->doctorSpecialization()->attach($request->speciality_id);
        
        // save doctor Services 
        if(isset($request->services)){
            Service::where('user_id', '=', $user_id)->delete();
            $services = explode(",", $request->services);
            if(count($services) > 0) {
                foreach($services as $val){
                    Service::create(['user_id'=>$user_id,'name'=> $val,'created_by'=>auth()->user()->id]);
                }
            }
        }
       
       /* $serviceArray = $request->services;
        if(isset($serviceArray) ) {
            foreach($serviceArray as $key => $service){
                $new_service = new Service();
                if(!empty($service)){
                    $new_service->name = $new_service;
                    $new_service->user_id = $user_id;
                    $new_service->save();
                }
            }
        }*/

        // save doctor Education details
        //$doctor->doctorEducation()->delete();
        EducationDetail::where('user_id', '=', $user_id)->delete();
        $educationArray = $request->education;
        if(isset($educationArray)) {
            foreach($educationArray['degree'] as $key => $degree){
                $education = new EducationDetail();
                if(!empty($degree) || !empty($educationArray['college'][$key]) || !empty($educationArray['completion'][$key])){
                    $education->degree = $degree;
                    $education->college = $educationArray['college'][$key];
                    $education->completion = $educationArray['completion'][$key];
                    $education->user_id = $user_id;
                    $education->save();
                }
            }
        }

        // save doctor Experience details
        ExperienceDetail::where('user_id', '=', $user_id)->delete();
        $experienceArray = $request->input('experience');
        if(isset($experienceArray)) {
            foreach($experienceArray['hospital_name'] as $key => $hospital){
                $experience = new ExperienceDetail();
                if(!empty($hospital) || !empty($experienceArray['from'][$key])
                        || !empty($experienceArray['to'][$key]) || !empty($experienceArray['designation'][$key])){
                    $experience->h_name = $hospital;
                    $experience->from = $experienceArray['from'][$key];
                    $experience->to = $experienceArray['to'][$key];
                    $experience->designation = $experienceArray['designation'][$key];
                    $experience->user_id = $user_id;
                    $experience->save();
                }
            }
        }

        //save doctor awards details
        AwardDetail::where('user_id', '=', $user_id)->delete();
        $awardArray = $request->achievement;
        if(isset($awardArray)) {
            foreach($awardArray['name'] as $key => $award){
                $achievement = new AwardDetail();
                if(!empty($award) || !empty($awardArray['award_year'][$key])){
                    $achievement->award = $award;
                    $achievement->year = $awardArray['award_year'][$key];
                    $achievement->user_id = $user_id;
                    $achievement->save();
                }
            }
        }

        // save doctor registration details
        RegistrationDetail::where('user_id', '=', $user_id)->delete();
        $registrationArray = $request->registration;
        if(isset($registrationArray)) {
            foreach($registrationArray['registration'] as $key => $reg){
                $registration = new RegistrationDetail();
                if(!empty($reg) || !empty($registrationArray['reg_year'][$key])){
                    $registration->reg = $reg;
                    $registration->reg_year = $registrationArray['reg_year'][$key];
                    $registration->user_id = $user_id;
                    $registration->save();
                }
            }
        }

        // save doctor MembershipDetail details
        MembershipDetail::where('user_id', '=', $user_id)->delete();
        $membershipArray = $request->membership;
        if(isset($membershipArray)) {
            foreach($membershipArray as $value){
                $membership = new MembershipDetail();
                $membership->name = $value;
                $membership->user_id = $user_id;
                $membership->save();
            }
        }

            return self::send_success_response([],'Doctor Records Store Successfully');
        }else{
            return self::send_bad_request_response('Incorrect User id. Please check and try again!');
        }
        /*user()->services()->delete();

        $services = explode(",", $request->services);
        if(count($services) > 0) {
            foreach($services as $val){
                user()->services()->create(['service'=> $val]);
            }
        }*/
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }

    }

    public function doctorSearchList(Request $request){
        $rules = array(
            'keywords' => 'nullable|string',
            'gender' => 'nullable|string',
            'speciality' => 'nullable|string',
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

            $doctors = User::role('doctor')->with(['doctorSpecialization','addresses','homeAddresses']);
            
            if($request->keywords){
                $doctors = $doctors->where('first_name', 'like', '%' . $request->keywords . '%')
                ->orWhere('last_name', 'like', '%' . $request->keywords . '%');
            }

            if($request->gender){
                $gender = explode(',',$request->gender);
                $doctors->whereIn('gender',$gender);
            }

            if($request->speciality){
                $speciality = explode(',',$request->speciality);
                $doctors = $doctors->whereHas('doctorSpecialization', function ($category) use ($speciality) {
                    $category->whereIn('user_speciality.speciality_id',$speciality);
                });
            }

            if($request->country_id){
                $country_id = $request->country_id;
                $doctors = $doctors->whereHas('addresses', function ($category) use ($country_id) {
                    $category->where('addresses.country_id',$country_id);
                });
            }
            
            if($request->state_id){
                $state_id = $request->state_id;
                $doctors = $doctors->whereHas('addresses', function ($category) use ($state_id) {
                    $category->where('addresses.state_id',$state_id);
                });
            }
            if($request->city_id){
                $city_id = $request->city_id;
                $doctors = $doctors->whereHas('addresses', function ($category) use ($city_id) {
                    $category->where('addresses.city_id',$city_id);
                });
            }
            
            if($request->sort == 2){ //latest
                $doctors = $doctors->orderBy('created_at', 'DESC');
            }else{
                $order_by = $request->order_by ? $request->order_by : 'desc';
                $doctors = $doctors->orderBy('created_at', $order_by);
            }

            if($request->sort == 3){ //free
                $doctors = $doctors->where('price_type',1);
            }
            $doctors = $doctors->get();

            if($doctors){
                return self::send_success_response($doctors,'Doctors data fetched successfully');
            }else{
                return self::send_success_response($doctors,'No Records Found');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    
}
