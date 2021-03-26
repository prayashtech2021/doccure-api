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

    public function doctorProfile($id = NULL){
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
            'member_since' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d M Y H:s A'),
            'accountstatus' => $this->getAccountStatusAttribute(),
            'doctor_earned' => ($this->providerPayment())?$this->providerPayment()->sum(DB::raw('total_amount-(transaction_charge + tax_amount)')):'',
            'doctorRating' => ($this->avgRating())? $this->avgRating() : 0,
            'feedback_count' => ($this->doctorRatings())? $this->doctorRatings()->where('user_id',$this->id)->count() : 0,
            'total_unread_chat' => $this->unreadChat(),
            'last_message' => ($id)? $this->lastChat($id) : '',
            'status' => $this->status,
            'online_status' => $this->onlineStatus(),
        ];
    }

    public function patientProfile($id = NULL){
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
            'member_since' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'d M Y H:s A'),
            'accountstatus' => $this->getAccountStatusAttribute(),
            'last_visit' => ($this->appointments()->first())?$this->appointments()->orderby('id','desc')->first()->appointment_date:'',
            'patient_paid' => ($this->payment())?$this->payment()->sum('total_amount'):'',
            'total_unread_chat' => $this->unreadChat(),
            'last_message' => ($id)? $this->lastChat($id) : '',
            'online_status' => $this->onlineStatus(),
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
    public function providerAppointments() { 
        return $this->hasMany('App\Appointment','doctor_id'); 
    }

    public function userFav(){        
        return $this->belongsToMany('App\User', 'user_favourites', 'user_id', 'favourite_id');
    }
    

    public function userHasFav($consumer_id){
        return $this->whereHas('userFav', function ($a) use($consumer_id) {
            $a->where('user_favourites.favourite_id',$this->id)->where('user_id',$consumer_id);
        })->count();
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
           'doctorRating' => ($this->avgRating())? $this->avgRating() : 0,
           'feedback_count' => ($this->doctorRatings())? $this->doctorRatings()->where('user_id',$this->id)->count() : 0,
           'status' => $this->status,
           'role' => $this->roles()->first()->name,
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
        $list =  UserSpeciality::where('user_id',$this->id);
        $data = collect();
            $list->each(function ($provider) use (&$data) {
                $data->push($provider->getData());
            });
         return $data;
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

    public function doctorRatings(){
        return $this->hasMany("App\Review","user_id");
    }
    public function avgRating(){
        return $this->doctorRatings()->average('rating');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function chat_inbox(){
        return $this->hasMany(Chat::class, 'recipient_id');
    }

    public function unreadChat(){
        return Chat::where('recipient_id',$this->id)->where('read_status',0)->count();
    }

    public function lastChat($id){
        $recipient_id = $this->id;
        $chat = Chat::select('message','file_path','created_at')->where(function($qry) use ($recipient_id,$id){
            $qry->whereRaw("sender_id=".$id." and recipient_id=".$recipient_id)
                ->orWhereRaw("sender_id=".$recipient_id." and recipient_id=".$id);
        });
        $list = $chat->orderBy('id','desc')->first();
        //$count = $chat->where('read_status',1)->count();
        if($list){
        (empty($list->message))? $msg = $list->file_path : $msg = $list->message; 
        $created_at=$list->created_at->diffForHumans();
        }else{$msg =''; $created_at=''; }

        return [
            'message' => $msg,
            'created_at' => $created_at,
           // 'unread' => ($list)? $count : 0,
        ];
    }
    
    public function callLog()
    {
        return $this->hasMany(CallLog::class, 'from');
    }

    public function onlineStatus(){
        $this->last_seen_time;
        $current = Carbon::now();
        $from = Carbon::parse($this->last_seen_time);

        $diff_in_minutes = $current->diffInMinutes($from);
        if($diff_in_minutes > 2){
            return 0;
        }else{
            return 1;
        }
    }
}
