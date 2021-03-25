<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PostCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name', 'created_by',
    ];
    public function getData(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d-m-Y h:i A'),
            'deleted_at' => $this->deleted_at,         ];
    }
    public function post(){
        return $this->hasMany(Post::class,'post_category_id','id');
    }
}
