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
use DB;
use Storage;
use Image;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
            'category_id' => 'nullable|numeric|exists:post_categories,id',
            'tag_name' => 'nullable|string|exists:post_tags,name',
            'viewable' => 'nullable|numeric|in:0,1',
            'search_keyword' => 'nullable|string|min:1|max:50',
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
                }elseif (auth('api')->user()->hasRole('doctor')) {
                    $list = Post::orderBy('created_at', $order_by)->where('created_by',auth('api')->user()->id);
                }
            }else{
                $list = Post::orderBy('created_at', $order_by);
                if(isset($request->category_id) && !empty($request->category_id)){
                    $list = $list->where('post_category_id',$request->category_id);
                }elseif(isset($request->tag_name) && !empty($request->tag_name)){
                    $list = $list->whereHas(['tags'=>function($qry){
                    $qry->where('name',$request->tag_name);
                }]);
                }elseif(!empty($request->search_keyword)){
                    $list = $list->where(function($qry)use($request){
                        $qry->where('title','like','%'.$request->search_keyword.'%')
                        ->orWhere('content','like','%'.$request->search_keyword.'%');
                    }); 
                }
                $result['categories'] = PostCategory::withCount('post')->orderBy('name')->get();
                $result['tags'] = PostTag::orderBy('name')->get();

                $latest = Post::latest()->limit(5)->get();
                $latest->each(function($item, $key){
                    $item->thumbnail_image = getPostImage($item->thumbnail_image);
                    $item->banner_image = getPostImage($item->banner_image);
                });
                $result['latest']=$latest;
            }

            if(isset($request->viewable) && $request->viewable==1){
                if ($request->bearerToken()) {
                    if (auth('api')->user()->hasRole('company_admin')) {
                    $list = $list->where('is_verified',1);
                    }else{
                        $list = $list->where('is_verified',1)->where('is_viewable',1);
                    }
                }else{
                    $list = $list->where('is_verified',1)->where('is_viewable',1);
                }
            }elseif(isset($request->viewable) && $request->viewable==0){
                $list = $list->where(function($qry){
                    $qry->where('is_verified',0)->orWhere('is_viewable',0); 
                }); 
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

    public function save(Request $request)
    {
        if ($request->post_id) { //edit
            $rules = array(
                'post_id' => 'integer|exists:posts,id',
                'title' => 'required|unique:posts,id,' . $request->post_id,
                'slug' => 'nullable|unique:posts,id,' . $request->post_id,
                'content' => 'required',
                'meta_description' => 'nullable',
                'meta_keywords' => 'nullable',
                'url' => 'nullable|url',
                'category_id' => 'required|exists:post_categories,id',
                'sub_category_id' => 'nullable|exists:post_sub_categories,id',
                'tags' => 'nullable',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048|dimensions:min_width=680,min_height=454',
            );
        } else {
            $rules = array(
                'title' => 'required|unique:posts',
                'slug' => 'nullable|unique:posts',
                'content' => 'required',
                'meta_description' => 'nullable',
                'meta_keywords' => 'nullable',
                'url' => 'nullable|url',
                'category_id' => 'required|exists:post_categories,id',
                'sub_category_id' => 'nullable|exists:post_sub_categories,id',
                'tags' => 'nullable',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048|dimensions:min_width=680,min_height=454',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->post_id) {
                $post = Post::find($request->post_id);
                if (!$post) {
                    return self::send_bad_request_response('Incorrect post id. Please check and try again!');
                }
                $post->updated_by = auth()->user()->id;
            } else {
                $post = new Post();
                $post->created_by = auth()->user()->id;
            }
            $post->post_category_id = $request->category_id;
            $post->post_sub_category_id = (!empty($request->sub_category_id) && ($request->sub_category_id>0))?$request->sub_category_id:null;
            $post->title = $request->title;
            if($request->slug){
                $slug=strtolower($request->slug);
                $str = str_replace(" ", "-", $slug);
            }else{
                $slug=strtolower($post->title);
                $str = str_replace(" ", "-", $slug);
            }
            $post->slug = $str;
            $post->meta_description = $request->meta_description;
            $post->meta_keywords = $request->meta_keywords;
            $post->url = $request->url;
            $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $request->content);
            $post->content = $content;
            $post->save();
            if (!empty($request->image)) {
                if (Storage::exists('images/blogs/' . $post->banner_image)) {
                    Storage::delete('images/blogs/' . $post->banner_image);
                }
                if (Storage::exists('images/blogs/' . $post->thumbnail_image)) {
                    Storage::delete('images/blogs/' . $post->thumbnail_image);
                }
                
                $originalImage = $request->file('image');
                $extension = $request->file('image')->getClientOriginalExtension();
                $file_name = '308x206_'.date('YmdHis'). '.png';
                $banner_file_name = '680x454_'.date('YmdHis').$post->id. '.png';
                $path = 'images/blogs';
                $store = $request->file('image')->storeAs($path, $banner_file_name);
                $img = imageResize($originalImage,$file_name, $path,308, 206);
                $post->banner_image = $banner_file_name;
                $post->thumbnail_image = $file_name;
                $post->save();
                
            }

            if($request->tags){
                $tagArray=explode(',',$request->tags);
                $tags = PostTag::where('post_id',$post->id)->first();
                    if($tags){
                        PostTag::where('post_id',$post->id)->forceDelete();
                    }
                foreach($tagArray as $tag){
                    PostTag::create(['post_id'=>$post->id, 'name'=>$tag]);
                }
            }

            DB::commit();
            return self::send_success_response([], 'Post Saved Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }
    public function destroy(Request $request)
    {
        return self::customDelete('\App\Post', $request->id);
    }

    public function verifyPost(Request $request)
    {
        $rules = array(
            'post_id' => 'required|numeric|exists:posts,id',
            'verified' => 'required_if:veiwable,""|in:0,1',
            'veiwable' => 'required_if:verified,""|in:0,1',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try{
            $post = Post::find($request->post_id);
            if($request->verified==1){
                if($post->is_verified==1){
                    return self::send_bad_request_response('The post has already verified!');
                }
                $post->is_verified=1;
                $post->save();
            }elseif($request->viewable==1){
                if($post->is_verified==0){
                    return self::send_bad_request_response('Please verify the Post first!');
                }elseif($post->viewable==1){
                    return self::send_bad_request_response('The Post is already viewable!');
                }
                $post->is_viewable=1;
                $post->save();
            }elseif($request->viewable==0){
                if($post->is_verified==0){
                    return self::send_bad_request_response('Please verify the Post first!');
                }
                $post->is_viewable=0;
                $post->save();
            }

        return self::send_success_response([], 'Post status updated Sucessfully');
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
