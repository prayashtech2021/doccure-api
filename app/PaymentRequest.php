<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PaymentRequest extends Model
{
    protected $dates = ['payment_date']; 
    
    protected $fillable = [
        'user_id','reference_id', 'description', 'currency_code','request_type','request_amount','status','action_date','created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getData(){
        return [
            'id' => $this->id,
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'reference_id' => $this->reference_id,
            'currency_code' => $this->currency_code,
            'request_type' => ucfirst(config('custom.payment_request_type')[$this->request_type]),
            'status' => ucwords(config('custom.payment_request_status')[$this->status]),
            'request_amount' => $this->request_amount,
            'user_details' => $this->user()->first()->basicProfile(),
            'action_date' => convertToLocal(Carbon::parse($this->action_date),'','d/m/Y'),
        ];
    }


    
}
