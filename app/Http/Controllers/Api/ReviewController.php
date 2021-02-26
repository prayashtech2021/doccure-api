<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Review,Appointment };
use DB;
use Illuminate\Http\Request;
use Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        if ($request->review_id) { //edit
            $rules = array(
                'review_id' => 'integer|exists:reviews,id',
                'appointment_id' => 'required|integer|exists:appointments,id',
                'user_id' => 'required|integer|exists:users,id',
                'reviewer_id' => 'integer|exists:users,id',
                'rating' => 'integer|min:1|max:5',
                'description' => 'nullable',
            );
        } else {
            $rules = array(
                'appointment_id' => 'required|integer|exists:appointments,id',
                'user_id' => 'required|integer|exists:users,id',
                'reviewer_id' => 'integer|exists:users,id',
                'rating' => 'integer|min:1|max:5',
                'description' => 'nullable',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->review_id) {
                $data = Review::find($request->review_id);
                $data->updated_by = auth()->user()->id;
            } else {
                $data = new Review();
                $data->created_by = auth()->user()->id;
                Appointment::where('id',$request->appointment_id)->update(['review_status'=>1]);
            }
            $data->appointment_id = $request->appointment_id;
            $data->user_id = $request->user_id;
            $data->reviewer_id = $request->reviewer_id;
            $data->rating = $request->rating;
            $data->description = $request->description;
            $data->save();

            DB::commit();
            return self::send_success_response([], 'Review Stored Sucessfully');

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
            'language_id' => 'integer|exists:languages,id',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            $array = [];
            $lang_id = ($request->language_id)? $request->language_id : defaultLang();
            //$array['header'] = getLangContent(8,$lang_id);
            //$array['setting'] = getSettingData();
            //$array['lang_content'] = getLangContent(2,$lang_id);
            
            $list = Review::orderBy('id', $order_by);
            if(auth()->user()->hasRole('doctor')){
                $list = $list->whereUserId(auth()->user()->id);
            }
            $paginatedata = $list->paginate($paginate, ['*'], 'page', $pageNumber);
            $data = collect();
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($provider) use (&$data) {
                $data->push($provider->getData());
            });
            $array['total_count'] = $paginatedata->total();
            $array['review_list'] = $data;
            //$array['footer'] = getLangContent(9,$lang_id);

            return self::send_success_response($array, 'Review content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function doctorReply(Request $request){
        $rules = array(
            'review_id' => 'integer|exists:reviews,id',
            );
        
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();

            $update = Review::find($request->review_id);
            $update->reply = $request->reply;
            $update->updated_by = auth()->user()->id;
            $update->save();

            DB::commit();
            return self::send_success_response([], 'Updated Sucessfully');

        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\Review', $request->id);
    }
}
