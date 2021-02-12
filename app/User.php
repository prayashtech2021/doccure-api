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
use DB;

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

    protected $appends = ['pid','did','age','accountstatus','membersince','gendername','doctorfees','userimage',
    'providerspeciality','permanentaddress','officeaddress'];

    public function accessToken(){
        return $this->hasMany('App\OauthAccessToken');
    }

    public function doctorProfile(){
        return [
            'id' => $this->id,
            'did' => $this->getDidAttribute(),
            'name' => trim($this->first_name . ' '. $this->last_name),
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'gendername' => $this->getGenderNameAttribute(),
            'profile_image' => getUserProfileImage($this->id),
            'dob' => $this->dob,
            'age' => Carbon::parse($this->dob)->age,
            'blood_group' => $this->blood_group,
            'biography' => $this->biography,
            'fees' =>  ($this->price_type == 1)? 'Free' : $this->amount,
            'currency_code' => $this->currency_code,
            'time_zone_id' => $this->time_zone_id,
            'language_id' => $this->language_id,
            'service' => $this->doctorService()->get(),
            'providerspeciality' => $this->getProviderSpecialityAttribute(),
            'permanent_address' => $this->getPermanentAddressAttribute(),
            'office_address' => $this->getOfficeAddressAttribute(),
            'member_since' => date('d M Y H:s A', strtotime($this->created_at)),
            'accountstatus' => $this->getAccountStatusAttribute(),
            'doctor_earned' => ($this->providerPayment())?$this->providerPayment()->sum(DB::raw('total_amount-(transaction_charge + tax_amount)')):'',
        ];
    }

    public function patientProfile(){
        return [
            'id' => $this->id,
            'pid' => $this->getPidAttribute(),
            'name' => trim($this->first_name . ' '. $this->last_name),
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'gendername' => $this->getGenderNameAttribute(),
            'profile_image' => getUserProfileImage($this->id),
            'dob' => $this->dob,
            'age' => Carbon::parse($this->dob)->age,
            'blood_group' => $this->blood_group,
            'time_zone_id' => $this->time_zone_id,
            'language_id' => $this->language_id,
            'currency_code' => $this->currency_code,
            'permanent_address' => $this->getPermanentAddressAttribute(),
            'member_since' => date('d M Y H:s A', strtotime($this->created_at)),
            'accountstatus' => $this->getAccountStatusAttribute(),
            'last_visit' => ($this->appointments()->first())?$this->appointments()->orderby('id','desc')->first()->appointment_date:'',
            'patient_paid' => ($this->payment())?$this->payment()->sum('total_amount'):'',
        ];
    }

    public function doctorSpecialization() { 
        return $this->belongsToMany('App\Speciality', 'user_speciality');
    }

    public function doctorService(){
        return $this->hasMany(Service::class);
    }
    
    public function homeAddress(){
        return $this->hasMany(Address::class)->with('country','state','city')->whereNull('name');
    }

    public function clinicAddress(){
        return $this->hasMany(Address::class)->with('country','state','city','addressImage')->whereNotNull('name');
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
    
    public function appointments() { 
        return $this->hasMany('App\Appointment','user_id'); 
    }

    public function userFav(){        
        return $this->belongsToMany('App\User', 'user_favourites', 'user_id', 'favourite_id');
    }

    public function basicProfile(){
       return [
           'id' => $this->id,
           'name' => trim($this->first_name . ' '. $this->last_name),
           'profile_image' => getUserProfileImage($this->id),
           'mobile_number' => $this->mobile_number,
           'email' => $this->email,
           'address' => $this->getPermanentAddressAttribute(),
           'doctorSpecialization' => $this->getProviderSpecialityAttribute(),
       ];
    }

    public function payment()
    {
        return $this->hasManyThrough(Payment::class, Appointment::class);
    }

    public function providerPayment()
    {
        return $this->hasManyThrough(Payment::class, Appointment::class,'doctor_id');
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

    public function getProviderSpecialityAttribute(){
        $speciality = Speciality::whereHas('speciality', function ($a) {
                    $a->where('user_speciality.user_id',$this->id);
                })->get();
        removeMetaColumn($speciality);
        return $speciality;
    }

    public function getPermanentAddressAttribute(){
        return Address::with('country','state','city')->whereNull('name')->where('user_id',$this->id)->first();
    }
    
    public function getOfficeAddressAttribute(){
        return Address::with('country','state','city','addressImage')->whereNotNull('name')->where('user_id',$this->id)->first();
    }

    public function getUserImageAttribute() { 
        return getUserProfileImage($this->id); 
    }

    public function accountDetails() { 
        return $this->hasOne('App\AccountDetail', 'user_id');
    }

    public function paymentRequest() { 
        return $this->hasMany('App\PaymentRequest', 'user_id');
    }
}
