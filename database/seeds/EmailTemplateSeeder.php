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
                'content' => '<p><strong># Hi There,</strong></p><p>&nbsp;</p><p>Thank You for register with {{config_app_name}} account.</p><p>Your Verfication Code is <strong>{{verification_code}}.</strong></p><p><a href="{{link}}" type="button">Click here to verify e-mail address</a></p><p>&nbsp;</p><p>Please&nbsp;click&nbsp;the&nbsp;above&nbsp;link&nbsp;to&nbsp;verify&nbsp;your&nbsp;email&nbsp;address.&nbsp;</p><p>&nbsp;</p><p>Regards,</p><p>{{config_app_name}} Team,</p><p>{{custom_support_phone}},</p><p>{{custom_support_email}}.</p>',
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
