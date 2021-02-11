<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { SocialMedia };
use DB;
use Illuminate\Http\Request;
use Validator;

class SocialMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request){
  
        try {
            DB::beginTransaction();
            
            if($request->social_media){
                $result = json_decode($request->social_media, true);
                foreach($result as $data){
                    $update = SocialMedia::UpdateorCreate([
                            'provider_id'=>$data['provider_id'],
                            'name'=>$data['name']
                        ],
                        [
                            'provider_id' => $data['provider_id'],
                            'name' => $data['name'],
                            'link'=>$data['link'],
                        ]);
                }
            }

            DB::commit();
            return self::send_success_response([], 'Social media Stored Successfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    
    public function view($provider_id){
        
        try {

            $view = SocialMedia::select('id', 'provider_id', 'name', 'link')->where('provider_id', $provider_id)->get();
            if($view){
                $array = [];
                foreach($view as $result){
                    $array[$result->name] = $result->link;
                }
                return self::send_success_response($array, 'Social media content fetched successfully');
            }else{
                return self::send_bad_request_response('Invalid Social media Id. Kindly check and try again.');
            }
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\SocialMedia', $request->id);
    }
}
