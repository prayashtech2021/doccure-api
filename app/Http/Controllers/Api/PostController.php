<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Post;
use App\PostCategory;
use App\PostSubCategory;
use App\PostTag;
use App\PostComment;
use Illuminate\Support\Carbon;

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

            if ($request->bearerToken()) {
                if (auth('api')->user()->hasRole('company_admin')) {
                    $list = Post::withTrashed()->orderBy('created_at', $order_by);
                }
            }else{
                $list = Post::orderBy('created_at', $order_by);
                $result['categories'] = PostCategory::withCount('post')->orderBy('name')->get();
                $result['tags'] = PostTag::orderBy('name')->get();
            }

            $data = collect();
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($post) use (&$data) {
                $data->push($post->getData());
            });
            $result['list'] = $data;
            

            return self::send_success_response($result, 'Post Details Fetched Successfully');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function view(Request $request)
    {
        try{
            $list = $post = Post::find($request->id);
            if(!$list){
                return self::send_bad_request_response('Incorrect Id. Please check and try again.');
            }
            $list = $list->getData();
            
            $result['list'] = $list;
            $result['categories'] = PostCategory::withCount('post')->orderBy('name','ASC')->get();
            $result['sub_categories'] = PostSubCategory::orderBy('name','ASC')->get();
            if (!$request->bearerToken()) {
                $result['tags'] = PostTag::orderBy('name')->get();
                $latest = Post::latest()->limit(5)->get();

                $latest->each(function($item, $key){
                    $item->thumbnail_image = getPostImage($item->thumbnail_image);
                    $item->banner_image = getPostImage($item->banner_image);
                });

                $result['latest']=$latest;
                
            //for comments
            $getcom = collect();
            $coms = $post->comments()->get();
            $coms->each( function($comments)use (&$getcom){
                $arr=[];$cnt=0;
                $comments->replies()->each( function($replies)use (&$arr,&$cnt){
                    $arr[$cnt]['id']=$replies->id;
                    $arr[$cnt]['user_details']=$replies->user->basicProfile();
                    $arr[$cnt]['reply']=$replies->comments;
                    $arr[$cnt]['created_at']=convertToLocal(Carbon::parse($replies->created_at),'','d-m-Y h:i A');
                    $cnt++;
                });
                $getcom->push(['id'=>$comments->id, 'user_details'=>$comments->user->basicProfile(), 'comment'=>$comments->comments, 'created_at'=>convertToLocal(Carbon::parse($comments->created_at),'','d-m-Y h:i A'), 'reply'=>$arr]);  
            });
            $result['comments_list'] = $getcom;

            }


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
