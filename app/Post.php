<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Post extends Model
{
    use SoftDeletes;

    public function getData(){
        return [
            'id' => $this->id,
            'title' => $this->title,
            'thumbnail_image' => getPostImage($this->thumbnail_image),
            'banner_image' => getPostImage($this->banner_image),
            'author' => $this->author->basicProfile(),
            'is_verified' => $this->is_verified,
            'is_viewable' => $this->is_viewable,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'url' => $this->url,
            'status' => (!empty($this->deleted_at))?false:true,
            'created_at' => convertToLocal(Carbon::parse($this->created_at),'','d-m-Y h:i A'),
            
        ];
    }

    public function author(){
        return $this->belongsTo(User::class,'created_by','id');
    }
}
