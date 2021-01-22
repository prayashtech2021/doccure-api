<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $dates = ['payment_date']; 
    
    protected $fillable = [
        'code', 'description', 'currency_code','user_id','request_type','request_amount','status','action_date','created_by'
    ];

    public function user()
    {
        return $this->belongsTo(App\User::class);
    }



    
}
