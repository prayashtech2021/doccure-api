<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { PageContent,User,Speciality,Feature,Post,Banner };
use DB;
use Illuminate\Http\Request;
use Validator;
use Storage;

class PageContentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        try {
            DB::beginTransaction();

            if($request->cms){
                $setting_result = json_decode($request->cms, true);
                foreach($setting_result as $data){  
                    if(empty($data['title'])){
                        return self::send_bad_request_response('All Fields are required');
                    } 
                    $update = PageContent::where('slug',$data['slug'])->update([
                        'title'=> ($data['title'])? $data['title'] : '', 
                        'sub_title' => isset($data['sub_title'])? $data['sub_title'] : '', 
                        'content' => isset($data['content'])? $data['content'] : '', 
                        ]);
                } //foreach
            } 
            
            if($request->banner_image){ 
                $file = $request->banner_image;
                $keyword = 'banner';
                $rules = array(
                    'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:max_width=1600,max_height=210',
                );
            }elseif($request->feature_image){ 
                $file = $request->feature_image;
                $keyword = 'features';
                $rules = array(
                    'feature_image' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:max_width=421,max_height=376',
                );
            }elseif($request->login_image){ 
                $file = $request->login_image;
                $keyword = 'login';
                $rules = array(
                    'login_image' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:max_width=1000,max_height=750',
                );
            }

            if(!empty($file)){
                $valid = self::customValidation($request, $rules);
                if($valid){ return $valid;}

                $getSettings = PageContent::where('slug',$keyword)->first();
                if(!empty($getSettings->image)){
                    if (Storage::exists('images/cms-images/' . $getSettings->image)) {
                        Storage::delete('images/cms-images/' . $getSettings->image);
                    }
                }
                $extension = $file->getClientOriginalExtension();
                $file_name = date('YmdHis') . '_' . $keyword . '.png';
                $path = 'images/cms-images/';
                $store = $file->storeAs($path, $file_name);

                $setting_update = PageContent::where('slug',$keyword)->update(['image' => $file_name]);
            }
            
            DB::commit();
            return self::send_success_response([], 'CMS Setting Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(Request $request){

        $lang_id = ($request->language_id)? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8,$lang_id);
        $common['setting'] = getSettingData();
        $common['lang_content'] = getLangContent(2,$lang_id);
        $common['footer'] = getLangContent(9,$lang_id);

        try {
            if($request->language_id){ 
                $rules = array(
                    'language_id' => 'integer|exists:languages,id',
                );
                $valid = self::customValidation($request, $rules,$common);
                if($valid){ return $valid;}
            } 
        
            $getSettings = PageContent::get();
            
            $banner = Banner::orderBy('id', 'desc');
                
            $banner_list = collect();
            $banner->each(function ($banner) use (&$banner_list) {
                $banner_list->push($banner->getData());
            });

            if($request->type == 1){ // 1 only for lang page content 
                $provider_list = User::role('doctor')->orderBy('id','asc');
                if($request->route()->getName() == "landingPage"){
                    $mobile = 1;
                }else{
                    $mobile = 0;
                }
                $doc_array = collect();
                $provider_list->each(function ($provider) use (&$doc_array,$mobile) {
                    $doc_array->push($provider->basicProfile($mobile));
                });

                $speciality = Speciality::orderBy('id','asc');
                $spl_array = collect();
                $speciality->each(function ($data) use (&$spl_array) {
                    $spl_array->push($data->getData());
                });
                
                $feature = Feature::orderBy('id', 'desc');
                
                $list = collect();
                $feature->each(function ($feature) use (&$list) {
                    $list->push($feature->getData());
                });

                $blog_list = Post::where('is_verified',1)->where('is_viewable',1);

                $blog_data = collect();
                $blog_list->paginate(4)->getCollection()->each(function ($post) use (&$blog_data) {
                    $blog_data->push($post->getData());
                });

                if($request->route()->getName() == 'landingPage'){
                    $array['doctors'] = $doc_array->toArray();
                    $array['speciality'] = $spl_array->toArray();
                    $array['features_list'] = $list->toArray();
                    $array['blog_list'] = $blog_data->toArray();
                }else{
                    $array['doctors'] = $doc_array;
                    $array['speciality'] = $spl_array;
                    $array['features_list'] = $list;
                    $array['blog_list'] = $blog_data;
                }

            }
            
            foreach($getSettings as $result){
                if (!empty($result->image) && Storage::exists('images/cms-images/' . $result->image)) {
                    $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/cms-images/' . $result->image, now()->addMinutes(5)) : Storage::url('app/public/images/cms-images/' . $result->image);
                } elseif(!empty($result->image)){
                    $path = url('img/cms-images/' . $result->image);
                }else{
                    $path = url('img/logo.png');
                }
                $array[$result->slug] = [
                    'slug' =>$request->slug,
                    'title'=>$result->title, 
                    'sub_title'=>$result->sub_title, 
                    'content'=>$result->content, 
                    'path'=>$path
                ];
            }
            $array['banner_list'] = $banner_list;

            return self::send_success_response($array, 'Page Content data fetched successfully',$common);
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage(),$common);
        }
    }

    public function saveBanner(Request $request)
    {
        if ($request->banner_id) { //edit
            $rules = array(
                'banner_id' => 'integer|exists:banners,id',
                'name' => 'required|unique:banners,name,' . $request->banner_id,
                'button_name' => 'required',
                'link' => 'nullable',
            );
        } else {
            $rules = array(
                'name' => 'required|unique:banners',
                'button_name' => 'required',
                'link' => 'nullable',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            
            if ($request->banner_id) {
                $banner = Banner::find($request->banner_id);
                if (!$banner) {
                    return self::send_bad_request_response('Incorrect Banner id. Please check and try again!');
                }
                $banner->updated_by = auth()->user()->id;
            } else {
                $banner = new Banner();
                $banner->created_by = auth()->user()->id;
            }

            $banner->name = $request->name;
            $banner->button_name = $request->button_name;
            $banner->link = $request->link;
            $banner->save();

            
            if (!empty($request->image)) {
                if(!empty($banner->image)){
                    if (Storage::exists('images/cms-images/' . $banner->image)) {
                        Storage::delete('images/cms-images/' . $banner->image);
                    }
                }
                $extension = $request->file('image')->getClientOriginalExtension();
                $file_name = date('YmdHis') . '_' . auth()->user()->id . '.png';
                $path = 'images/cms-images';
                $store = $request->file('image')->storeAs($path, $file_name);

                $banner->image = $file_name;
                $banner->save();
            }

            DB::commit();
            return self::send_success_response([], 'Banner Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    
}
