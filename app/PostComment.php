<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use EncryptedAttribute;
    protected $encryptable = [
        'comments'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function post(){
        return $this->belongsTo(Post::class,'post_id');
    }
    public function replies(){
        return $this->hasMany(PostComment::class,'parent_id');
    }
}
