<?php

namespace App\Http\Controllers;

use Validator;
use App\ { User };
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

    public function list(){
        try {
            $list = User::select('id','first_name','last_name','address_line1','address_line2')
            ->with(['user' => function ($q) {
                $q->select('users.profile_image');
            }])->get();

            return response()->json(['success' => true, 'code' => 200, "data" => $list]);
        } catch (\Exception | \Throwable $exception) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.', 'error' => $exception->getMessage()]);
        }
    }

    public function profile_details($id){
        $patient = User::find($id);
        return response()->json(['success' => true, 'code' => 200, "data" => $patient]);
    }

    public function profile_update(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'patient_id' => 'required|integer',
                'first_name'  => 'required|string|max:191',
                'last_name'  => 'string|max:191',
                'sex'  => 'required|string|max:10',
                'dob'  => 'date',
                'email' => 'required|email|unique:users',
                'mobile_number' => 'required|min:10|max:10|unique:users',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
            }
            DB::beginTransaction();

            $patient  = Patient::findOrFail($request->patient_id);

            $patient->fill($request->all());

            $patient->dob = date('Y-m-d',strtotime(str_replace('/', '-', $request->dob)));

            $patient->save();

            DB::commit();
            if($update){
                return response()->json(['success' => true, 'code' => 200, 'message'=>'Updated Successfully']);
            }else{
                return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.']);
            }

        } catch (\Exception | \Throwable $exception) {
            DB::rollback();
			return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.', 'error' => $exception->getMessage()]);
		}
    }

    public function uploadProfileImage(Request $request, $user_id = null)
    {
        $rules = array(
            'profile_image' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
        }

        try {

            if ($user_id) {
                $user = User::find($user_id);
            } else {
                $user = User::find(auth()->user()->id);
            }

            if (!empty($request->profile_image)) {
                if (preg_match('/data:image\/(.+);base64,(.*)/', $request->profile_image, $matchings)) {
                    $imageData = base64_decode($matchings[2]);
                    $extension = $matchings[1];
                    $file_name = date('YmdHis') . rand(100,999). '_' . $user->id . '.' . $extension;
                    $path = 'profile-images/'.$file_name;
                    Storage::put($path , $imageData);
                    // file_put_contents($path, $imageData);

                    if(!empty($user->profile_image)){
                        if (Storage::exists('profile-images/' . $user->profile_image)) {
                            Storage::delete('profile-images/' . $user->profile_image);
                        }
                    }
                    $user->profile_image = $file_name;
                }
            }
            $user->updated_by = auth()->user()->id;
            $user->save();

            return response()->json(['success' => true, 'code' => 200, 'message' => 'Image updated successfully!']);

        } catch (Exception | Throwable $e) {

            return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);

        }
    }


}
