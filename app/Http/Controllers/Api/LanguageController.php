<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Language;
use App\MultiLanguage;
use App\PageMaster;
use DB;
use App\Chat;

class LanguageController extends Controller
{
    public function list(Request $request)
    {
        try {
            $language = Language::orderBy('name');
            if($request->is_all){
                $language = $language->get();
            }else{
                $language = $language->where('is_enable',1)->get();
            }
            removeMetaColumn($language);
            return self::send_success_response($language);

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function update(Request $request)
    {
        $rules = self::customValidation($request, [
            'language_id' => 'required|exists:languages,id',
        ]);
        if($rules){ return $rules;}
        try {
            $user = auth()->user();
            if($user->language_id != $request->language_id){
                $user->language_id = $request->language_id;
                $user->save();
                removeMetaColumn($user);
                return self::send_success_response($user,'User Language updated successfully');
            }
            removeMetaColumn($user);
            return self::send_success_response($user,'Already exist');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function save(Request $request){
        if ($request->language_id) {
            $rules = [
                'language_id' => 'required|numeric|exists:languages,id',
                'name' => 'required|unique:languages,name,'.$request->language_id,
                'code' => 'required',
                'is_default' => 'required',
                ];
        }else{
            $rules = [
                'name' => 'required|unique:languages',
                'code' => 'required',
                'is_default' => 'required',
                ];
        }
        
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if($request->is_default == 1){  
                Language::where('id', '>', 0)->update(['is_default'=>0]);
                DB::commit();
            }
            if ($request->language_id) {
                $language = Language::find($request->language_id);
                $language->updated_by = auth()->user()->id;
            } else {
                $language = new Language();
                $language->created_by = auth()->user()->id;
            }
            $language->name = $request->name;
            $language->code = $request->code;
            $language->is_default = $request->is_default;
            $language->save();
            DB::commit();
            if(Language::where('is_default',1)->count() == 0){  //if all sets as not defualt
                Language::where('id', 1)->update(['is_default'=>1]);    //automatically english will be set
                DB::commit();
            }

            if(!$request->language_id){
                $get_keyword = MultiLanguage::select('page_master_id','keyword')->where('language_id',1)->get();
                foreach($get_keyword as $keyword){
                    $new = MultiLanguage::create([
                        'page_master_id' => $keyword->page_master_id,
                        'language_id' => $language->id,
                        'keyword' => $keyword->keyword,
                        'created_by' => auth()->user()->id
                    ]);
                }
            }
            return self::send_success_response([],'Language Updated Successfully');
        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function enableLang(Request $request)
    {
        $rules = self::customValidation($request, [
            'language_id' => 'required|exists:languages,id',
        ]);
        if($rules){ return $rules;}
        try {
            if($request->check_keyword){
                $count = Language::whereId($request->language_id)->whereNull('keywords')->count();
                if($count){
                    return self::send_bad_request_response('Language Not yet updated for all keywords');
                }else{
                    return self::send_success_response([],'Language updated for all keywords'); 
                }
            }
                $language = Language::find($request->language_id);
                $language->updated_by = auth()->user()->id;
                $language->is_enable = $request->is_enable;
                $language->save();
                return self::send_success_response([],'Language updated successfully');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function multiLangEdit(Request $request){
        if($request->page_master_id && $request->language_id){
            $rules = [
                'page_master_id' => 'required|exists:page_masters,id',
                'language_id' => 'required|exists:languages,id',
                ];
    
            $valid = self::customValidation($request, $rules);
            if($valid){ return $valid;}

            $data['multi_language'] = MultiLanguage::select('id','keyword','value')->where('page_master_id',$request->page_master_id)->where('language_id',$request->language_id)->get();
        }else{
            $data['page_master'] = PageMaster::get();
            $data['language'] = Language::select('id','name','code')->get();
        }
        $data['unread_noty_count'] = auth()->user()->unreadNotifications()->count();
        $data['unread_chat_count'] = Chat::where('recipient_id',auth()->user()->id)->where('read_status',0)->count();
        
        return self::send_success_response($data,'List Fetched Successfully');
    }

    public function multiLangSave(Request $request){
        $rules = [
            'page_master_id' => 'required|exists:page_masters,id',
            'language_id' => 'required|exists:languages,id',
            ];

        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            $keyword_update = json_decode($request->keyword_update, true);
            foreach($keyword_update as $update){
                $get = MultiLanguage::where('id',$update['id'])->update(['value'=> $update['value'], 'updated_by'=> auth()->user()->id]); 
            }
           
            DB::commit();
            return self::send_success_response([],'Language Updated Successfully');
        } catch (Exception | Throwable $exception) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }
}
