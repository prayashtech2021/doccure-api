<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Speciality };
use DB;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Carbon;
use Storage;

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
                'speciality_id' => 'integer|exists:specialities,id',
                'name' => 'required|unique:specialities,name,' . $request->speciality_id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=74,max_height=74',
                'duration' => 'required|date_format:H:i:s',
                'amount' => 'required|numeric',
            );
        } else {
            $rules = array(
                'name' => 'required|unique:specialities',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=74,max_height=74',
                'duration' => 'required|date_format:H:i:s',
                'amount' => 'required|numeric',
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

            $seconds = Carbon::parse('00:00:00')->diffInSeconds(Carbon::parse($request->duration));
            $seconds = (int) $seconds;

            $speciality->name = $request->name;
            $speciality->duration = $seconds;
            $speciality->amount = $request->amount;
            $speciality->save();

            if(!empty($speciality->image)){
                if (Storage::exists('images/speciality/' . $speciality->image)) {
                    Storage::delete('images/speciality/' . $speciality->image);
                }
            }

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

    public function getList(Request $request)
    {
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

    try {
        $paginate = $request->count_per_page ? $request->count_per_page : 30;
        $order_by = $request->order_by ? $request->order_by : 'asc';
        $pageNumber = $request->page ? $request->page : 1;

            $spl = Speciality::orderBy('id', $order_by);
            if($request->withtrash){
                $spl = $spl->withTrashed();
            }
            $paginatedata = $spl->paginate($paginate, ['*'], 'page', $pageNumber);
            
            $list = collect();
            $paginatedata->getCollection()->each(function ($speciality) use (&$list) {
                $list->push($speciality->getData());
            });

            $result['list'] = $list;
            $result['total_count'] = $paginatedata->total();
            $result['last_page'] = $paginatedata->lastPage();
            $result['current_page'] = $paginatedata->currentPage();

            return self::send_success_response($result, 'Speciality content fetched successfully');
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
