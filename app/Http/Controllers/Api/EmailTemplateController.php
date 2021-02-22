<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { EmailTemplate };
use DB;
use Illuminate\Http\Request;
use Validator;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        if ($request->email_template_id) { //edit
            $rules = array(
                'email_template_id' => 'integer|exists:email_templates,id',
                'slug' => 'required',
                'subject' => 'required',
                'content' => 'required',
            );
        } else {
            $rules = array(
                'slug' => 'required|unique:email_templates,slug',
                'subject' => 'required',
                'content' => 'required',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->email_template_id) {
                $template = EmailTemplate::find($request->email_template_id);
            } else {
                $template = new EmailTemplate();
            }

            $template->slug = $request->slug;
            $template->subject = $request->subject;
            $template->content = $request->content;
            $template->save();

            DB::commit();
            return self::send_success_response([], 'Email Template Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(Request $request)
    {
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';

            $list = EmailTemplate::select('id', 'slug', 'subject')->orderBy('slug', $order_by)->get();
            if($list){
                return self::send_success_response($list, 'Email Template content fetched successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function view($id){
        
        try { 
            $view = EmailTemplate::select('id', 'slug', 'subject', 'content')->where('id', $id)->first();
            $array = [
                'id' => $view->id,
                'slug' => $view->slug,
                'subject'=> $view->subject,
                'content' => htmlspecialchars_decode($view->content),
            ];
            if($view){
                return self::send_success_response($view, 'Email Template content fetched successfully');
            }else{
                return self::send_bad_request_response('Invalid Email Template Id. Kindly check and try again.');
            }
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\EmailTemplate', $request->id);
    }
}
