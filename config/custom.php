<?php

return [
    'support_phone' => env('SUPPORT_PHONE', '0422-11223344'),
    'support_email' => env('SUPPORT_EMAIL', 'doccure@gmail.com'),

    'importSecret' => 'OrcaloTaxi1020',

    'appointment_status' =>[
        1=>'new',
        2=>'accepted',
        3=>'completed',
        4=>'refund',
        5=>'refund_approved',
        6=>'cancelled',
        7=>'expired'
    ],
    'appointment_log_message' =>[
        1=>'new appointment created',
        2=>'request accepted',
        3=>'appointment completed',
        4=>'refund request raised for doctor approval',
        5=>'request approved',
        6=>'request cancelled',
        7=>'appointment expired'
    ],
    'empty_working_hours' =>[
        "sunday"=> [],
        "monday"=> [],
        "tuesday"=> [],
        "wednesday"=> [],
        "thursday"=> [],
        "friday"=> [],
        "saturday"=> [],
    ],

    'days' =>[
        1=>"sunday",
        2=>"monday",
        3=>"tuesday",
        4=>"wednesday",
        5=>"thursday",
        6=>"friday",
        7=>"saturday",
    ],
    'payment_request_type' =>[
        1=>"payment",
        2=>"refund",
    ],
    'payment_request_status' =>[
        1=>"new",
        2=>"paid",
        3=>"rejected",
    ],
    'timezone' => [
        251 => 'Asia/Kolkata',
    ]
];