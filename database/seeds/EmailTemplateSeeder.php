<?php

use Illuminate\Database\Seeder;

use App\EmailTemplate;

class EmailTemplateSeeder extends Seeder
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
                'slug' => 'registration',
                'subject' => 'Doccure Registration',
                'content' => '# Hi {{username}},\r\n\r\nThank you for register with {{config_app_name}} account. <br>\r\nYour Verification code is <h2><b>{{verification_code}}</b></h2>\r\n\r\n<button><a href=\"{{link}}\" target=\"_blank\">Verify Your Email Address</a></button> <br>\r\n\r\nPlease click the above link to verify your email address. <br><br>\r\nRegards,<br>\r\n<span style=\"color:#11cdef;\">{{config_app_name}} Team</span><br>\r\n<small>{{custom_support_phone}} <br> {{custom_support_email}}</small>',
            ],
            2 => [
                'slug' => 'reset_password',
                'subject' => 'User Account Password Reset',
                'content' => '# Hi {{username}},\r\n\r\nPlease click the following link to reset the password.\r\n \r\n<button><a href=\"{{link}}\" target=\"_blank\">Reset Password</a></button> <br>\r\n\r\nRegards,<br>\r\n<span style=\"color:#11cdef;\">{{config_app_name}} Team</span><br>\r\n<small>{{custom_support_phone}} <br> {{custom_support_email}}</small>',
            ],
            
        ];
            foreach ($datas as $id => $data) {
                $row = EmailTemplate::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
