<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { Speciality };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class SpecialityController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request) {
        if ($request->speciality_id) { //edit
            $rules = array(
                'speciality_id' => 'integer',
                'name' => 'required|unique:specialities,id,'.$request->speciality_id,
                'image' => 'nullable|string',
            );
        }else{
            $rules = array(
                'name' => 'required|unique:specialities',
                'image' => 'nullable|string',
            );
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }

        try {
            DB::beginTransaction();
            if ($request->speciality_id) {
                $speciality = Speciality::find($request->speciality_id);
                if(!$speciality){
                    return self::send_bad_request_response('Incorrect speciality id. Please check and try again!');
                }
                $speciality->updated_by = auth()->user()->id;
            } else {
                $speciality = new Speciality();
                $speciality->created_by = auth()->user()->id;
            }

            $speciality->name = $request->name;
            $speciality->save();

            if (!empty($request->image)) {
                if (preg_match('/data:image\/(.+);base64,(.*)/', $request->image, $matchings)) {
                    $imageData = base64_decode($matchings[2]);
                    $extension = $matchings[1];
                    $file_name = date('YmdHis') . rand(100,999). '_' . $speciality->id . '.' . $extension;
                    $path = 'speciality/'.$file_name;
                    Storage::put($path , $imageData);

                    if(!empty($speciality->image)){
                        if (Storage::exists('speciality/' . $speciality->image)) {
                            Storage::delete('speciality/' . $speciality->image);
                        }
                    }
                    $speciality->image = $file_name;
                    $speciality->save();
                }
            }
            
            DB::commit();
            return self::send_success_response([],'Speciality Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(){
        try {
            $list = Speciality::select('id','name','image')->orderBy('name', 'ASC')->get();
            
            return self::send_success_response($list,'Speciality content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
           
            $data = Speciality::withTrashed()->find($request->id);
            if ($data && $request->id) {
                DB::beginTransaction();
                if ($data->trashed()) {
                    $data->restore();
                    $data->deleted_by = null;
                    $data->save();                 
                    $message = 'Record Activated successfully!';   
                } else {
                    $data->delete();
                    $data->deleted_by =auth()->user()->id;
                    $data->save();
                    $message = 'Record Deleted successfully!';
                }
                DB::commit();
                return self::send_success_response([],$message);
            } else {
                return self::send_bad_request_response('Something went wrong! Please try again later.');
            }
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($e->getMessage());
        }
    }
}
?>