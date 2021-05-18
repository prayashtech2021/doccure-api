<?php

namespace App\Stripe;
use Illuminate\Support\Facades\Hash;

class StripePayment {

    public function __construct() {
        \Stripe\Stripe::setApiKey(config('payment.stripe.secret_key'));
    }

  /*  public function create_stripe_customer_and_save_card($user, $setup_intent_id) {
        try {

            $setup_intent = $this->fetch_setup_intent_by_id($setup_intent_id);
            
            if($setup_intent){

                $stripe_customer = \Stripe\Customer::create([
                    'payment_method' => $setup_intent->payment_method,
                ]);

                $user_stripe = new \App\UserStripeDetails();
                $user_stripe->user_id = $user->id;
                $user_stripe->stripe_cus_id = $stripe_customer->id;
                $user_stripe->is_active = 1;
                $user_stripe->payload = json_encode($stripe_customer);
                $user_stripe->save();


                $PaymentMethod = \Stripe\PaymentMethod::all([
                    'customer' => $stripe_customer,
                    'type' => 'card',
                ]);

                $user_card_id = $this->save_card_details_db($user->id, $user_stripe->id, $PaymentMethod->data[0], 1);

                if ($user_card_id) {
                    return ['status' => '1', 'data' => ['stripe_id' => $user_stripe->id, 'card_id' => $user_card_id], 'message' => 'success'];
                } else {
                    return ['status' => '2', 'data' => '', 'message' => 'card save in db failed'];
                }
            }else{
                return ['status' => '2', 'data' => '', 'message' => 'invalid stripe token given'];
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return ['status' => '0', 'data' => $e->getJsonBody(), 'message' => 'card declined'];
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return ['status' => '0', 'data' => $e->getJsonBody(), 'message' => 'too many requests made to the api too quickly'];
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return ['status' => '0', 'data' => $e->getJsonBody(), 'message' => 'invalid parameters were supplied to stripe\'s api'];
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return ['status' => '0', 'data' => $e->getJsonBody(), 'message' => 'authentication with stripe\'s api failed'];
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return ['status' => '0', 'data' => $e->getJsonBody(), 'message' => 'network communication with stripe failed'];
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return ['status' => '0', 'data' => $e->getJsonBody(), 'message' => 'generic stripe error'];
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return ['status' => '3', 'data' => $e->getMessage(), 'message' => 'non stripe generic error'];
        }
    }

    public function save_card_details_db($user_id, $stripe_id, $payment_method, $is_new_order = 0) {
        try {

            if(!$is_new_order){
                $all_existing_cards = UserCardDetails::where('user_id',$user_id)
                    ->where('stripe_id',$stripe_id);

                if($all_existing_cards->count() > 0){
                    $all_existing_cards->update(['is_default' => 0]);
                }
            }

            $card = $payment_method->card;

            $user_card = UserCardDetails::where('fingerprint', $card->fingerprint)
                    ->where('user_id',$user_id)
                    ->where('stripe_id',$stripe_id)
                    ->first();
            
            if(!$user_card){
                $user_card = new UserCardDetails();
            }
            
            
            $user_card->user_id = $user_id;
            $user_card->stripe_id = $stripe_id;
            $user_card->stripe_card_id = $payment_method->id;
            $user_card->fingerprint = $card->fingerprint;
            $user_card->brand = $card->brand;
            $user_card->last4 = $card->last4;
            $user_card->exp_month = $card->exp_month;
            $user_card->exp_year = $card->exp_year;
            $user_card->payload = json_encode($card);
            $user_card->is_active = 1;
            $user_card->is_default = 1;
            $user_card->save();
            return $user_card->id;
        } catch (\Exception | \Throwable $e) {
            return false;
        }
    }

    public function check_stripe_customer_exist($user_id) {
        try {
            $user_stripe = \App\UserStripeDetails::whereUserId($user_id)->first();
            if ($user_stripe) {
            	Log::info('Payment - Stripe customer exist', ['userId' => $user_id]);
                //$user_stripe_details = $user_stripe->toArray();
                return $user_stripe;
            } else {
            	Log::info('Payment - Stripe customer not exist', ['userId' => $user_id]);
                return null;
            }
        } catch (\Exception $ex) {
        	Log::error('Payment - Stripe customer exist check', ['userId' => $user_id, 'error_message' => $ex->getMessage()]);
            return false;
        }
    }

    */

    public function createSetupIntent(){
        try{
            $stripe_setup_intent = \Stripe\SetupIntent::create();
            return $stripe_setup_intent;
        }catch (\Exception | \Throwable $e) {
            return false;
        }
    }


}
