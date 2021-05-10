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
                'value' => '9942576886',
                'created_by' => 1,
            ],
            8 => [
                'slug' => 'general_settings',
                'keyword' => 'support_email',
                'value' => 'doccure@gmail.com',
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
                'value' => 'smtp.googlemail.com',
                'created_by' => 1,
            ],
            12 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_port',
                'value' => '465',
                'created_by' => 1,
            ],
            13 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_user',
                'value' => 'seo@dreamguys.co.in',
                'created_by' => 1,
            ],
            14 => [
                'slug' => 'smtp_settings',
                'keyword' => 'smtp_password',
                'value' => '@Dreams@SEO',
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
                'value' => 'pk_test_51I7I3VJU1C4Pq9PuhPtMFsBPQ9BVez0j9fxJIVtniujXDQjKfc7YEof1TAM0ACdaw6Y7T7DlmOQUTOQUBUwwkFA600EMj5SEjx',
                'created_by' => 1,
            ],
            26 => [
                'slug' => 'payment_gateway',
                'keyword' => 'stripe_sandbox_rest_key',
                'value' => 'sk_test_51I7I3VJU1C4Pq9PuDaOJuo6UWDIlc4qVCpEZSaXdl9rFUTLLKN1qYOZgUXhoa7sVygh23pTzDmKVPwCEMdsmw8wn00oq87z4vK',
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
                'value' => '47119584',
                'created_by' => 1,
            ],
            36 => [
                'slug' => 'tokbox',
                'keyword' => 'tokbox_session_id',
                'value' => '2_MX40NzExOTU4NH5-MTYxMjk2NTI3MDc1OX42YVlZeURoejhEQTZsWVd5RHFVUk9jSXp-fg',
                'created_by' => 1,
            ],
            37 => [
                'slug' => 'tokbox',
                'keyword' => 'tokbox_token_id',
                'value' => 'cGFydG5lcl9pZD00NzExOTU4NCZzaWc9ODM5ZjdiNTVmYjU0ZWViNDY2MzI1ZThhNjdiOTJkOWM1Nzg5ODJiYTpzZXNzaW9uX2lkPTJfTVg0ME56RXhPVFU0Tkg1LU1UWXhNamsyTlRJM01EYzFPWDQyWVZsWmVVUm9lamhFUVRac1dWZDVSSEZWVWs5alNYcC1mZyZjcmVhdGVfdGltZT0xNjEyOTY1MzA1Jm5vbmNlPTAuNzEyNDU0NzIwNDIyODA2OSZyb2xlPXB1Ymxpc2hlciZleHBpcmVfdGltZT0xNjE1NTU3MzA0JmluaXRpYWxfbGF5b3V0X2NsYXNzX2xpc3Q9',
                'created_by' => 1,
            ],
            38 => [
                'slug' => 'general_settings',
                'keyword' => 'website_content',
                'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'created_by' => 1,
            ],
            39 => [
                'slug' => 'privacy_policy',
                'keyword' => 'privacy_policy',
                'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'created_by' => 1,
            ],
            40 => [
                'slug' => 'terms_and_condition',
                'keyword' => 'terms_and_condition',
                'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'created_by' => 1,
            ],
            41 => [
                'slug' => 'smtp_settings',
                'keyword' => 'driver',
                'value' => 'smtp',
                'created_by' => 1,
            ],
            42 => [
                'slug' => 'smtp_settings',
                'keyword' => 'encryption',
                'value' => 'ssl',
                'created_by' => 1,
            ],
            43 => [
                'slug' => 'smtp_settings',
                'keyword' => 'username',
                'value' => 'Doccure',
                'created_by' => 1,
            ],
            44 => [
                'slug' => 'push_notification',
                'keyword' => 'firebase_api_key',
                'value' => '',
                'created_by' => 1,
            ],
            45 => [
                'slug' => 'push_notification',
                'keyword' => 'apns_pem_file',
                'value' => '',
                'created_by' => 1,
            ],
            46 => [
                'slug' => 'push_notification',
                'keyword' => 'apns_password',
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
