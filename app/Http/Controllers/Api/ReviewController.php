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
        $lang_id = ($request->language_id)? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8,$lang_id);
        $common['setting'] = getSettingData();
        if ($request->bearerToken()) {
            $common['menu'] = getAppMenu($request);
        }
        $common['lang_content'] = getLangContent(30,$lang_id);
        $common['footer'] = getLangContent(9,$lang_id);

        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        
        try {

            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
            }
            $valid = self::customValidation($request, $rules,$common);
            if ($valid) {return $valid;}
    
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            $list = Review::orderBy('id', $order_by);
            if(auth()->user()->hasRole('doctor')){
                $list = $list->whereUserId(auth()->user()->id);
            }
            $paginatedata = $list->paginate($paginate, ['*'], 'page', $pageNumber);
            $data = collect();
            $paginatedata->getCollection()->each(function ($provider) use (&$data) {
                $data->push($provider->getData());
            });
            if($request->route()->getName() == "reviewList"){
                $result['review_list'] = $data->toArray();
            }else{
                $result['review_list'] = $data;
            }
            $result['total_count'] = $paginatedata->total();
            $result['last_page'] = $paginatedata->lastPage();
            $result['current_page'] = $paginatedata->currentPage();
            
            return self::send_success_response($result, 'Review content fetched successfully',$common);
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage(),$common);
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
