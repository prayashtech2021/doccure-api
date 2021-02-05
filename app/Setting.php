<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'slug', 'keyword', 'value', 'created_by',
   ];
    
    public function getAmount(){
        $getSettings = $this->where('slug','general_settings')->get();

        $trans_percent = $getSettings->first(function($item) {return $item->keyword == 'transaction_charge';})->value;
        $trans_percent = (!empty($trans_percent) && $trans_percent>0)?$trans_percent:0;
        $tax_percent = $getSettings->first(function($item) {return $item->keyword == 'tax';})->value;
        $tax_percent = (!empty($tax_percent) && $tax_percent>0)?$tax_percent:0;
        $commission_percent = $getSettings->first(function($item) {return $item->keyword == 'commission';})->value;
        $commission_percent = (!empty($commission_percent) && $commission_percent>0)?$commission_percent:0;

        $data =['trans_percent'=>$trans_percent,
                'tax_percent'=>$tax_percent,        
                'commission_percent'=>$commission_percent       
        ];
        return $data;
    }

    public function getData(){
        //$getSettings = $this->where('slug','general_settings')->get();
        
        return [
            $this->keyword => $this->value,
        ];
    }
}
