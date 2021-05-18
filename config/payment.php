<?php

/*
 * payment related configs if you are using other than laravel cashier
 * PapPal,Stripe,Braintree and etc
 * like public key,secret key, api key and etc
 */

return [
    'stripe' => [
        'public_key' => env('STRIPE_KEY'),
        'secret_key' => env('STRIPE_SECRET'),
    //    'client_id' => env('STRIPE_CLIENT_ID'),
    ],
    
];


