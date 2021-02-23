<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\SendMessage;
use Illuminate\Http\Request;
use App\Chat;

class ChatController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $rules = array(
            'recipient_id' => 'required|exists:users,id',
            'count_per_page' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $pageNumber = $request->page ? $request->page : 1;
            $order_by = $request->order_by ? $request->order_by : 'desc';

            $list = Chat::where(function($qry) use ($request){
                $qry->where(['sender_id'=>auth()->user()->id, 'recipient_id'=>$request->recipient_id])
                ->orWhere(['sender_id'=>$request->recipient_id, 'recipient_id'=>auth()->user()->id]);
            })->orderBy('id',$order_by);

            $data = collect();
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($chat) use (&$data) {
                $data->push($chat->getData());
            });

            return self::send_success_response($data);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
    public function send(Request $request)
    {
        if($request->attachments){
            $rules = array(
                'recipient_id' => 'required|exists:users,id',
                'attachments' => 'required|mimes:jpeg,png,jpg,pdf,doc|max:2048',
            );
        }else{
            $rules = array(
                'recipient_id' => 'required|exists:users,id',
                'message' => 'required|string|min:1|max:255',
            );
        }
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $user = auth()->user();

            if (!empty($request->attachments)) {    //only attachments
                $extension = $request->file('attachments')->getClientOriginalExtension();
                $file_name = date('YmdHis') . '_' . auth()->user()->id . '.'.$extension;
                $path = 'images/chat-attachments';
                $store = $request->file('attachments')->storeAs($path, $file_name);

                $message = $user->chats()->create([
                    'recipient_id' => $request->input('recipient_id'),
                    'file_path' => $file_name,
                ]);
            }else{  //only text message
                $message = $user->chats()->create([
                    'recipient_id' => $request->input('recipient_id'),
                    'message' => $request->input('message'),
                ]);
            }

            if ($message) {
                // event(new SendMessage($message));
            }
            return self::send_success_response('Message Sent Successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
