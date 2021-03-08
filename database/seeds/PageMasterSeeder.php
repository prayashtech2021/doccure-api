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
