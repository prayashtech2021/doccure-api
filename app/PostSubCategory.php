<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PostSubCategory extends Model
{
    use SoftDeletes;
    use EncryptedAttribute;

    protected $fillable = [
        'name', 'post_category_id','created_by',
    ];
    protected $encryptable = [
        'name'
    ];
    public function getData(){
        return [
            'id' => $this->id,
            'category' => $this->category()->withTrashed()->select('id','name')->first(),
            'name' => $this->name,
            'created_at' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'M-d-Y h:i A'),
            'deleted_at' => $this->deleted_at,         
        ];
    }
    public function category(){
        return $this->belongsTo(PostCategory::class, 'post_category_id', 'id');
    }
}
            