<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Speciality };
use DB;
use Illuminate\Http\Request;
use Validator;

class SpecialityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        if ($request->speciality_id) { //edit
            $rules = array(
                'speciality_id' => 'integer',
                'name' => 'required|unique:specialities,id,' . $request->speciality_id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            );
        } else {
            $rules = array(
                'name' => 'required|unique:specialities',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->speciality_id) {
                $speciality = Speciality::find($request->speciality_id);
                if (!$speciality) {
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
                $extension = $request->file('image')->getClientOriginalExtension();
                $file_name = date('YmdHis') . '_' . auth()->user()->id . '.png';
                $path = 'images/speciality';
                $store = $request->file('image')->storeAs($path, $file_name);

                $speciality->image = $file_name;
                $speciality->save();
            }

            DB::commit();
            return self::send_success_response([], 'Speciality Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList()
    {
        try {
            $list = Speciality::select('id', 'name', 'image')->orderBy('name', 'ASC')->get();

            return self::send_success_response($list, 'Speciality content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\Speciality', $request->id);
    }
}
