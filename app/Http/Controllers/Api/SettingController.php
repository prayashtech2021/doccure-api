<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Setting };
use DB;
use Illuminate\Http\Request;
use Validator;
use Storage;

class SettingController extends Controller
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

                if($request->company_logo){ 
                    $file = $request->company_logo;
                    $keyword = 'company_logo';
                    $rules = array(
                        'company_logo' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:max_width=200,max_height=50',
                    );
                    $valid = self::customValidation($request, $rules);
                    if($valid){ return $valid;}

                    if(!empty($file)){
                        $getSettings = Setting::where('slug','general_settings')->where('keyword',$keyword)->first();
                        if(!empty($getSettings->value)){
                            if (Storage::exists('images/company-images/' . $getSettings->value)) {
                                Storage::delete('images/company-images/' . $getSettings->value);
                            }
                        }
                        $extension = $file->getClientOriginalExtension();
                        $file_name = date('YmdHis') . '_' . $keyword . '.png';
                        $path = 'images/company-images/';
                        $store = $file->storeAs($path, $file_name);

                        $setting_update = Setting::where('keyword',$keyword)->update(['value' => $file_name, 'created_by'=> auth()->user()->id]);
                    }
                }
                if($request->footer_logo){ 
                    $file = $request->footer_logo;
                    $keyword = 'footer_logo';
                    $rules = array(
                        'footer_logo' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:max_width=200,max_height=50',
                    );
                    $valid = self::customValidation($request, $rules);
                    if($valid){ return $valid;}

                    if(!empty($file)){
                        $getSettings = Setting::where('slug','general_settings')->where('keyword',$keyword)->first();
                        if(!empty($getSettings->value)){
                            if (Storage::exists('images/company-images/' . $getSettings->value)) {
                                Storage::delete('images/company-images/' . $getSettings->value);
                            }
                        }
                        $extension = $file->getClientOriginalExtension();
                        $file_name = date('YmdHis') . '_' . $keyword . '.png';
                        $path = 'images/company-images/';
                        $store = $file->storeAs($path, $file_name);

                        $setting_update = Setting::where('keyword',$keyword)->update(['value' => $file_name, 'created_by'=> auth()->user()->id]);
                    }
                }
                if($request->favicon){ 
                    $file = $request->favicon;
                    $keyword = 'favicon';
                    $rules = array(
                        'favicon' => 'nullable|mimes:png,ico|dimensions:min_width=16,min_height=16,max_width=32,max_height=32',
                    );
                    $valid = self::customValidation($request, $rules);
                    if($valid){ return $valid;}

                    if(!empty($file)){
                        $getSettings = Setting::where('slug','general_settings')->where('keyword',$keyword)->first();
                        if(!empty($getSettings->value)){
                            if (Storage::exists('images/company-images/' . $getSettings->value)) {
                                Storage::delete('images/company-images/' . $getSettings->value);
                            }
                        }
                        $extension = $file->getClientOriginalExtension();
                        $file_name = date('YmdHis') . '_' . $keyword . '.png';
                        $path = 'images/company-images/';
                        $store = $file->storeAs($path, $file_name);

                        $setting_update = Setting::where('keyword',$keyword)->update(['value' => $file_name, 'created_by'=> auth()->user()->id]);
                    }
                }
                
            
            if($request->settings){
                $setting_result = json_decode($request->settings, true);
                foreach($setting_result as $data){
                    if(empty($data['value'])){
                        $msg = 'All fields are required.';
                        return self::send_bad_request_response($msg);
                    }
                    $update = Setting::where('keyword',$data['keyword'])->update(['value' => $data['value'], 'created_by'=> auth()->user()->id]);
                }
            }
           
            DB::commit();
            return self::send_success_response([], 'Setting Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getSetting(Request $request){
        try {
            $getSettings = Setting::all();
        
            $array = [];
           
            foreach($getSettings as $result){
                if(($result->keyword=='company_logo') || ($result->keyword=='footer_logo') || ($result->keyword=='favicon') ){
                    $array[$result->keyword] = getSettingImage($result->value);
                }else{
                    $array[$result->keyword] = $result->value;
                }
            }
            return self::send_success_response($array, 'Setting data fetched successfully');
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getPageSetting(Request $request){
        $common = [];
        $lang_id = ($request->language_id)? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8,$lang_id);
        if($request->content && $request->content ==1){
            $content = 'terms_and_condition';
        }else{
            $content = 'privacy_policy';     
        }
        $common['setting'] = getSettingData($content);
        $common['footer'] = getLangContent(9,$lang_id);
        
        try {
            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
                $valid = self::customValidation($request, $rules,$common);
                if($valid){ return $valid;}
            }
            return self::send_success_response([], 'Setting data fetched successfully',$common);
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage(),$common);
        }
    }

    
}
