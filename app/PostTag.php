<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
    use EncryptedAttribute;

    protected $fillable = [
        'post_id', 'name'
    ];
    protected $encryptable = [
        'name'
    ];
    public function post(){
        return $this->belongsTo(Post::class,'post_id');
    }
}
