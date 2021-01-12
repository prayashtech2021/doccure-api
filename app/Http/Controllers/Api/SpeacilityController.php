<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\ { Speciality };
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class SpeacilityController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request) {
        $rules = array(
            'name' => 'required',
            'image' => 'nullable|string',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }

        try {
            DB::beginTransaction();
            if ($request->id) {
                $speciality = Speciality::find($request->id);
                $speciality->updated_by = auth()->user()->id;
            } else {
                $speciality = new Speciality();
                $speciality->created_by = auth()->user()->id;
            }

            $speciality->name = $request->name;

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
                }
            }
            $speciality->save();
            DB::commit();
            return self::send_success_response([],'Speciality Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(){
        try {
            $list = Speciality::select('id','name')->orderBy('name', 'ASC')->get();
            
            return self::send_success_response($list,'Speciality content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }
}
?>