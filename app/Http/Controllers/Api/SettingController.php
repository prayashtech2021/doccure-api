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
                        'company_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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

                        $setting_update = Setting::where('keyword',$keyword)->update(['slug'=>'general_settings', 'value' => $file_name, 'created_by'=> auth()->user()->id]);
                    }
                }
                if($request->footer_logo){ 
                    $file = $request->footer_logo;
                    $keyword = 'footer_logo';
                    $rules = array(
                        'footer_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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

                        $setting_update = Setting::where('keyword',$keyword)->update(['slug'=>'general_settings', 'value' => $file_name, 'created_by'=> auth()->user()->id]);
                    }
                }
                if($request->favicon){ 
                    $file = $request->favicon;
                    $keyword = 'favicon';
                    $rules = array(
                        'favicon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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

                        $setting_update = Setting::where('keyword',$keyword)->update(['slug'=>'general_settings', 'value' => $file_name, 'created_by'=> auth()->user()->id]);
                    }
                }
            
            
            if($request->settings){
                $setting_result = json_decode($request->settings, true);
                foreach($setting_result as $data){
                    $update = Setting::where('keyword',$data['keyword'])->update(['slug'=>$data['slug'], 'value' => $data['value'], 'created_by'=> auth()->user()->id]);
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
            $getSettings = Setting::get();
        
            $array = [];
            foreach($getSettings as $result){
                if(($result->keyword=='company_logo') || ($result->keyword=='footer_logo') || ($result->keyword=='favicon') ){
                    if (!empty($result->value) && Storage::exists('images/company-images/' . $result->value)) {
                        $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/company-images/' . $result->value, now()->addMinutes(5)) : Storage::url('app/public/images/company-images/' . $result->value);
                    } else {
                        $path = url('img/logo.png');
                    }
                    $array[$result->keyword] = $path;
                }else{
                    $array[$result->keyword] = $result->value;
                }
            }
            return self::send_success_response($array, 'Setting data fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    
}
