<?php

return [
    'support_phone' => env('SUPPORT_PHONE', '0422-11223344'),
    'support_email' => env('SUPPORT_EMAIL', 'support@taxiapp.com'),

    'importSecret' => 'OrcaloTaxi1020',

    'appointment_status' =>[
        'new'=>1,
        'approve_request'=>2,
        'approved'=>3,
        'cancelled'=>4,
        'refund_request'=>5,
        'expired'=>6
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
        "satday"=> [],
    ],

    'days' =>[
        1=>"sunday",
        2=>"monday",
        3=>"tuesday",
        4=>"wednesday",
        5=>"thursday",
        6=>"friday",
        7=>"satday",
    ]
];