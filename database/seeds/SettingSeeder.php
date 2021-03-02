<?php

use Illuminate\Database\Seeder;

use App\Setting;

class SettingSeeder extends Seeder
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
                'slug' => 'general_settings',
                'keyword' => 'app_name',
                'value' => 'Doccure',
                'created_by' => 1,
            ],
            2 => [
                'slug' => 'general_settings',
                'keyword' => 'tax',
                'value' => '10',
                'created_by' => 1,
            ],
            3 => [
                'slug' => 'general_settings',
                'keyword' => 'transaction_charge',
                'value' => '2',
                'created_by' => 1,
            ],
            4 => [
                'slug' => 'general_settings',
                'keyword' => 'company_logo',
                'value' => '',
                'created_by' => 1,
            ],
            5 => [
                'slug' => 'general_settings',
                'keyword' => 'footer_logo',
                'value' => '',
                'created_by' => 1,
            ],
            6 => [
                'slug' => 'general_settings',
                'keyword' => 'favicon',
                'value' => '',
                'created_by' => 1,
            ],
            7 => [
                'slug' => 'general_settings',
                'keyword' => 'support_number',
                'value' => '',
                'created_by' => 1,
            ],
            8 => [
                'slug' => 'general_settings',
                'keyword' => 'support_email',
                'value' => '',
                'created_by' => 1,
            ],
            9 => [
                'slug' => 'general_settings',
                'keyword' => 'support_address',
                'value' => 'Dreamguys Tech, cheran ma nagar, Coimbatore.',
                'created_by' => 1,
            ],
            10 => [
                'slug' => 'general_settings',
                'keyword' => 'support_zipcode',
                'value' => '641006',
                'created_by' => 1,
            ],
            11 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_host',
                'value' => '',
                'created_by' => 1,
            ],
            12 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_port',
                'value' => '',
                'created_by' => 1,
            ],
            13 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_user',
                'value' => '',
                'created_by' => 1,
            ],
            14 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_password',
                'value' => '',
                'created_by' => 1,
            ],
            15 => [
                'slug' => 'social_link',
                'keyword' => 'facebook',
                'value' => '',
                'created_by' => 1,
            ],
            16 => [
                'slug' => 'social_link',
                'keyword' => 'twitter',
                'value' => '',
                'created_by' => 1,
            ],
            17 => [
                'slug' => 'social_link',
                'keyword' => 'google_plus',
                'value' => '',
                'created_by' => 1,
            ],
            18 => [
                'slug' => 'social_link',
                'keyword' => 'linked_in',
                'value' => '',
                'created_by' => 1,
            ],
            19 => [
                'slug' => 'social_link',
                'keyword' => 'instagram',
                'value' => '',
                'created_by' => 1,
            ],
            20 => [
                'slug' => 'email',
                'keyword' => 'email_from_address',
                'value' => '',
                'created_by' => 1,
            ],
            21 => [
                'slug' => 'email',
                'keyword' => 'email_title',
                'value' => '',
                'created_by' => 1,
            ],
            22 => [
                'slug' => 'payment_gateway',
                'keyword' => 'paypal_sandbox_email',
                'value' => '',
                'created_by' => 1,
            ],
            23 => [
                'slug' => 'payment_gateway',
                'keyword' => 'paypal_live_email',
                'value' => '',
                'created_by' => 1,
            ],
            24 => [
                'slug' => 'payment_gateway',
                'keyword' => 'paypal_sanbox_live',
                'value' => '',
                'created_by' => 1,
            ],
            25 => [
                'slug' => 'payment_gateway',
                'keyword' => 'stripe_sandbox_api_key',
                'value' => '',
                'created_by' => 1,
            ],
            26 => [
                'slug' => 'payment_gateway',
                'keyword' => 'stripe_sandbox_rest_key',
                'value' => '',
                'created_by' => 1,
            ],
            27 => [
                'slug' => 'payment_gateway',
                'keyword' => 'stripe_live_api_key',
                'value' => '',
                'created_by' => 1,
            ],
            28 => [
                'slug' => 'payment_gateway',
                'keyword' => 'stripe_live_rest_key',
                'value' => '',
                'created_by' => 1,
            ],
            29 => [
                'slug' => 'payment_gateway',
                'keyword' => 'stripe_sandbox_live',
                'value' => '',
                'created_by' => 1,
            ],
            30 => [
                'slug' => 'payment_gateway',
                'keyword' => 'razorpay_sandbox_key_id',
                'value' => '',
                'created_by' => 1,
            ],
            31 => [
                'slug' => 'payment_gateway',
                'keyword' => 'razorpay_sandbox_key_secret',
                'value' => '',
                'created_by' => 1,
            ],
            32 => [
                'slug' => 'payment_gateway',
                'keyword' => 'razorpay_live_key_id',
                'value' => '',
                'created_by' => 1,
            ],
            33 => [
                'slug' => 'payment_gateway',
                'keyword' => 'razorpay_live_key_secret',
                'value' => '',
                'created_by' => 1,
            ],
            34 => [
                'slug' => 'payment_gateway',
                'keyword' => 'razorpay_is_sandbox_live',
                'value' => '',
                'created_by' => 1,
            ],
            35 => [
                'slug' => 'tokbox',
                'keyword' => 'tokbox_api_key',
                'value' => '',
                'created_by' => 1,
            ],
            36 => [
                'slug' => 'tokbox',
                'keyword' => 'tokbox_api_secret',
                'value' => '',
                'created_by' => 1,
            ],
            
		];
		foreach ($datas as $id => $data) {
			$row = Setting::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
        }
    }
}
