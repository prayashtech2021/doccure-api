<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { Setting };
use DB;
use Illuminate\Http\Request;
use Validator;

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

                        $extension = $file->getClientOriginalExtension();
                        $file_name = date('YmdHis') . '_' . $keyword . '.png';
                        $path = 'images/settings/';
                        $store = $file->storeAs($path, $file_name);

                        $setting_update = Setting::updateOrCreate(['keyword'=>$keyword],['slug'=>'general_settings', 'value' => $file_name, 'created_by'=> 1]);
                    }
                }
                if($request->footer_logo){ 
                    $file = $request->footer_logo;
                    $keyword = 'footer_logo';
                    $rules = array(
                        'footer_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    );
                }
                if($request->favicon){ 
                    $file = $request->favicon;
                    $keyword = 'favicon';
                    $rules = array(
                        'favicon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    );
                }
            }
            
            if($request->settings){
                $setting_result = json_decode($request->settings, true);
                foreach($setting_result as $data){
                    $update = Setting::updateOrCreate(['keyword'=>$data['keyword']],['slug'=>$data['slug'], 'value' => $data['value'], 'created_by'=> 1]);
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
            $data = Setting::select('id','slug','keyword','value')->get();
           
            return self::send_success_response($data, 'Setting data fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    
}
