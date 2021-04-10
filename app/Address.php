<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;
    use EncryptedAttribute;

    protected $fillable = [
        'user_id', 'name', 'line_1', 'line_2','country_id', 'state_id','city_id','postal_code','created_by',
    ];
    protected $encryptable = [
        'name','line_1','line_2'
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

    

}
