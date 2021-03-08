<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Post;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try{
            // $user = auth()->user();
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            // if ($user->hasrole('company_admin')) {
                $list = Post::withTrashed()->orderBy('created_at', $order_by);
            // }

            $data = collect();
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($post) use (&$data) {
                $data->push($post->getData());
            });

            return self::send_success_response($data, 'Post Details Fetched Successfully');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function save(Request $request)
    {
        try{

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\Speciality', $request->id);
    }
}
