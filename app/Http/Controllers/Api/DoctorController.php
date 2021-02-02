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
            'amount' => 'numeric',
            'contact_address_line1' => 'required',   
            'speciality_id' => 'nullable|numeric|exists:specialities,id',   
        );

        if($request->clinic_name){
            $rules['clinic_address_line1'] = 'required';
        }
       
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}
        
        DB::beginTransaction();

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
            if($request->clinic_name){
                $get_clinic_details = Address::whereUserId($user_id)->whereNotNull('name')->first();
                
                if(isset($get_clinic_details)){
                    $clinic_details = $get_clinic_details;
                    $clinic_details->updated_by = auth()->user()->id;
                }else{
                    $clinic_details = new Address();
                    $clinic_details->user_id = $user_id;
                    $clinic_details->created_by = auth()->user()->id;    
                }
                
                $clinic_details->name = $request->clinic_name;
                $clinic_details->line_1 = $request->clinic_address_line1;
                $clinic_details->line_2 = ($request->clinic_address_line2)? $request->clinic_address_line2 : NULL;
                $clinic_details->country_id = ($request->clinic_country_id)? $request->clinic_country_id : NULL;
                $clinic_details->state_id = ($request->clinic_state_id)? $request->clinic_state_id : NULL;
                $clinic_details->city_id = ($request->clinic_city_id)? $request->clinic_city_id : NULL;
                $clinic_details->postal_code = ($request->clinic_postal_code)? $request->clinic_postal_code : NULL;
                $clinic_details->save();
            }
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
            if($request->speciality_id){
                $doctor->doctorSpecialization()->detach();
                $doctor->doctorSpecialization()->attach($request->speciality_id);
            }
            
            // save doctor Services 
            Service::where('user_id', '=', $user_id)->forcedelete();

            if(isset($request->services)){
                $services = explode(",", $request->services);
                if(count($services) > 0) {
                    foreach($services as $val){
                        Service::create(['user_id'=>$user_id,'name'=> $val,'created_by'=>auth()->user()->id]);
                    }
                }
            }

            EducationDetail::where('user_id', '=', $user_id)->forcedelete();
            if($request->education) {
                $education_result = json_decode($request->education, true);
                foreach($education_result as $degree){
                    $year = (int)$degree['completion'];
                    if($year<1000 || $year>2100){
                        DB::rollback();
                        return self::send_bad_request_response('Invalid Year of Completion . Please check and try again.');
                    }
                    $education = new EducationDetail();
                    if(!empty($degree['degree']) || !empty($degree['college']) || !empty($degree['completion'])){
                        $education->degeree = $degree['degree'];
                        $education->institute = $degree['college'];
                        $education->year_of_completion = $degree['completion'];
                        $education->user_id = $user_id;
                        $education->created_by = auth()->user()->id;
                        $education->save();
                    }
                }
            }

            // save doctor Experience details
            ExperienceDetail::where('user_id', '=', $user_id)->forcedelete();
            if($request->experience) {
                $experience_result = json_decode($request->experience, true);
                foreach($experience_result as $hospital){
                    $from_year = (int)$hospital['from'];
                    $to_year = (int)$hospital['to'];

                    if($from_year<1000 || $from_year>2100 || $to_year<1000 || $to_year>2100 || $from_year > $to_year){
                        DB::rollback();
                        return self::send_bad_request_response('Incorrect From or To year. Please check and try again.');
                    }                 
                   
                    $experience = new ExperienceDetail();
                    if(!empty($hospital['hospital_name']) || !empty($hospital['from']) || !empty($hospital['to']) || !empty($hospital['designation'])){
                        $experience->hospital_name = $hospital['hospital_name'];
                        $experience->from = $hospital['from'];
                        $experience->to = $hospital['to'];
                        $experience->designation = $hospital['designation'];
                        $experience->user_id = $user_id;
                        $experience->created_by = auth()->user()->id;
                        $experience->save();
                    }
                }
            }

            //save doctor awards details
            $awardArray = $request->achievement;
            AwardDetail::where('user_id', '=', $user_id)->forcedelete();
            if(isset($awardArray)) {
                $achievement_result = json_decode($request->achievement, true);
                foreach($achievement_result as $award){
                    $awardyear = (int)$award['award_year'];
                    if($awardyear<1000 || $awardyear>2100){
                        DB::rollback();
                        return self::send_bad_request_response('Invalid Awarded Year . Please check and try again.');
                    }
                    $achievement = new AwardDetail();
                    if(!empty($award['name']) || !empty($award['award_year'])){
                        $achievement->name = $award['name'];
                        $achievement->award_year = $award['award_year'];
                        $achievement->user_id = $user_id;
                        $achievement->created_by = auth()->user()->id;
                        $achievement->save();
                    }
                }
            }

            // save doctor registration details
            $registrationArray = $request->registration;
            RegistrationDetail::where('user_id', '=', $user_id)->forcedelete();
            if(isset($registrationArray)) {
                $registration_result = json_decode($request->registration, true);
                foreach($registration_result as $reg){
                    $regyear = (int)$reg['registration_year'];
                    if($regyear<1000 || $regyear>2100){
                        DB::rollback();
                        return self::send_bad_request_response('Invalid Registration Year . Please check and try again.');
                    }
                    $registration = new RegistrationDetail();
                    if(!empty($reg['name']) || !empty($reg['registration_year'])){
                        $registration->name = $reg['name'];
                        $registration->registration_year = $reg['registration_year'];
                        $registration->user_id = $user_id;
                        $registration->created_by = auth()->user()->id;
                        $registration->save();
                    }
                }
            }

            // save doctor MembershipDetail details
            $membershipArray = $request->membership;
            MembershipDetail::where('user_id', '=', $user_id)->forcedelete();
            if(isset($membershipArray)) {
                $membership_result = json_decode($request->membership, true);
                foreach($membership_result as $value){
                    $membership = new MembershipDetail();
                    $membership->name = $value['name'];
                    $membership->user_id = $user_id;
                    $membership->created_by = auth()->user()->id;
                    $membership->save();
                }
            }    
            
                DB::commit();

                return self::send_success_response([],'Doctor Records Store Successfully');
            }else{
                return self::send_bad_request_response('Incorrect User id. Please check and try again!');
            }
       
        } catch (\Exception | \Throwable $exception) {
            DB::rollback();
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

            if(count($data)>0){
                return self::send_success_response($data,'Doctors data fetched successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (\Exception | \Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    
}
