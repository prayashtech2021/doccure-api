<?php

namespace App\Http\Controllers;

use Validator;
use App\ { User, Patient };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Hash;

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
                'name'  => 'required|string|max:191',
                'email' => 'required|email|unique:users',
                'mobile_number' => 'required|min:10|max:10|unique:users',
                'password' => 'required|confirmed|min:6|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
            }
            DB::beginTransaction();
            $array=$request->toArray();
            $array['first_name'] = $request->name; //test
            $array['email'] = $request->email;
            $array['password'] = Hash::make($request->password);
            $array['created_by'] = 1; //test
            
            $user = User::create($array);

            if($user){
                if($request->type==1){  //patient
                    $role = 'patient';
                    Patient::create([
                        'user_id' => $user->id,
                        'first_name' => $request->name,
                        'created_by' => 1, //test
                    ]);
                   
                }else{  //Doctor
                    $role = 'doctor';
                    Doctor::create([
                        'user_id' => $user->id,
                        'first_name' => $request->name,
                        'created_by' => 1, //test
                    ]);
                }
                $user->assignRole($role);

                DB::commit();
                return response()->json(['success' => true, 'code' => 200, 'message'=>'Registered Successfully']);
            }else{
                return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.']);
            }

        } catch (\Exception | \Throwable $exception) {
            DB::rollback();
			return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.', 'error' => $exception->getMessage()]);
		}
    }

    public function getList(Request $request)
    {
    	$case = $request->case;

    	if ($case) {
    		switch ($case) {
                case 'get_country' : 
                    $response = Country::get()->toArray();
                    break;
                case 'get_states' : 
                    $response = State::get()->toArray(); 
                    break;
                case 'get_cities' : 
                    $response = City::get()->toArray(); 
                    break;
                default : 
                    $response = ['case' => $case, 'status' => 'Action not found']; 
                    break;
	    	}
	    } else {
            $response = ['status' => 'invalid request'];
        }

        return response()->json($response, 200);
    }

    public function get_country(){

    }

}
