<?php
namespace App\Http\Controllers\Api;

use App\Appointment;
use App\AppointmentLog;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Prescription;
use App\PrescriptionDetail;
use App\ScheduleTiming;
use App\Speciality;
use App\Setting;
use App\Signature;
use App\TimeZone;
use App\User;
use App\CallLog;
use App\UserSpeciality;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Storage;
use \Exception;
use \Throwable;

class AppointmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware(function ($request, Closure $next) {

            if ($request->user()) {
                $user = $request->user();
                if ($user->time_zone_id && $time_zone = TimeZone::find($user->time_zone_id)) {
                    config()->set('app.timezone', $time_zone->name);
                    date_default_timezone_set($time_zone->name);
                }
            }

            return $next($request);
        });
    }

    function list(Request $request,$flag = NULL) {
        try {
            $rules = array(
                'count_per_page' => 'nullable|numeric',
                'page' => 'nullable|numeric',
                'order_by' => 'nullable|in:desc,asc',
                'appointment_status' => 'nullable|numeric',
                'request_type' => 'nullable|numeric|in:1,2',
                'appointment_date' => 'nullable|date_format:d/m/Y',
            );
            $valid = self::customValidation($request, $rules);
            if ($valid) {return $valid;}

            $user = auth()->user();
            updateLastSeen(auth()->user());
            $update = Appointment::whereIn('appointment_status',[1,2])->whereDate('created_at','<',convertToUTC(now()))->update(['appointment_status'=>7]);
            
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $pageNumber = $request->page ? $request->page : 1;

            $order_by = $request->order_by ? $request->order_by : 'desc';
            $list = Appointment::orderBy('created_at', $order_by);

            $status = $request->appointment_status;
            if ($status) {
                switch ($status) {
                    case 1: //upcoming
                        $list = $list->whereIn('appointment_status', [1,2])->whereDate('appointment_date', '>=', convertToUTC(now()));
                        break;
                    case 2: //missed
                        $list = $list->whereIn('appointment_status', [1,2])->whereDate('appointment_date', '<=', convertToUTC(now()));
                        break;
                    case 3: //completed/approved
                        $list = $list->where('appointment_status', 3)->whereDate('appointment_date', '<', convertToUTC(now()));
                        break;
                    default:
                        break;
                }
            }

            $appointment_date = $request->appointment_date ? Carbon::createFromFormat('d/m/Y', $request->appointment_date) : null;
            $list = $list->when($appointment_date, function ($qry) use ($appointment_date) {
                return $qry->whereDate('appointment_date', convertToUTC($appointment_date));
            });

            $data = collect();

            if ($user->hasRole('patient')) {
                $list = $list->whereUserId($user->id);
            } elseif ($user->hasRole('doctor')) {
                $list = $list->whereDoctorId($user->id);
            }

            if (!empty($request->request_type) && $request->request_type > 0) {
                $list = $list->where('request_type', $request->request_type);
            }elseif ($user->hasRole('patient')) {
                // $list = $list->where('appointment_status','<>',3);
            } elseif ($user->hasRole('doctor')) {
                $list = $list->where('appointment_status','<>',4);
            }

            removeMetaColumn($user);
            unset($user->roles);
            $result['user_details'] = $user;
            if (isset($user->accountDetails)) {
                $user->accountDetails;
                removeMetaColumn($user->accountDetails);
            }
            $user->user_balance = [
                'earned' => $user->paymentRequest()->where('payment_requests.status', 2)->sum('request_amount'),
                'balance' => $user->balanceFloat,
                'requested' => $user->paymentRequest()->where('payment_requests.status', 1)->sum('request_amount'),
            ];
            unset($user->wallet);
            if($flag){
                $list->paginate(10)->getCollection()->each(function ($appointment) use (&$data) {
                    $data->push($appointment->basicData());
                });
                return $data;
            }else{
                $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($appointment) use (&$data) {
                    $data->push($appointment->getData());
                });
                $result['list'] = $data;
                return self::send_success_response($result);
            }

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:users,id',
            'appointment_type' => 'required',
            'payment_type' => 'required',
            'appointment_date' => 'required|date_format:d/m/Y',
            'start_time' => 'required|date_format:"H:i:s"',
            'end_time' => 'required|date_format:"H:i:s"|after:start_time',
            'selected_slots' => 'required|numeric',
            'speciality_id' => 'required|exists:user_speciality,id',
        ]);

        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }
        DB::beginTransaction();

        try {

            $user = $request->user();
            $doctor = User::find($request->doctor_id);

            // $booking_hours = Carbon::createFromFormat('H:i:s', $request->start_time)->diffInSeconds(Carbon::createFromFormat('H:i:s', $request->end_time));
            /**
             * Appointment
             */
            $appointment_date = convertToUTC(Carbon::createFromFormat('d/m/Y', $request->appointment_date));
            $chk = Appointment::where(['doctor_id' => $doctor->id, 'appointment_date' => $appointment_date->toDateString(), 'start_time' => $request->start_time, 'end_time' => $request->end_time])->first();
            if ($chk) {
                return self::send_bad_request_response(['message' => 'Appointment already exists', 'error' => 'Appointment already exists']);
            }

            $appointment = new Appointment();
            $last_id = $appointment->latest()->first() ? $appointment->latest()->first()->id : 0;
            $appointment->appointment_reference = generateReference($user->id, $last_id, 'APT');
            $appointment->user_id = 4;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->appointment_type = $request->appointment_type; //1=online, 2=clinic
            $appointment->appointment_date = $appointment_date;
            $appointment->start_time = $request->start_time;
            $appointment->end_time = $request->end_time;
            $appointment->payment_type = $request->payment_type;
            $appointment->request_type = 1;
            $appointment->appointment_status = 1;
            $appointment->save();

            $log = new AppointmentLog;
            $log->appointment_id = $appointment->id;
            $log->request_type = 1;
            $log->description = config('custom.appointment_log_message.1');
            $log->status = $appointment->appointment_status;
            $log->save();

            /**
             * Payment
             */

            $payment = new Payment();
            $last_id = $payment->latest()->first() ? $payment->latest()->first()->id : 0;
            $payment->appointment_id = $appointment->id;
            $payment->invoice_no = generateReference($user->id, $last_id, 'INV');
            $payment->payment_type = $request->payment_type;
            if ($doctor->price_type == 2) {
                $getSettings = new Setting;
                $getSettings = $getSettings->getAmount();

                $speciality = UserSpeciality::find($request->speciality_id);
                $speciality_amt = ($speciality)?$speciality->amount:0;
                $amount = $speciality_amt * $request->selected_slots;
                $transaction_charge = ($amount * ($getSettings['trans_percent'] / 100));
                $total_amount = $amount + $transaction_charge;
                $tax_amount = (round($total_amount) * $getSettings['tax_percent'] / 100);

                $total_amount = $total_amount + $tax_amount;

                $payment->tax = $getSettings['tax_percent'];
                $payment->duration = $speciality->duration;
                $payment->tax_amount = $tax_amount;
                $payment->transaction = $getSettings['trans_percent'];
                $payment->transaction_charge = $transaction_charge;
                $payment->total_amount = $total_amount;
            } else {
                $payment->total_amount = 0;
            }

            $payment->currency_code = $doctor->currency_code ?? config('cashier.currency');
            $payment->save();

            $requested_amount = $payment->total_amount - ($payment->tax_amount + $payment->transaction_charge);
            $doctor->depositFloat($requested_amount);

            if ($request->payment_type == 1 || $request->payment_type == 2) {

                $billable_amount = (int) ($payment->total_amount * 100);

                $payment_options = [
                    'description' => config('app.name') . ' appointment reference #' . $appointment->appointment_reference,
                    'metadata' => [
                        'reference' => $appointment->appointment_reference,
                    ],
                ];

                if ($request->payment_type == 2) {
                    $paymentMethod = $user->findPaymentMethod($request->payment_method);

                    if (!$paymentMethod) {
                        DB::rollBack();
                        return self::send_bad_request_response(['message' => 'Payment processing failed', 'error' => 'Payment processing failed']);
                    }

                    $paymentIntent = $user->charge($billable_amount, $paymentMethod->id, $payment_options);
                    $user->updateDefaultPaymentMethod($request->payment_method);
                } else {
                    if(!$user->hasStripeId()){
                        $user->createAsStripeCustomer();
                    }
                    $user->addPaymentMethod($request->payment_method);
                    $paymentIntent = $user->charge($billable_amount, $request->payment_method, $payment_options);
                }

                if ($paymentIntent) {
                    $charges = collect($paymentIntent->asStripePaymentIntent()->charges->data);
                    $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                    $stripeCharge = $stripe->balanceTransactions->retrieve($charges->first()->balance_transaction);

                    $payment->txn_id = $paymentIntent->id;
                    $payment->transaction_charge = $stripeCharge->fee_details[0]->amount / 100;
                    $payment->save();
                    DB::commit();
                    return self::send_success_response($appointment->getData());

                } else {
                    DB::rollBack();
                    return self::send_bad_request_response(['message' => 'Payment processing failed', 'error' => 'Payment processing failed']);
                }
            }

            DB::commit();

            return self::send_success_response($appointment->getData());

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getsignature($doctor_id){

        $sign =  Signature::select('id','signature_image')->whereUserId($doctor_id)->orderBy('id','desc')->first();
        if($sign && !empty($sign->signature_image) && Storage::exists('images/signature/' . $sign->signature_image)) {
            $img = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/signature/' . $sign->signature_image, now()->addMinutes(5)) : Storage::url('app/public/images/signature/' . $sign->signature_image);
            $data = [
                'id' => $sign->id,
                'signature_image' => $img,
            ];
            return self::send_success_response($data,'Signature fetched Successfully');
        }else{
            return self::send_bad_request_response('No Records Found');
        }
    }

    public function savePrescription(Request $request)
    {

        if ($request->prescription_id) { //edit
            $rules = array(
                'prescription_id' => 'integer|exists:prescriptions,id',
                'appointment_id' => 'required|numeric|exists:appointments,id',
                'user_id' => 'required|numeric|exists:users,id',
                'prescription_detail' => 'required',
            );
        } else {
            $rules = array(
                'appointment_id' => 'required|numeric|exists:appointments,id',
                'user_id' => 'required|numeric|exists:users,id',
                'prescription_detail' => 'required',
            );
        }

        if ($request->signature_id) {
            $rules['signature_id'] = 'required|numeric|exists:signatures,id';
        } else {
            $rules['signature_image'] = 'required|string';
        }

        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        DB::beginTransaction();

        try {
            if (isset($request->prescription_id)) {
                $prescription = Prescription::find($request->prescription_id);
            } else {
                $prescription = new Prescription();
            }
            $prescription->appointment_id = $request->appointment_id;
            $prescription->user_id = $request->user_id;
            $prescription->doctor_id = auth()->user()->id;
            if ($request->signature_id) {
                $prescription->signature_id = $request->signature_id;
            } elseif (!empty($request->signature_image)) {
                if (preg_match('/data:image\/(.+);base64,(.*)/', $request->signature_image, $matchings)) {
                    $imageData = base64_decode($matchings[2]);
                    $extension = $matchings[1];
                    $file_name = date('YmdHis') . rand(100, 999) . '_' . $request->user_id . '.' . $extension;
                    $path = 'images/signature/' . $file_name;
                    Storage::put($path, $imageData);

                    $sign = new Signature();
                    $sign->user_id = auth()->user()->id;
                    $sign->signature_image = $file_name;
                    $sign->created_by = auth()->user()->id;
                    $sign->save();

                    $prescription->signature_id = $sign->id;
                } else {
                    return self::send_bad_request_response('Image Uploading Failed. Please check and try again!');
                }
            }
            $prescription->created_by = auth()->user()->id;
            $prescription->save();

            PrescriptionDetail::where('prescription_id', '=', $prescription->id)->forcedelete();

            $result = json_decode($request->prescription_detail, true);
            foreach ($result as $value) {
                $medicine = new PrescriptionDetail();

                if (!empty($value['drug_name']) || !empty($value['quantity']) || !empty($value['type']) || !empty($value['days']) || !empty($value['time'])) {
                    $medicine->drug_name = $value['drug_name'];
                    $medicine->quantity = $value['quantity'];
                    $medicine->type = $value['type'];
                    $medicine->days = $value['days'];
                    $medicine->time = $value['time'];
                    $medicine->prescription_id = $prescription->id;
                    $medicine->created_by = auth()->user()->id;
                    $medicine->save();
                } else {
                    return self::send_bad_request_response('Some feilds are missing in Prescription Details. Please check and try again!');
                }
            }

            DB::commit();

            return self::send_success_response([], 'Prescription Stored Successfully');

        } catch (Exception | Throwable $exception) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function prescriptionList(Request $request)
    {
        $rules = array(
            'consumer_id' => 'nullable|numeric|exists:users,id',
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            $user = auth()->user();
            if ($user->hasRole('patient')) {
                $user_id = $user->id;
            } else {
                $user_id = $request->consumer_id;
            }
            $list = Prescription::whereUserId($user_id)->orderBy('created_at', $order_by);

            $data = collect();

            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($prescription) use (&$data) {
                $data->push($prescription->getData());
            });

            if($data){
                return self::send_success_response($data,'Prescription Details Fetched Successfully');
            }else{
                return self::send_bad_request_response('No Records Found');
            }

        } catch (Exception | Throwable $exception) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function prescriptionView($pid)
    {
        $list = Prescription::where('id',$pid);
        if($list->get()){
            $data = collect();
            $list->each(function ($prescription) use (&$data) {
                $data->push($prescription->getData());
            });
            return self::send_success_response($data, 'Prescription Details Fetched Successfully');
        }else{
            return self::send_bad_request_response('No Records Found');
        }
    }

    public function prescription_destroy($id)
    {

        return self::customDelete('\App\Prescription', $id);
    }

    public function appointmentStatusUpdate(Request $request)
    {
        $rules = array(
            'appointment_id' => 'required|exists:appointments,id',
            'request_type' => 'nullable|numeric|in:1,2',
            'status' => 'required|numeric|min:2|max:7',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $appointment = Appointment::find($request->appointment_id);
            $appointment->appointment_status = $request->status;
            if(isset($request->request_type)){
            $appointment->request_type = $request->request_type;
            }
            $appointment->save();

            $log = new AppointmentLog;
            $log->appointment_id = $appointment->id;
            $log->description = config('custom.appointment_log_message.' . $request->status . '');
            if(isset($request->request_type)){
            $log->request_type = $request->request_type;
            }
            $log->status = $appointment->appointment_status;
            $log->save();
            if ($request->status == 5 && $appointment->payment->total_amount>0) { // refund approved
                $user = User::find($appointment->user_id);
                $requested_amount = $appointment->payment->total_amount - $appointment->payment->transaction_charge;
                $user->depositFloat($requested_amount);
            }

            return self::send_success_response([], 'Status updated sucessfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function scheduleList(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|numeric|exists:users,id',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $data = collect();
            if (auth()->user()) {
                updateLastSeen(auth()->user());
                $result['provider_details'] = $user = User::find($request->provider_id);
                $list = ScheduleTiming::where('provider_id', $request->provider_id)->get();
                // dd(json_decode($list->working_hours));
                $list->each(function ($schedule_timing) use (&$data) {
                    $data->push($schedule_timing->getData());
                });
                $result['list'] = $data;
                $result['specialities'] = $user->getProviderSpecialityAttribute();
            }
            return self::send_success_response($result, 'Schedule Details Fetched Successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function scheduleCreate(Request $request)
    {
        // dd(json_encode(config('custom.empty_working_hours')));
        $rules = array(
            'provider_id' => 'required|numeric|exists:users,id',
            // 'duration' => 'required|date_format:"H:i:s',
            'appointment_type' => 'required|numeric|in:1,2',
            'day' => 'required|numeric|in:1,2,3,4,5,6,7',
            'working_hours' => 'required|string',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $schedule = ScheduleTiming::where('provider_id', $request->provider_id)->first();
            // $seconds = Carbon::parse('00:00:00')->diffInSeconds(Carbon::parse($request->duration));
            // $seconds = (int) $seconds;
            if ($schedule) { //update
                $schedule = ScheduleTiming::where('provider_id', $request->provider_id)->where('appointment_type', $request->appointment_type)->first();
                if ($schedule) { //update

                    // if ($schedule->duration == $seconds) { //update working hrs
                        $array = json_decode($schedule->working_hours, true);
                        $day_array = $array[config('custom.days.' . $request->day)];
                        $incoming = explode(',', $request->working_hours);
                        foreach($incoming as $item){
                            array_push($day_array, $item);
                        }
                        $array[config('custom.days.' . $request->day)] = $day_array;
                        $schedule->working_hours = json_encode($array);
                        $schedule->save();
                    // } else { // update duration and working hrs
                    //     $array = config('custom.empty_working_hours');
                    //     $array[config('custom.days.' . $request->day)] = explode(',', $request->working_hours);
                    //     $schedule->duration = $seconds;
                    //     $schedule->working_hours = json_encode($array);
                    //     $schedule->save();

                    //     //
                    //     $type = ($request->appointment_type == 1) ? 2 : 1;
                    //     $schedule2 = ScheduleTiming::where('provider_id', $request->provider_id)->where('appointment_type', $type)->first();
                    //     $schedule2->duration = $seconds;
                    //     $schedule2->working_hours = json_encode(config('custom.empty_working_hours'));
                    //     $schedule2->save();
                    // }
                }

            } else { // insert
                for ($i = 1; $i <= 2; $i++) {
                    $schedule = new ScheduleTiming;
                    $schedule->provider_id = $request->provider_id;
                    $schedule->appointment_type = $i;
                    // $schedule->duration = $seconds;
                    if ($i == $request->appointment_type) {
                        $array = config('custom.empty_working_hours');
                        $array[config('custom.days.' . $request->day)] = explode(',', $request->working_hours);
                        $schedule->working_hours = json_encode($array);
                    } else {
                        $schedule->working_hours = json_encode(config('custom.empty_working_hours'));
                    }
                    $schedule->save();
                }
            }

            $data = collect();
            $result['provider_details'] = User::find($request->provider_id);
            $list = ScheduleTiming::where('provider_id', $request->provider_id)->get();
            $list->each(function ($schedule_timing) use (&$data) {
                $data->push($schedule_timing->getData());
            });
            $result['list'] = $data;

            return self::send_success_response($result, 'Schedule details updated successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function scheduleDelete(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|numeric|exists:users,id',
            // 'duration' => 'required|date_format:"H:i:s',
            'appointment_type' => 'required|numeric|in:1,2',
            'day' => 'required|numeric|in:1,2,3,4,5,6,7',
            'working_hours' => 'required|string',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $seconds = Carbon::parse('00:00:00')->diffInSeconds(Carbon::parse($request->duration));
            $seconds = (int) $seconds;
            $schedule = ScheduleTiming::where('provider_id', $request->provider_id)->where('appointment_type', $request->appointment_type)->first();
            if ($schedule) {
                $array = json_decode($schedule->working_hours, true);
                $inner_arr = $array[config('custom.days.' . $request->day)];
                if (($key = array_search($request->working_hours, $inner_arr,true)) !== false) {
                    array_splice($inner_arr, $key, 1);
                } else {
                    return self::send_bad_request_response('No matches found');
                }
                $array[config('custom.days.' . $request->day)] = $inner_arr;
                $schedule->working_hours = json_encode($array);
                $schedule->save();

                $data = collect();
                $result['provider_details'] = User::find($request->provider_id);
                $list = ScheduleTiming::where('provider_id', $request->provider_id)->get();
                $list->each(function ($schedule_timing) use (&$data) {
                    $data->push($schedule_timing->getData());
                });
                $result['list'] = $data;
                return self::send_success_response($result, 'Schedule deleted successfully');
            }
            return self::send_bad_request_response('No records found');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function savedCards(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->hasRole('patient')) {
                $saved_cards = collect();
                foreach ($user->paymentMethods() as $paymentMethod){
                    $saved_cards->push([
                        'id' => $paymentMethod->id,
                        'brand' => $paymentMethod->card->brand,
                        'last4' => $paymentMethod->card->last4,
                        'name' => ucwords($paymentMethod->billing_details->name),
                        'exp_month' => $paymentMethod->card->exp_month,
                        'exp_year' => $paymentMethod->card->exp_year,
                        'card_type' => $paymentMethod->card->funding,
                    ]);
                }
                return self::send_success_response($saved_cards->toArray());
            } else {
                return self::send_bad_request_response(['message' => 'Invalid request', 'error' => 'Invalid request']);
            }

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function invoiceList(Request $request)
    {
        try {
            $invoice_list = collect();
            $user = $request->user();
            updateLastSeen(auth()->user());
            if ($user->hasRole(['patient', 'doctor'])) {
                $payments = $user->payment()->get();

                foreach ($payments as $payment) {
                    $appointment = $payment->appointment()->first();
                    $invoice_list->push([
                        'payment' => $payment->getData(),
                        'from' => $appointment->getData()['doctor'],
                        'to' => $appointment->getData()['patient'],
                        'created' => $payment->getData()['created'],
                    ]);

                }
            }

            return self::send_success_response($invoice_list->toArray());
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function viewInvoice(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required',
            ]);

            if ($validator->fails()) {
                return self::send_bad_request_response($validator->errors()->first());
            }

            $result = [];
            $user = $request->user();
            if($user->hasRole(['patient', 'doctor'])){
                $payment = $user->payment()->where('payments.id', $request->invoice_id)->first();
                if($payment){
                    $appointment = $payment->appointment()->first();

                    $result = [
                        'payment' => $payment->getData(),
                        'from' => $appointment->getData()['doctor'],
                        'to' => $appointment->getData()['patient'],
                        'created' => $payment->getData()['created'],
                    ];
                }else{
                    return self::send_bad_request_response(['message' => 'Invoice not found with ID given', 'error' => 'Invoice not found with ID given']);
                }
            }

            return self::send_success_response($result);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function calendarList(Request $request){
        try{
            $user = auth()->user();

            $list = Appointment::orderBy('created_at','DESC');
            $data = collect();

            if ($user->hasRole('patient')) {
                $list = $list->whereUserId($user->id);
            } elseif ($user->hasRole('doctor')) {
                $list = $list->whereDoctorId($user->id);
            }

            $list->each(function ($appointment) use (&$data) {
                $data->push($appointment->basicData());
            });

            return self::send_success_response($data);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function saveCallLog(Request $request)
    {
        $rules = array(
            'appointment_id' => 'required|exists:appointments,id',
            'call_to' => 'required|exists:users,id',
            'start_time' => 'required|date_format:"Y-m-d H:i:s"',
            'type' => 'required|in:1,2',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $user = auth()->user();
            DB::beginTransaction();
            $log = $user->callLog()->create([
            'appointment_id' => $request->appointment_id,
            'from' => $user->id,
            'to' => $request->call_to,
            'type' => $request->type,
            'start_time' => $request->start_time,
            ]);

            $app = Appointment::find($request->appointment_id);
            $app->appointment_status = 3;
            $app->call_status = 1;
            $app->save();

            $applog = new AppointmentLog;
            $applog->appointment_id = $request->appointment_id;
            $applog->request_type = 1;
            $applog->description = config('custom.appointment_log_message.3');
            $applog->status = 3; //completed
            $applog->save();
            DB::commit();       
            return self::send_success_response($log,'Log Saved Successfully');
        } catch (Exception | Throwable $exception) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function updateCallLog(Request $request)
    {
        $rules = array(
            'call_log_id' => 'required|exists:call_logs,id',
            'end_time' => 'required|date_format:"Y-m-d H:i:s"',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $user = auth()->user();
            
            $log = CallLog::find($request->call_log_id);
            $duration = Carbon::parse($request->end_time)->diffInSeconds(Carbon::parse($log->start_time));
            $log->end_time =$request->end_time;
            $log->duration = ($duration>0)?$duration:0;
            $log->save();

            return self::send_success_response('Log updated Successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
