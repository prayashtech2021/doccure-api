<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Feature };
use DB;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Carbon;
use Storage;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        if ($request->feature_id) { //edit
            $rules = array(
                'feature_id' => 'integer|exists:features,id',
                'name' => 'required|unique:features,name,' . $request->feature_id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            );
        } else {
            $rules = array(
                'name' => 'required|unique:features',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->feature_id) {
                $feature = Feature::find($request->feature_id);
                if (!$feature) {
                    return self::send_bad_request_response('Incorrect Feature id. Please check and try again!');
                }
                $feature->updated_by = auth()->user()->id;
            } else {
                $feature = new Feature();
                $feature->created_by = auth()->user()->id;
            }

            $feature->name = $request->name;
            $feature->save();
            if(!empty($feature->image)){
                if (Storage::exists('images/features/' . $feature->image)) {
                    Storage::delete('images/features/' . $feature->image);
                }
            }
            if (!empty($request->image)) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $file_name = date('YmdHis') . '_' . auth()->user()->id . '.png';
                $path = 'images/features';
                $store = $request->file('image')->storeAs($path, $file_name);

                $feature->image = $file_name;
                $feature->save();
            }

            DB::commit();
            return self::send_success_response([], 'Feature Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(Request $request)
    {
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';

            $feature = Feature::orderBy('name', $order_by);
            if($request->withtrash){
                $feature = $feature->withTrashed();
            }
            $list = collect();
            $feature->each(function ($feature) use (&$list) {
                $list->push($feature->getData());
            });
            
            return self::send_success_response($list, 'Feature content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\Feature', $request->id);
    }
}
