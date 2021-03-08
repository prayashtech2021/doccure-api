<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Storage;
use URL;

class Chat extends Model
{
    protected $fillable = [
        'sender_id', 'recipient_id', 'message', 'file_path'
   ];

   public function sender() { 
    return $this->belongsTo('App\User','sender_id'); 
   }
   public function recipient() { 
    return $this->belongsTo('App\User','recipient_id'); 
   }

   public function getData(){
    return [
        'id' => $this->id,
        'sender_id' => $this->sender_id,
        'sender_name' => trim($this->sender->first_name . ' '. $this->sender->last_name),
        'sender_image' => getUserProfileImage($this->sender->id),
        'recipient_id' => $this->recipient_id,
        'recipient_name' => trim($this->recipient->first_name . ' '. $this->recipient->last_name),
        'recipient_image' => getUserProfileImage($this->recipient->id),
        'message' => $this->message,
        'file_path' => $this->getUserAttachment(),
        'read_status' => $this->read_status,
        'delete_status' => $this->delete_status,
        'created_at' => convertToLocal(Carbon::parse($this->created_at),'','d-m-Y h:i A'),
     ];
    }
    function getUserAttachment(){
        if (!empty($this->file_path) && Storage::exists('images/chat-attachments/' . $this->file_path)) {
            return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/chat-attachments/' . $this->file_path, now()->addMinutes(5)) : Storage::url('app/public/images/chat-attachments/' . $this->file_path);
        } else {
            return URL::asset('img/no_attachment.jpg');
        }
    }
}