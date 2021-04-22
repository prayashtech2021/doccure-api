<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Schema;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (Schema::hasTable('settings')) {
            $data = DB::table('settings')->select('value')->where('slug', 'smtp_settings')->get();
            if ($data) {
                $emailServices = DB::table('settings')->select('value')->where('slug', 'smtp_settings')->pluck('value');

                if ($emailServices) {
                    $config = array(
                        'driver' => $emailServices[4],
                        'host' => $emailServices[0],
                        'port' => $emailServices[1],
                        'from' => array('address' => $emailServices[2], 'name' => $emailServices[6]),
                        'encryption' => $emailServices[5],
                        'username' => $emailServices[2],
                        'password' => $emailServices[3],
                        'sendmail' => '/usr/sbin/sendmail -bs',
                        'pretend' => false,
                    );
                    Config::set('mail', $config);
                }
            }
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
