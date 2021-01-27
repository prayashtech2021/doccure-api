<?php

return [
    'support_phone' => env('SUPPORT_PHONE', '0422-11223344'),
    'support_email' => env('SUPPORT_EMAIL', 'support@taxiapp.com'),

    'importSecret' => 'OrcaloTaxi1020',

    'appointment_status' =>[
        1=>'new',
        2=>'waiting for approval',
        3=>'approved',
        4=>'cancelled',
        5=>'refund',
        6=>'expired'
    ],
    'appointment_log_message' =>[
        1=>'new appointment created',
        2=>'request raised for patient approval',
        3=>'request approved',
        4=>'request cancelled',
        5=>'refund request raised for doctor approval',
        6=>'appointment expired'
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
];