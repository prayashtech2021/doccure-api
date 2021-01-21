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
use Illuminate\Support\Carbon;


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

    protected $appends = ['pid','did','age','accountstatus','membersince','gendername','doctorfees'];

    public function accessToken(){
        return $this->hasMany('App\OauthAccessToken');
    }

    public static function userAddress($id){
        return Address::whereNull('name')->where('user_id',$id)->first();
    }

    public static function doctorClinicInfo($id){
        return Address::whereNotNull('name')->where('user_id',$id)->first();
    }
    public static function doctorClinicImage($id){
        return AddressImage::where('user_id',$id)->first();
    }

    public function addresses(){
        return $this->hasMany(Address::class);
    }

    public function doctorSpecialization() { 
       // return $this->hasOne('App\UserSpeciality','user_id');
        return $this->belongsToMany('App\Speciality', 'user_speciality');
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
           'speciality' => $this->doctorSpecialization(),
       ];
    }

    public function getPidAttribute() { 
        return 'PT00'.$this->id; 
    } // patient_ID

    public function getDidAttribute() { 
        return 'D00'.$this->id; 
    } // Doctor_ID

    public function getAgeAttribute(){
        return Carbon::parse($this->dob)->age;
    }

    public function getAccountStatusAttribute(){
        if($this->deleted_at == NULL){
            return true;
        }else{
            return false;
        }
    }

    public function getMemberSinceAttribute(){
        return date('d M Y H:s A', strtotime($this->created_at));
    }

    public function getGenderNameAttribute(){
        if($this->gender == 1){
            return 'Male';
        }elseif($this->gender == 2){
            return 'Female';
        }else{
            return '-';
        }
    }

    public function getDoctorFeesAttribute(){
        if($this->price_type == 1){ 
            return 'Free';
        }else{
            return $this->amount;
        }
    }


}
