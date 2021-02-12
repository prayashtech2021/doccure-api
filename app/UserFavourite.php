<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFavourite extends Model{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_favourites';

    protected $fillable = [
         'user_id', 'favourite_id',
    ];

    public function user() { 
        return $this->belongsTo('App\User','user_id','id'); 
    }
    public function favourite() { 
        return $this->belongsTo('App\User','favourite_id','id'); 
    }
}
?>