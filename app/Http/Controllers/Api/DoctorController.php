<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Validator;
use App\ { User, Speciality, EducationDetail, Service,Country, State, City, Address, AddressImage, UserSpeciality, ExperienceDetail, AwardDetail, MembershipDetail, RegistrationDetail };
use Illuminate\Http\Request;
use DB;
use Storage;

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
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';

            $list = User::role('doctor')->orderBy('created_at', $order_by);
            if(auth()->user()->hasrole('company_admin')){
                $list = $list->withTrashed();
            }
            $data = collect();
            $list->paginate($paginate)->getCollection()->each(function ($provider) use (&$data) {
                $data->push($provider->doctorProfile());
            });
            if($data){
                return self::send_success_response($data,'Doctor Details Fetched Successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function doctorProfile($user_id){
        try {
            
            $list = User::role('doctor')->with('doctorService','doctorEducation','doctorExperience','doctorAwards','doctorMembership','doctorRegistration')->find($user_id);
            if($list){
                
                $doctor['profile'] = $list;
                $doctor['feedback'] = [];
                $doctor['ratings'] = [];
                $doctor['book_appointment'] = '';
                $doctor['chat'] = '';
                $doctor['call'] = '';
                $doctor['video_call'] = '';
                $doctor['wishlist'] = '';
                return self::send_success_response($doctor,'Doctor Details Fetched Successfully.');
            }else{
                return self::send_bad_request_response('Incorrect User Id. Please check and try again.');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function saveProfile(Request $request){
        try{
         
        $user_id = $request->user_id;
        $rules = array(
            'user_id' => 'required|integer|exists:users,id',
            'first_name'  => 'required|string|max:191',
            'last_name'  => 'string|max:191',
            'country_code_id' => 'required|integer|exists:countries,id',
            'mobile_number' => 'required|min:10|max:10|unique:users,mobile_number,'.$request->user_id,
            'gender'  => 'required|integer|between:1,2',
            'dob'  => 'date',
            'price_type' => 'required|between:1,2',
            'amount' => 'decimal',
            'contact_address_line1' => 'required',   
        );

        if($request->clinic_name){
            $rules['clinic_address_line1'] = 'required';
        }

        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        //Save doctor profile
        $doctor = User::find($user_id);
        if($doctor){
        $doctor->fill($request->all());
        $doctor->country_id = $request->country_code_id;
        $doctor->currency_code = Country::getCurrentCode($request->country_code_id);
        $doctor->dob = date('Y-m-d',strtotime(str_replace('/', '-', $request->dob)));
        $doctor->save();
      
        /* Doctor Address Details */
        $get_contact_details = Address::whereUserId($user_id)->whereNull('name')->first();
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
        $contact_details->city_id = ($request->contact_city_id)? $request->contact_city_id : NULL ;
        $contact_details->postal_code = ($request->contact_postal_code)? $request->contact_postal_code : NULL;
        $contact_details->save();
       
        /* Doctor Clinic Info */
            $get_clinic_details = Address::whereUserId($user_id)->whereNotNull('name')->first();
        
        if(isset($get_clinic_details)){
            $clinic_details = $get_clinic_details;
            $clinic_details->updated_by = auth()->user()->id;
        }else{
            $clinic_details = new Address();
            $clinic_details->user_id = $user_id;
            $clinic_details->created_by = auth()->user()->id;    
        }
        
        $clinic_details->name = ($request->clinic_name)? $request->clinic_name : '';
        $clinic_details->line_1 = $request->clinic_address_line1;
        $clinic_details->line_2 = ($request->clinic_address_line2)? $request->clinic_address_line2 : NULL;
        $clinic_details->country_id = ($request->clinic_country_id)? $request->clinic_country_id : NULL;
        $clinic_details->state_id = ($request->clinic_state_id)? $request->clinic_state_id : NULL;
        $clinic_details->city_id = ($request->clinic_city_id)? $request->clinic_city_id : NULL;
        $clinic_details->postal_code = ($request->clinic_postal_code)? $request->clinic_postal_code : NULL;
        $clinic_details->save();
        
        /* Clinic Images */

        $images=array();

        if($files=$request->file('clinic_images')){
            $clinic_img = AddressImage::whereUserId($user_id)->where('address_id',$clinic_details->id)->delete();

            foreach($files as $file){
                $new_clinic_img = new AddressImage();
                $new_clinic_img->user_id = $user_id;
                $new_clinic_img->address_id	 = $clinic_details->id;
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

                $new_clinic_img->image = $file_name;
                $new_clinic_img->save();
                
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
            foreach($registrationArray['name'] as $key => $reg){
                $registration = new RegistrationDetail();
                if(!empty($reg) || !empty($registrationArray['registration_year'][$key])){
                    $registration->reg = $registrationArray['name'];
                    $registration->reg_year = $registrationArray['registration_year'][$key];
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
       
        } catch (\Exception | \Throwable $exception) {
          //  DB::rollback();
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

            $doctors = User::role('doctor');
            
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
                $doctors = $doctors->whereHas('homeAddress', function ($category) use ($country_id) {
                    $category->where('addresses.country_id',$country_id);
                });
            }
            
            if($request->state_id){
                $state_id = $request->state_id;
                $doctors = $doctors->whereHas('homeAddress', function ($category) use ($state_id) {
                    $category->where('addresses.state_id',$state_id);
                });
            }
            if($request->city_id){
                $city_id = $request->city_id;
                $doctors = $doctors->whereHas('homeAddress', function ($category) use ($city_id) {
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

            $data = collect();
            $doctors->paginate($paginate)->getCollection()->each(function ($provider) use (&$data) {
                $data->push($provider->doctorProfile());
            });

            if($data){
                return self::send_success_response($data,'Doctors data fetched successfully');
            }else{
                return self::send_bad_request_response($doctors,'No Records Found');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    
}
