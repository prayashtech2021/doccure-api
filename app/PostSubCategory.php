<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PostSubCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name', 'post_category_id','created_by',
    ];
    public function getData(){
        return [
            'id' => $this->id,
            'category' => $this->category()->withTrashed()->select('id','name')->first(),
            'name' => $this->name,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,         
        ];
    }
    public function category(){
        return $this->belongsTo(PostCategory::class, 'post_category_id', 'id');
    }
}
