<?php

namespace App\Http\Controllers;

use Validator;
use App\ { User };
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
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'required|string|max:191',
                'email' => 'required|email|unique:users',
                'mobile_number' => 'required|min:10|max:10|unique:users',
                'password' => 'required|confirmed|min:6|string',
                'type' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
            }
            DB::beginTransaction();
            $array=$request->toArray();
            $array['password'] = Hash::make($request->password);
            $array['created_by'] = 1; //test
            
            $user = User::create($array);

            if($user){
                $user->assignRole($request->type);
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

    public function getList($case)
    {
    	if ($case) {
    		switch ($case) {
                case 'get_country' : 
                    $response = Country::pluck('id','name')->toArray();
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

}
