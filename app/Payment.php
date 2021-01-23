<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Payment extends Model
{
    public function getData(){
        return [
            'id' => $this->id,
            'reference' => $this->invoice_no,
            'created' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'type' => config('appointments.type')[$this->payment_type],
            'total_amount' => $this->total_amount,
            'currency_code' => $this->currency_code,
            'txn_id' => $this->txn_id??'NA',
            'tax' => $this->tax??'NA',
            'tax_amount' => $this->tax_amount??'NA',
            'transaction_charge' => $this->transaction_charge??'NA',
        ];
    }

    public function appointment(){
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }
}
