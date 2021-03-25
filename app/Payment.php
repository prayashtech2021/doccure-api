<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Payment extends Model
{
    public function getData()
    {
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
            'from' => $this->appointment->doctor()->first()->basicProfile(),
            'to' => $this->appointment->patient()->first()->basicProfile(),
            'created' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d/m/Y h:i A'),
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
