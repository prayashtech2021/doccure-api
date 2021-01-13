<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Validator;
use App\ { User, Speciality, EducationDetail, Country, State, City };
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
        $user_id = $request->user()->id;
        $rules = [
            'user_id' => 'required|integer',
            'first_name'  => 'required|string|max:191',
            'last_name'  => 'string|max:191',
            'email' => 'required|email|unique:users',
            'mobile_number' => 'required|min:10|max:10|unique:users',
            'gender'  => 'required|integer|max:10',
            'dob'  => 'date',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'code' => 401, 'error' => $validator->errors()->first(), 'error_details' => $validator->errors()]);
        }
        
        //Save doctor profile
        $doctor = User::find($user_id);
        $doctor->fill($request->all());
        $doctor->dob = date('Y-m-d',strtotime(str_replace('/', '-', $request->dob)));
        $doctor->save();
//        $doctor->specialities()->detach();
  //      $doctor->specialities()->attach($request->specialist);

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
        $awardArray = $request->input('achievement');
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
        $registrationArray = $request->input('registration');
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
        $membershipArray = $request->input('m_ship');
        if(count($membershipArray) > 0) {
            foreach($membershipArray as $value){
                $membership = new MembershipDetail();
                $membership->m_ship = $value;
                $membership->user_id = $user_id;
                $membership->save();
            }
        }

        user()->services()->delete();

        $services = explode(",", $request->services);
        if(count($services) > 0) {
            foreach($services as $val){
                user()->services()->create(['service'=> $val]);
            }
        }
    }

    public function doctorList(Request $request){
        try{
            $doctors = User::role('doctor')->with('specialities');
            if($request->gender){
                $doctors = $doctors->where('gender',$request->gender);
            }
            if($request->speciality){
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
            }
            $doctors->get();
            if($doctors){
                return self::send_success_response($doctors,'No Data Found with these request');
            }else{
                return self::send_success_response($doctors,'Doctors data fetched successfully');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    
}
