<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    
    public function post(){
        return $this->hasMany(Post::class,'post_category_id','id');
    }
}
