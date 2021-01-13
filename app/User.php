<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Laravel\Cashier\Billable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;

class User extends Authenticatable implements Wallet, WalletFloat
{
    use SoftDeletes;
    use Notifiable;
    use HasRoles;
    use HasApiTokens;
    use Billable;

    use HasWalletFloat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'first_name', 'last_name', 'email', 'password', 'mobile_number', 'profile_image','country_id','currency_code',
         'verification_code','is_verified','created_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','remember_token',
    ];

   public function accessToken()
    {
        return $this->hasMany('App\OauthAccessToken');
    }
    
    public function specialities() { 
        return $this->belongsToMany('App\Speciality', 'user_speciality'); 
    }

    public function doctorEducation() { 
        return $this->hasMany('App\EducationDetail'); 
    }


}
