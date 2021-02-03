<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'line_1', 'line_2','country_id', 'state_id','city_id','postal_code','created_by',
   ];

   public function country()
    {
        return $this->belongsTo('App\Country','country_id')->select(array('id', 'name'));
    }
    public function state()
    {
        return $this->belongsTo('App\State','state_id')->select(array('id', 'name'));
    }
    public function city()
    {
        return $this->belongsTo('App\City','city_id')->select(array('id', 'name'));
    }

    public function addressImage(){
        return $this->hasMany(AddressImage::class);
    }

    public function contactAddress() {
        return $address = [
            'line_1' => ucfirst($this->line_1),
            'line_2' => ucfirst($this->line2),
            'country' => ucfirst(optional($this->country)->name),
            'state' => ucfirst(optional($this->state)->name),
            'city' => ucfirst(optional($this->city)->name),
        ];
    }

    public function clinicAddress() {
        return $address = [
            'name' => ucfirst($this->name),
            'line_1' => ucfirst($this->line_1),
            'line_2' => ucfirst($this->line2),
            'country' => ucfirst(optional($this->country)->name),
            'state' => ucfirst(optional($this->state)->name),
            'city' => ucfirst(optional($this->city)->name),
        ];
    }

}
