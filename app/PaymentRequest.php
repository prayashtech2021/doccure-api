<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PaymentRequest extends Model
{
    use EncryptedAttribute;

    protected $dates = ['payment_date']; 
    
    protected $fillable = [
        'user_id','reference_id', 'description', 'currency_code','request_type','request_amount','status','action_date','created_by'
    ];
    protected $encryptable = [
        'reference_id','description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getData(){
        return [
            'id' => $this->id,
            'created' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d/m/Y h:i A'),
            'reference_id' => $this->reference_id,
            'currency_code' => $this->currency_code,
            'request_type' => ucfirst(config('custom.payment_request_type')[$this->request_type]),
            'status' => ucwords(config('custom.payment_request_status')[$this->status]),
            'request_amount' => $this->request_amount,
            'user_details' => $this->user()->first()->basicProfile(),
            'action_date' => convertToLocal(Carbon::parse($this->action_date),config('custom.timezone')[251],'d/m/Y'),
            'account_details' => ($this->user->accountDetails()->first())?$this->user->accountDetails()->first()->getData():[],
        ];
    }



    
}
