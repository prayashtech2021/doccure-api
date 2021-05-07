<?php

use Illuminate\Database\Seeder;

use App\PageMaster;

class PageMasterSeeder extends Seeder
{ 
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
			1 => [
                'name' => 'menu',
            ],
            2 => [
                'name' => 'landing_page',
            ],
            3 => [
                'name' => 'doctor_search',
            ],
            4 => [
                'name' => 'doctor_preview',
            ],
            5 => [
                'name' => 'login',
            ],
            6 => [
                'name' => 'register',
            ],
            7 => [
                'name' => 'forgot_password',
            ],
            8 => [
                'name' => 'header',
            ],
            9 => [
                'name' => 'footer',
            ],
            10 => [
                'name' => 'patient_dashboard',
            ],
            11 => [
                'name' => 'email_verification'
            ],
            12 => [
                'name' => 'appointments'
            ],
            13 => [
                'name' => 'calender'
            ],
            14 => [
                'name' => 'invoice'
            ],
            15 => [
                'name' => 'invoice_view'
            ],
            16 => [
                'name' => 'chat'
            ],
            17 => [
                'name' => 'favourites'
            ],
            18 => [
                'name' => 'Accounts'
            ],
            19 => [
                'name' => 'profile_setting'
            ],
            20 => [
                'name' => 'change_password'
            ],
            21 => [
                'name' => 'notifications'
            ],
            22 => [
                'name' => 'booking'
            ],
            23 => [
                'name' => 'checkout'
            ],
            24 => [
                'name' => 'booking_success'
            ],
            25 => [
                'name' => 'get_direction'
            ],
            26 => [
                'name' => 'doctor_dashboard'
            ],
            27 => [
                'name' => 'my_patients'
            ],
            28 => [
                'name' => 'patient_search'
            ],
            29 => [
                'name' => 'schedule_timing'
            ],
            30 => [
                'name' => 'reviews'
            ],
            31 => [
                'name' => 'social_media'
            ],
            32 => [
                'name' => 'blogs'
            ],
            33 => [
                'name' => 'blog_list'
            ],
            34 => [
                'name' => 'post'
            ],
            35 => [
                'name' => 'prescription'
            ],
            36 => [
                'name' => 'medical_record'
            ],
            37 => [
                'name' => 'call'
            ],
            38 => [
                'name' => 'appointmentspage',
                'type' => 1,
            ],
            39 => [
                'name' => 'callpage',
                'type' => 1,
            ],
            40 => [
                'name' => 'changepasswordpage',
                'type' => 1,
            ],
            41 => [
                'name' => 'chatpage',
                'type' => 1,
            ],
            42 => [
                'name' => 'checkoutpage',
                'type' => 1,
            ],
            43 => [
                'name' => 'commonlyusedtexts',
                'type' => 1,
            ],
            44 => [
                'name' => 'doctordashboardpage',
                'type' => 1,
            ],
            45 => [
                'name' => 'doctorprofilepage',
                'type' => 1,
            ],
            46 => [
                'name' => 'forgotpasswordpage',
                'type' => 1,
            ],
            47 => [
                'name' => 'homepage',
                'type' => 1,
            ],
            48 => [
                'name' => 'login',
                'type' => 1,
            ],
            49 => [
                'name' => 'navigationpage',
                'type' => 1,
            ],
            50 => [
                'name' => 'navigationpagedoctor',
                'type' => 1,
            ],
            51 => [
                'name' => 'navigationpagepatient',
                'type' => 1,
            ],
            52 => [
                'name' => 'patientdashboard',
                'type' => 1,
            ],
            53 => [
                'name' => 'prescriptionpage',
                'type' => 1,
            ],
            54 => [
                'name' => 'profilesettingspage',
                'type' => 1,
            ],
            55 => [
                'name' => 'rate_and_review_page',
                'type' => 1,
            ],
            56 => [
                'name' => 'registerpage',
                'type' => 1,
            ],
            57 => [
                'name' => 'scheduletimingspage',
                'type' => 1,
            ],
            58 => [
                'name' => 'searchdoctorpage',
                'type' => 1,
            ],

        ];
            foreach ($datas as $id => $data) {
                $row = PageMaster::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
