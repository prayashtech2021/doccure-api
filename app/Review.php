<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


class Review extends Model
{
    //
    use SoftDeletes;

    
    protected $fillable = [
        'user_id', 'reviewer_id', 'rating', 'description','created_by',
    ];

    public function getData(){
        return [
            'id' => $this->id,
            'user' => $this->user()->first()->basicProfile(),
            'reviewer' => $this->reviewer()->first()->basicProfile(),
            'rating' => $this->rating,
            'description' => $this->description,
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
        ];
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reviewer(){
        return $this->belongsTo(User::class, 'reviewer_id', 'id');
    }
}
?>