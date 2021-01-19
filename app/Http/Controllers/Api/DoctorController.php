<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Validator;
use App\ { User, Speciality, EducationDetail, Country, State, City, UserContactDetail, ClinicInformation, ClinicImage, UserSpeciality, ExperienceDetail, AwardDetail, MembershipDetail, RegistrationDetail };
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

    public function doctorProfile(Request $request){
        try {
            $user_id = $request->user()->id;
            
            $doctor['profile'] = User::find($user_id)->first();
            $doctor['speciality'] = user()->specialities->first();
            $doctor['specialization'] = Speciality::all();        
            $doctor['education'] = EducationDetail::where('user_id', '=', $user_id)->get();
            //$doctor['clinic'] = ClinicDetail::orderBy('id', 'DESC')->where('user_id', '=', $user_id)->get();

            $country = getList('get_country');

            $states =  getList('get_states');

            $cities =  getList('get_cities');

            $data = [ 'doctor' => $doctor, 'country' => $country, 'states' => $states, 'cities' => $cities ];
            return self::send_success_response($data);
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
            'contact_country_id' => 'required',
            'contact_state_id' => 'required',
            'contact_city_id' => 'required',
            'contact_postal_code' => 'required',
            
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'code' => 401, 'error' => $validator->errors()->first(), 'error_details' => $validator->errors()]);
        }
        
        //Save doctor profile
        $doctor = User::find($user_id);
        $doctor->fill($request->all());
        $doctor->country_id = $request->country_code_id;
        $doctor->currency_code = Country::getCurrentCode($request->country_code_id);
        $doctor->dob = date('Y-m-d',strtotime(str_replace('/', '-', $request->dob)));
        $doctor->save();
        
        /* Doctor Contact Details */
        $get_contact_details = UserContactDetail::whereUserId($user_id)->first();
        if($get_contact_details){
            $contact_details = $get_contact_details;
            $contact_details->updated_by = auth()->user()->id;
        }else{
            $contact_details = new UserContactDetail();
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
        $get_clinic_details = ClinicInformation::whereUserId($user_id)->first();
        if($get_clinic_details){
            $clinic_details = $get_clinic_details;
            $clinic_details->updated_by = auth()->user()->id;
        }else{
            $clinic_details = new ClinicInformation();
            $clinic_details->user_id = $user_id;
            $clinic_details->created_by = auth()->user()->id;
        }
        
        $clinic_details->clinic_name = ($request->clinic_name)? $request->clinic_name : '';
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
            $clinic_img = ClinicImage::whereUserId($user_id)->where('clinic_information_id',$clinic_details->id)->delete();

            foreach($files as $file){
                $new_clinic_img = new ClinicImage();
                $new_clinic_img->user_id = $user_id;
                $new_clinic_img->clinic_information_id = $clinic_details->id;
                $new_clinic_img->created_by = auth()->user()->id;

                if (Storage::exists('clinic_images/'.$clinic_details->id.'/')) {
                    Storage::delete('clinic_images/'.$clinic_details->id.'/');
                }

                if (preg_match('/data:image\/(.+);base64,(.*)/', $file, $matchings)) {
                    $imageData = base64_decode($matchings[2]);
                    $extension = $matchings[1];
                    $file_name = date('YmdHis') . rand(100,999). '_' . $clinic_details->id . '.' . $extension;
                    $path = 'clinic_images/'.$clinic_details->id.'/'.$file_name;
                    Storage::put($path , $imageData);
                    $new_clinic_img->clinic_image = $file_name;
                    $new_clinic_img->save();
                }
            }
        }

        /* Doctor Specialization */
        if($request->services && $request->speciality_id){
            $spl = UserSpeciality::whereUserId($user_id)->first();
            if($spl){
                $userSpeciality = $spl;
                $userSpeciality->updated_by = auth()->user()->id;
            }else{
                $userSpeciality = new UserSpeciality();
                $userSpeciality->created_by = auth()->user()->id;
            }
            $userSpeciality->user_id = $user_id;
            $userSpeciality->speciality_id = $request->speciality_id;
            $userSpeciality->service = $request->service;
            $userSpeciality->save();
        }

        /* Doctor Education */
        /*if($request->degree && $request->institute && $request->year_of_completion){
            $n = count($request->degree);
            EducationDetail::whereUserId($user_id)->delete();
            for ($i = 0; $i < $n; $i++){
                $education = new EducationDetail();
                $education->user_id = $user_id;
                $education->degree = $request->degree[$i];
                $education->institute = $request->institute[$i];
                $education->year_of_completion = $request->year_of_completion[$i];
                $education->save();
            }
        }*/

        /* Doctor Experience Details */
        /*if($request->hospital_name && $request->from && $request->to && $request->designation){
            $count = count($request->hospital_name);
            ExperienceDetail::whereUserId($user_id)->delete();
            for ($i = 0; $i < $n; $i++){
                $experience = new ExperienceDetail();
                $experience->user_id = $user_id;
                $experience->hospital_name = $request->hospital_name[$i];
                $experience->from = $request->from[$i];
                $experience->to = $request->to[$i];
                $experience->designation = $request->designation[$i];
                $experience->save();
            }
        }*/

        // save doctor Education details
        $doctor->doctorEducation()->delete();
        $educationArray = $request->education;
        if(isset($educationArray) && count($educationArray) > 0) {
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
        if(count($experienceArray) > 0) {
            foreach($experienceArray['h_name'] as $key => $hospital){
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
        if(count($awardArray) > 0) {
            foreach($awardArray['award'] as $key => $award){
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
        RegDetail::where('user_id', '=', $user_id)->delete();
        $registrationArray = $request->registration;
        if(count($registrationArray) > 0) {
            foreach($registrationArray['reg'] as $key => $reg){
                $registration = new RegDetail();
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
        if(count($membershipArray) > 0) {
            foreach($membershipArray as $value){
                $membership = new MembershipDetail();
                $membership->name = $value;
                $membership->user_id = $user_id;
                $membership->save();
            }
        }

        // save doctor RegistrationDetail details
        RegistrationDetail::where('user_id', '=', $user_id)->delete();
        $registrationArray = $request->registration;
        if(count($registrationArray) > 0) {
            foreach($registrationArray as $value){
                $registration = new RegistrationDetail();
                $registration->name = $value;
                $registration->user_id = $user_id;
                $registration->registration_year = 
                $membership->save();
            }
        }
        return self::send_success_response([],'Doctor Records Store Successfully');

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

    public function doctorList(Request $request){
        try{
            $doctors = User::get();//with('specialities');
            if($request->gender){
                $doctors->where('gender',$request->gender);
            }
           /* if($request->speciality){
                $sp = $request->speciality;
                $doctors = $doctors->whereHas('userSpeciality', function ($category) use ($sp) {
                    $category->whereIn('user_speciality.speciality_id',$sp)->where('user_speciality.deleted_at', null);
                });
            }
            if($request->country_id){
                $country_id = $request->country_id;
                $doctors = $doctors->whereHas('addresses', function ($category) use ($country_id) {
                    $category->where('addresses.country_id',$country_id)->where('addresses.deleted_at', null);
                });
            }
            if($request->state_id){
                $state_id = $request->state_id;
                $doctors = $doctors->whereHas('addresses', function ($category) use ($state_id) {
                    $category->where('addresses.state_id',$state_id)->where('addresses.deleted_at', null);
                });
            }
            if($request->city_id){
                $city_id = $request->city_id;
                $doctors = $doctors->whereHas('addresses', function ($category) use ($city_id) {
                    $category->where('addresses.city_id',$city_id)->where('addresses.deleted_at', null);
                });
            }*/
           // $doctors->get();
            if($doctors){
                return self::send_success_response($doctors,'');
            }else{
                return self::send_success_response($doctors,'Doctors data fetched successfully');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    
}
