<?php

namespace App\Http\Controllers;

use Validator;
use App\ { User, Patient, Doctor };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class DoctorController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function dashboard() {
        try {
            $user_id = auth()->user()->id;
            $doctor = Doctor::where('user_id', '=', $user_id)->first();
            if($doctor){
                $patient = Patient::get()->count();
                return response()->json(['success' => true, 'code' => 200, 'total_patient'=>$patient]);
            }else{
                return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.']);
            }
        } catch (\Exception | \Throwable $exception) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.', 'error' => $exception->getMessage()]);
        }
    }

    
}
