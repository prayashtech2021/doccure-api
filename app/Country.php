<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    //
    public static function getCurrentCode($country_id){
        $get_currency_code = Country::whereId($country_id)->get()->pluck('currency');
        if($get_currency_code){
            return $get_currency_code[0];
        }
    }
}
