<?php

namespace App;
//use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Payment extends Model
{
   // use EncryptedAttribute;
    // protected $encryptable = [
    //     'invoice_no','txn_id'
    // ];
    public function getData()
    {
        (auth()->user()->time_zone)? $zone = auth()->user()->time_zone : $zone = '';
        if(auth()->user()->hasRole('doctor')){
        $ttemp1 = $this->appointment->appointment_date.' '.$this->appointment->start_time;
        $ttemp2 = $this->appointment->appointment_date.' '.$this->appointment->end_time;
        $userr = User::find($this->appointment->user_id);
        $providerr = User::find($this->appointment->doctor_id);
        $startTime = userToProvider($ttemp1,$userr->time_zone,$providerr->time_zone,'h:i A');
        $endTime = userToProvider($ttemp2,$userr->time_zone,$providerr->time_zone,'h:i A');
        }else{
            $startTime = Carbon::parse($this->appointment->start_time)->format('h:i A');
            $endTime = Carbon::parse($this->appointment->end_time)->format('h:i A');
        }
        return [
            'id' => $this->id,
            'reference' => $this->invoice_no,
            'type' => config('appointments.payment')[$this->payment_type],
            'total_amount' => $this->total_amount,
            'currency_code' => $this->currency_code,
            'txn_id' => $this->txn_id ?? 'NA',
            'tax' => $this->tax ?? 'NA',
            'tax_amount' => $this->tax_amount ?? 'NA',
            'transaction_charge' => $this->transaction_charge ?? 'NA',
            'card_details' => $this->cardDetails(),
            'appointment_type' => ($this->appointment->appointment_type==1)?'Online':'Clinic',
            'from' => $this->appointment->doctor()->first()->basicProfile(),
            'to' => $this->appointment->patient()->first()->basicProfile(),
            'created' => convertToLocal(Carbon::parse($this->created_at),$zone,'d/m/Y h:i A'),
            'appointment_date' => date('d/m/Y',strtotime($this->appointment->appointment_date)),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function cardDetails()
    {
        $card = [];
        $appointment = $this->appointment()->first();
        if ($appointment->payment_type == 1 || $appointment->payment_type == 2) {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($this->txn_id, [
                'expand' => ['payment_method']
            ]);
            $paymentMethod = $paymentIntent->payment_method;
            $card = [
                'id' => $paymentMethod->id,
                'brand' => $paymentMethod->card->brand,
                'last4' => $paymentMethod->card->last4,
                'name' => ucwords($paymentMethod->billing_details->name)
            ];
        }

        return $card;
    }
}
