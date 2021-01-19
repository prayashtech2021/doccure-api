<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $guard_name='web';
    
    protected $fillable = [
        'name','guard_name','created_by',
    ];

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
