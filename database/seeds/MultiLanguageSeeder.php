<?php

use Illuminate\Database\Seeder;

use App\MultiLanguage;

class MultiLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            // for menu page
			1 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'dashboard',
                'value' => 'dashboard',
                'created_by' => 1,
            ],2 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'appointments',
                'value' => 'appointments',
                'created_by' => 1,
            ],3 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'specialization',
                'value' => 'specialization',
                'created_by' => 1,
            ],4 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'doctors',
                'value' => 'doctors',
                'created_by' => 1,
            ],5 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'patients',
                'value' => 'patients',
                'created_by' => 1,
            ],6 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'payment_requests',
                'value' => 'payment requests',
                'created_by' => 1,
            ],7 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'settings',
                'value' => 'settings',
                'created_by' => 1,
            ],8 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'email_template',
                'value' => 'email template',
                'created_by' => 1,
            ],9 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'cms',
                'value' => 'cms',
                'created_by' => 1,
            ],10 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'language',
                'value' => 'language',
                'created_by' => 1,
            ],11 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'my_profile',
                'value' => 'my profile',
                'created_by' => 1,
            ],12 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'change_password',
                'value' => 'change password',
                'created_by' => 1,
            ],13 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'logout',
                'value' => 'logout',
                'created_by' => 1,
            ],14 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'my_patients',
                'value' => 'my patients',
                'created_by' => 1,
            ],15 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'schedule_timings',
                'value' => 'schedule timings',
                'created_by' => 1,
            ],16 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'calendar',
                'value' => 'calendar',
                'created_by' => 1,
            ],17 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'invoice',
                'value' => 'invoice',
                'created_by' => 1,
            ],18 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'accounts',
                'value' => 'accounts',
                'created_by' => 1,
            ],19 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'reviews',
                'value' => 'reviews',
                'created_by' => 1,
            ],20 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'chat',
                'value' => 'chat',
                'created_by' => 1,
            ],21 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'social_media',
                'value' => 'social media',
                'created_by' => 1,
            ],22 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'patient_search',
                'value' => 'patient search',
                'created_by' => 1,
            ],23 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'favourite_doctor',
                'value' => 'favourite doctor',
                'created_by' => 1,
            ],24 => [
                'page_master_id' => 1,
                'language_id' => 1,
                'keyword' => 'doctor_search',
                'value' => 'doctor search',
                'created_by' => 1,
            ],
            // for menu page end
        ];
            foreach ($datas as $id => $data) {
                $row = MultiLanguage::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
