<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
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
        'first_name', 'last_name', 'email', 'password', 'mobile_number', 'profile_image','country_id','currency_code','gender','dob','blood_group',
        'biography','price_type','amount','verification_code','is_verified','currency_code','created_by',
   ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','remember_token',
    ];

    public function accessToken(){
        return $this->hasMany('App\OauthAccessToken');
    }

    public static function userAddress($id){
        return Address::whereNull('name')->where('user_id',$id)->first();
    }

    public static function doctorClinicInfo($id){
        return Address::whereNotNull('name')->where('user_id',$id)->first();
    }

    public function doctorSpecialization() { 
       // return $this->hasOne('App\UserSpeciality','user_id');
        return $this->belongsToMany('App\Speciality', 'speciality_users');
    }

    public function doctorService(){
        return $this->hasMany(Service::class);
    }

    public function doctorEducation(){
        return $this->hasMany(EducationDetail::class);
    }

    public function doctorExperience() { 
        return $this->hasMany(ExperienceDetail::class);
    }

    public function doctorAwards() { 
        return $this->hasMany(AwardDetail::class);
    }

    public function doctorMembership() { 
        return $this->hasMany(MembershipDetail::class); 
    }

    public function doctorRegistration() { 
        return $this->hasMany(RegistrationDetail::class); 
    }

    public function basicProfile(){
       return [
           'id' => $this->id,
           'name' => trim($this->first_name . ' '. $this->last_name),
       ];
    }
}
