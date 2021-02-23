<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { PageContent };
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
                    $update = PageContent::where('slug',$data['slug'])->update([
                        'title'=> ($data['title'])? $data['title'] : '', 
                        'sub_title' => ($data['sub_title'])? $data['sub_title'] : '', 
                        'content' => ($data['content'])? $data['content'] : '', 
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
                    'login_image' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:max_width=1000,max_height=650',
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
        try {
            $getSettings = PageContent::get();
        
            $array = [];
            
            $array['header'] = getLangContent(8);

            foreach($getSettings as $result){
                if (!empty($result->image) && Storage::exists('images/cms-images/' . $result->image)) {
                    $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/cms-images/' . $result->image, now()->addMinutes(5)) : Storage::url('app/public/images/cms-images/' . $result->image);
                } else {
                    $path = url('img/logo.png');
                }
                $array[$result->slug] = [
                    'title'=>$result->title, 
                    'sub_title'=>$result->sub_title, 
                    'content'=>$result->content, 
                    'path'=>$path
                ];
            }
            $array['footer'] = getLangContent(9);

            return self::send_success_response($array, 'Page Content data fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    
}
