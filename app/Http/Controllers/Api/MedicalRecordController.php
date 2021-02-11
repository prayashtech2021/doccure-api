<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ { MedicalRecord };
use DB;
use Illuminate\Http\Request;
use Validator;
use Storage;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        if ($request->medical_record_id) { //edit
            $rules = array(
                'medical_record_id' => 'required|numeric|exists:medical_records,id',
                'appointment_id' => 'required|numeric|exists:appointments,id',
                'consumer_id' => 'required|numeric|exists:users,id',
                'description' => 'required',
                'document_file' => 'required|image|mimes:jpeg,png,jpg,pdf,doc|max:2048',
            );
        } else {
            $rules = array(
                'appointment_id' => 'required|numeric|exists:appointments,id',
                'consumer_id' => 'required|numeric|exists:users,id',
                'description' => 'required',
                'document_file' => 'required|image|mimes:jpeg,png,jpg,pdf,doc|max:2048',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->medical_record_id) {
                $record = MedicalRecord::find($request->medical_record_id);
                $record->updated_by = auth()->user()->id;
            } else {
                $record = new MedicalRecord();
                $record->created_by = auth()->user()->id;
            }
            $record->appointment_id = $request->appointment_id;
            $record->provider_id = auth()->user()->id;
            $record->consumer_id = $request->consumer_id;
            $record->description = $request->description;
            $record->save();

            if (!empty($request->document_file)) {
                $extension = $request->file('document_file')->getClientOriginalExtension();
                $file_name = date('YmdHis') . '_' . auth()->user()->id . '.png';
                $path = 'images/records';
                $store = $request->file('document_file')->storeAs($path, $file_name);

                $record->document_file = $file_name;
                $record->save();
            }

            DB::commit();
            return self::send_success_response([], 'Records Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(Request $request)
    {
        $rules = array(
            'consumer_id' => 'required|numeric|exists:users,id',
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            $list = MedicalRecord::with('doctor');
            if(auth()->user()->hasrole('doctor')){
                $list = $list->where('provider_id',auth()->user()->id);
            }
            if($request->consumer_id){
                $list = $list->where('consumer_id',$request->consumer_id);
            }
            $list = $list->orderBy('id', $order_by);

            $data = collect();
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($result) use (&$data) {
                $data->push($result->getData());
            });

            if($data){
                return self::send_success_response($data, 'Medical Record content fetched successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getView($record_id){
        try {

            $list = MedicalRecord::with('doctor','patient')->where('id',$record_id)->orderBy('id', 'ASC')->get();
            if($list){
                return self::send_success_response($list, 'Medical Record content fetched successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\MedicalRecord', $request->id);
    }
}
