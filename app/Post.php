<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Post extends Model
{
    use SoftDeletes;
   // use EncryptedAttribute;

    // protected $encryptable = [
    //     'content','title','meta_description','meta_keywords'
    // ];
    public function getData(){
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'thumbnail_image' => getPostImage($this->thumbnail_image),
            'banner_image' => getPostImage($this->banner_image),
            'content' => $this->content,
            'author' => $this->author->basicProfile(),
            'is_verified' => $this->is_verified,
            'is_viewable' => $this->is_viewable,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'url' => $this->url,
            'status' => (!empty($this->deleted_at))?false:true,
            'created_at' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d/m/Y'),
            'category' => $this->category()->select('id','name')->first(),
            'sub_category' => $this->subCategory()->select('id','name')->first(),
            'tags' => $this->tags()->select('id','name')->get(),
            
        ];
    }

    public function author(){
        return $this->belongsTo(User::class,'created_by','id');
    }
    public function category(){
        return $this->belongsTo(PostCategory::class,'post_category_id','id');
    }
    public function subCategory(){
        return $this->belongsTo(PostSubCategory::class,'post_sub_category_id','id');
    }
    public function tags(){
        return $this->hasMany(PostTag::class,'post_id');
    }
    public function comments(){
        return $this->hasMany(PostComment::class,'post_id','id')->whereNull('parent_id');
    }
    
}
