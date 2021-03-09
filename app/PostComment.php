<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
  

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
