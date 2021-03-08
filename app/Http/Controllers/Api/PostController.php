<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Post;
use App\PostCategory;
use App\PostSubCategory;
use App\PostTag;

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
            $result['list'] = $data;
            $result['categories'] = PostCategory::orderBy('name','ASC')->get();
            $result['tags'] = PostTag::orderBy('name','ASC')->get();

            return self::send_success_response($result, 'Post Details Fetched Successfully');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function view(Request $request)
    {
        try{
            $list = Post::find($request->id);
            if(!$list){
                return self::send_bad_request_response('Incorrect Id. Please check and try again.');
            }
            $list = $list->getData();

            $result['list'] = $list;
            $result['categories'] = PostCategory::orderBy('name','ASC')->get();
            $result['sub_categories'] = PostSubCategory::orderBy('name','ASC')->get();

            return self::send_success_response($result, 'Post Details Fetched Successfully');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\Post', $request->id);
    }
}
