<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

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
    use EncryptedAttribute;

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
        'biography','price_type','amount','verification_code','is_verified','currency_code','created_by','time_zone_id','time_zone',
   ];
    protected $encryptable = [
        'first_name','last_name','biography'
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
    
    public static function boot() {
        parent::boot();
        self::deleting(function($user) { // before delete() method call this
           
            $user->accountDetails()->each(function($account) {
                $account->delete(); // <-- direct deletion
            }); 
            $user->homeAddress()->each(function($home) {
                $home->delete(); // <-- direct deletion
            });
            $user->clinicAddress()->each(function($clinic) {
                $clinic->delete(); // <-- direct deletion
            });
            $user->addressImage()->each(function($image) {
                $image->delete(); // <-- direct deletion
            });
            $user->appointments()->each(function($appointment) {
                $appointment->delete(); // <-- direct deletion
                AppointmentLog::where('appointment_id',$appointment->id)->delete();
                CallLog::where('appointment_id',$appointment->id)->delete();
                Prescription::where('appointment_id',$appointment->id)->delete();
                MedicalRecord::where('appointment_id',$appointment->id)->delete();
                Payment::where('appointment_id',$appointment->id)->delete();
                Review::where('appointment_id',$appointment->id)->delete();

            });
            $user->providerAppointments()->each(function($provider_appointment) {
                $provider_appointment->delete(); // <-- direct deletion
                AppointmentLog::where('appointment_id',$provider_appointment->id)->delete();
                CallLog::where('appointment_id',$provider_appointment->id)->delete();
                Prescription::where('appointment_id',$appointment->id)->delete();
                MedicalRecord::where('appointment_id',$appointment->id)->delete();
                Payment::where('appointment_id',$appointment->id)->delete();
                Review::where('appointment_id',$appointment->id)->delete();

            });
            
            if($user->doctorService()){
             $user->doctorService()->each(function($post) {
                $post->delete(); // <-- raise another deleting event on Post to delete comments
             });
            }
            $user->doctorAwards()->each(function($award) {
                $award->delete(); // <-- raise another deleting event on Post to delete comments
             });
             
            $user->chats()->each(function($chat) {
                $chat->delete(); // <-- raise another deleting event on Post to delete comments
            });
            $user->chat_inbox()->each(function($chat_inbox) {
                $chat_inbox->delete(); // <-- raise another deleting event on Post to delete comments
            });
            $user->doctorEducation()->each(function($edu) {
                $edu->delete(); // <-- raise another deleting event on Post to delete comments
            });
            $user->doctorExperience()->each(function($exp) {
                $exp->delete(); // <-- raise another deleting event on Post to delete comments
            });
            $user->doctorMembership()->each(function($member) {
                $member->delete(); // <-- raise another deleting event on Post to delete comments
            });
            
            PostComment::where('user_id',$user->id)->delete();
            
            $user->doctorRegistration()->each(function($reg) {
                $reg->delete(); // <-- raise another deleting event on Post to delete comments
            });

            ScheduleTiming::where('provider_id',$user->id)->delete();
            Signature::where('user_id',$user->id)->delete();
            SocialMedia::where('provider_id',$user->id)->delete();
            //$user->userFav()->detach($user->id);
            $user->doctorSpecialization()->each(function($spl) {
                $spl->delete(); // <-- raise another deleting event on Post to delete comments
            });
            $user->paymentRequest()->each(function($req) {
                $req->delete(); // <-- raise another deleting event on Post to delete comments
            });
            
             // do the rest of the cleanup...
        });
    }
    
    public function doctorProfile($id = NULL,$mobile = NULL){
        if(isset($mobile) && ($mobile==1)){
            $permanentAddress = ($this->getPermanentAddressAttribute()) ? $this->getPermanentAddressAttribute($mobile) : (object)[];
            $officeAddress = ($this->getOfficeAddressAttribute()) ? $this->getOfficeAddressAttribute() : (object)[];
            $last_message = ($id)? $this->lastChat($id) : (object)[];
        }else{
            $permanentAddress = $this->getPermanentAddressAttribute();
            $officeAddress = $this->getOfficeAddressAttribute();
            $last_message = ($id)? $this->lastChat($id) : '';
        } 
        if (isset($id) && $id!=0) {
            $fav = $this->userHasFav($id);
        }else{
            $fav = 0;
        }
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
            'permanent_address' => $permanentAddress,
            'office_address' => $officeAddress,
            'member_since' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'M-d-Y H:s A'),
            'accountstatus' => $this->getAccountStatusAttribute(),
            'doctor_earned' => ($this->providerPayment())?$this->providerPayment()->sum(DB::raw('total_amount-(transaction_charge + tax_amount)')):'',
            'doctorRating' => ($this->avgRating())? $this->avgRating() : 0,
            'feedback_count' => ($this->doctorRatings())? $this->doctorRatings()->where('user_id',$this->id)->count() : 0,
            'total_unread_chat' => ($id)? $this->unreadChat($id) : '',
            'last_message' => $last_message,
            'status' => $this->status,
            'online_status' => $this->onlineStatus(),
            'education' => $this->doctorEducation()->get(),
            'favourite' => $fav,
        ];
    }

    public function patientProfile($id = NULL,$mobile = NULL){
        if(isset($mobile) && ($mobile==1)){
            $address = ($this->getPermanentAddressAttribute()) ? $this->getPermanentAddressAttribute($mobile) : (object)[];
        }else{
            $address = $this->getPermanentAddressAttribute();
        }
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
            'permanent_address' => $address,
            'member_since' => convertToLocal(Carbon::parse($this->created_at),config('custom.timezone')[251],'M-d-Y H:s A'),
            'accountstatus' => $this->getAccountStatusAttribute(),
            'last_visit' => ($this->appointments()->first())?$this->appointments()->orderby('id','desc')->first()->appointment_date:'',
            'patient_paid' => ($this->payment())?$this->payment()->sum('total_amount'):'',
            'total_unread_chat' => ($id)? $this->unreadChat($id) : '',
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

    public function basicProfile($id = NULL,$mobile=NULL){
        if(isset($mobile) && ($mobile==1)){
            $address = ($this->getPermanentAddressAttribute()) ? $this->getPermanentAddressAttribute($mobile) : (object)[];
        }else{
            $address = $this->getPermanentAddressAttribute();
        }
        if (isset($id) && $id!=0) {
            $fav = $this->userHasFav($id);
        }else{
            $fav = 0;
        }
        return [
           'id' => $this->id,
           'name' => trim($this->first_name . ' '. $this->last_name),
           'profile_image' => getUserProfileImage($this->id),
           'mobile_number' => $this->mobile_number,
           'email' => $this->email,
           'address' => $address,  
           'doctorSpecialization' => $this->getProviderSpecialityAttribute(),
           'doctorRating' => ($this->avgRating())? $this->avgRating() : 0,
           'feedback_count' => ($this->doctorRatings())? $this->doctorRatings()->where('user_id',$this->id)->count() : 0,
           'status' => $this->status,
           'role' => $this->roles()->first()->name,
           'education' => $this->doctorEducation()->get(),     
           'favourite' => $fav,
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

    public function getPermanentAddressAttribute($mobile = NULL){
        if(isset($mobile) && ($mobile==1)){
            $address = Address::with('country','state','city')->whereNull('name')->where('user_id',$this->id)->first();
            return [
                "id" => $address->id,
                "user_id" => $address->user_id,
                "name" => $address->name,
                "line_1" => $address->line_1,
                "line_2" => $address->line_2,
                "country_id" => $address->country_id,
                "state_id" => $address->state_id,
                "city_id" => $address->city_id,
                "postal_code" => $address->postal_code,
                "country" => ($address->country)? [
                    "id" => $address->country->id,
                    "name" => $address->country->name,
                ] : (object)[],
                "state" => ($address->state)? [
                    "id" => $address->state->id,
                    "name"=> $address->state->name
                ] : (object)[],
                "city" => ($address->city)? [
                    "id" => $address->city->id,
                    "name"=> $address->city->name
                ] : (object)[], 
            ];
        }else{
            return Address::with('country','state','city')->whereNull('name')->where('user_id',$this->id)->first();
        }
    }
    
    public function getOfficeAddressAttribute(){
        return  Address::with('country','state','city','addressImage')->whereNotNull('name')->where('user_id',$this->id)->first();
    }

    public function addressImage() { 
        return $this->hasMany('App\AddressImage', 'user_id');
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

    public function unreadChat($id){
        return Chat::where('sender_id',$this->id)->where('recipient_id',$id)->where('read_status',0)->count();
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

    public function timezone(){
        return $this->belongsTo(TimeZone::class, 'time_zone_id', 'id');
    }
}
