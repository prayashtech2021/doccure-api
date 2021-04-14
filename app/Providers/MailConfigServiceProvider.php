<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Setting;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $emailServices = Setting::select('keyword','value')->where('slug','smtp_settings')->get();

        if ($emailServices) {
            $config = array(
                'driver'     => 'smtp',
                'host'       => $emailServices[0]->smtp_host,
                'port'       => $emailServices[0]->smtp_port,
                'username'   => $emailServices[0]->smtp_user,
                'password'   => $emailServices[0]->smtp_password,
                'encryption' => 'ssl',
                'from'       => array('address' => $emailServices[0]->smtp_user, 'name' => $emailServices[0]->name),
                'sendmail'   => '/usr/sbin/sendmail -bs',
                'pretend'    => false,
            );

            Config::set('mail', $config);
        }
    }
}
