<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\SendMessage;
use Illuminate\Http\Request;
use App\Chat;
use App\User;
use App\Notifications\ChatNoty;

class ChatController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $common = [];
        $lang_id = ($request->language_id)? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8,$lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(16,$lang_id);
        $common['footer'] = getLangContent(9,$lang_id);

        $rules = array(
            'recipient_id' => 'required|exists:users,id',
            'count_per_page' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        if ($request->language_id) {
            $rules['language_id'] = 'integer|exists:languages,id';
        }
        $valid = self::customValidation($request, $rules,$common);
        if ($valid) {return $valid;}

        try {
            updateLastSeen(auth()->user());
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $pageNumber = $request->page ? $request->page : 1;
            $order_by = $request->order_by ? $request->order_by : 'desc';

            $list = Chat::where(function($qry) use ($request){
                $qry->whereRaw("sender_id=".auth()->user()->id." and recipient_id=".$request->recipient_id)
                ->orWhereRaw("sender_id=".$request->recipient_id." and recipient_id=".auth()->user()->id);
            })->orderBy('id',$order_by);


            $paginatedata = $list->paginate($paginate, ['*'], 'page', $pageNumber);

            $data = collect();
            $paginatedata->getCollection()->each(function ($chat) use (&$data) {
                $data->push($chat->getData());
            });
           

            $array['list'] = $data;
            $array['total_count'] = $paginatedata->total();
            $array['last_page'] = $paginatedata->lastPage();
            $array['current_page'] = $paginatedata->currentPage();

            $update = Chat::where('sender_id',$request->recipient_id)->where('recipient_id',auth()->user()->id)->update(['read_status'=>1]);
            
            return self::send_success_response($array,'Chat List Fetched Successfully',$common);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(),$common);
        }
    }
    public function send(Request $request)
    {
        if($request->attachments){
            $rules = array(
                'recipient_id' => 'required|exists:users,id',
                'attachments' => 'required|mimes:jpeg,png,jpg,pdf,doc,docx|max:2048',
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
            updateLastSeen(auth()->user());
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
                $receiver = User::find($request->input('recipient_id')); 
                $receiver->notify(new ChatNoty());
            
                event(new SendMessage($message));
                  
                if(!empty($receiver->device_id)){
                    $msgdata['recipient_id'] = $receiver->recipient_id;
                    $msgdata['recipient_name'] = $receiver->first_name.' '.$receiver->last_name;
                    $msgdata['recipient_image'] = getUserProfileImage($receiver->id);
                    $msgdata['sender_id'] = auth()->user()->id;
                    $msgdata['sender_name'] = $user->first_name.' '.$user->last_name
                    $msgdata['sender_image'] = getUserProfileImage(auth()->user()->id);
                    $msgdata['type']= 'Message';
                    
                    $notifydata['message']=$message;
                    $notifydata['notifications_title']=auth()->user()->name;
                    $notifydata['additional_data'] = $msgdata;
                    $notifydata['device_id'] = $receiver->device_id;
                    if($receiver->device_type=='Android' && (!empty($notifydata['device_id']))){
                        sendFCMNotification($notifydata);
                    }
                    if($receiver->device_type=='IOS'){
                    // sendiosNotification($notifydata);
                    }
                }
            }
            return self::send_success_response('Message Sent Successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
