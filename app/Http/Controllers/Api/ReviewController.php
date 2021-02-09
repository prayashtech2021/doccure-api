<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Review };
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
                'user_id' => 'integer|exists:users,id',
                'reviewer_id' => 'integer|exists:users,id',
                'rating' => 'integer|min:1|max:5',
                'description' => 'nullable',
            );
        } else {
            $rules = array(
                'user_id' => 'integer|exists:users,id',
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
            } else {
                $data = new Review();
            }

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
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            $list = Review::orderBy('id', $order_by);
            
            $data = collect();
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($provider) use (&$data) {
                $data->push($provider->getData());
            });

            return self::send_success_response($data, 'Review content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\Review', $request->id);
    }
}
