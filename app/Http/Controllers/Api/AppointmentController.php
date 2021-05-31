<?php
namespace App\Http\Controllers\Api;

use App\Appointment;
use App\AppointmentLog;
use App\CallLog;
use App\EmailTemplate;
use App\Http\Controllers\Controller;
use App\Mail\SendInvitation;
use App\Notifications\AppointmentNoty;
use App\Payment;
use App\Prescription;
use App\PrescriptionDetail;
use App\ScheduleTiming;
use App\Setting;
use App\Signature;
use App\Speciality;
use App\TimeZone;
use App\User;
use App\UserSpeciality;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

    function list(Request $request, $flag = null) {
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        if ($request->request_type) {
            $common['lang_content'] = getLangContent(18, $lang_id);
        } else {
            $common['lang_content'] = getLangContent(12, $lang_id);
        }
        $common['footer'] = getLangContent(9, $lang_id);

        try {
            $rules = array(
                'count_per_page' => 'nullable|numeric',
                'page' => 'nullable|numeric',
                'order_by' => 'nullable|in:desc,asc',
                'appointment_status' => 'nullable|numeric',
                'status' => 'nullable|numeric',
                'request_type' => 'nullable|numeric|in:1,2',
                'appointment_date' => 'nullable|date_format:d/m/Y',
                'consumer_id' => 'integer|exists:users,id',
                'appointment_type' => 'integer|numeric|in:1,2',
            );
            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
            }
            $valid = self::customValidation($request, $rules, $common);
            if ($valid) {return $valid;}

            $user = auth()->user();
            updateLastSeen(auth()->user());
            $update = Appointment::whereIn('appointment_status', [1, 2])->whereDate('appointment_date', '<', convertToUTC(Carbon::now(),'','Y-m-d'))->get();
            // dd( convertToUTC(Carbon::now(),'','Y-m-d'));
            if ($update) {
                foreach ($update as $upd) {
                    $app = Appointment::where('id', $upd->id)->where('end_time', '<', convertToLocal(Carbon::parse(now()), $upd->time_zone, 'H:i:s'))->first();
                    if ($app) {
                        Appointment::where('id', $app->id)->update(['appointment_status' => 7]);

                        $user = User::find($app->user_id);
                        $requested_amount = $app->payment->total_amount - $app->payment->transaction_charge;
                        $user->depositFloat($requested_amount);
                    }
                }
            }

            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $pageNumber = $request->page ? $request->page : 1;

            $order_by = $request->order_by ? $request->order_by : 'desc';
            $list = Appointment::orderBy('created_at', $order_by);

            $status = $request->appointment_status;
            if ($status) {
                switch ($status) {
                    case 1: //upcoming
                        $list = $list->whereIn('appointment_status', [1, 2])->whereDate('appointment_date', '>=', convertToUTC(now()));
                        break;
                    case 2: //missed
                        $list = $list->whereIn('appointment_status', [1, 2])->whereDate('appointment_date', '<', convertToUTC(now()));
                        break;
                    case 3: //completed/approved
                        $list = $list->where('appointment_status', 3);
                        break;
                    default:
                        break;
                }
            }

            if ($request->status) {
                $list = $list->where('appointment_status', $request->status);
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

            if ($request->consumer_id) {
                $list = $list->whereUserId($request->consumer_id);
            }
            if (!empty($request->appointment_type) && $request->appointment_type > 0) { // required only for mobile - api
                $list = $list->where('appointment_type', $request->appointment_type);
            }
            if (!empty($request->request_type) && $request->request_type > 0) {
                $list = $list->where('request_type', $request->request_type);
            } elseif ($user->hasRole('patient')) {
                // $list = $list->where('appointment_status','<>',3);
            } elseif ($user->hasRole('doctor')) {
                if (empty($request->status)) {
                    $list = $list->where('appointment_status', '<>', 4);
                }
            }

            removeMetaColumn($user);
            unset($user->roles);
            if ($request->route()->getName() == 'appointmentList') {
                $result['user_details'] = self::convertNullsAsEmpty($user->toArray());
            } else {
                $result['user_details'] = $user;
            }
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
            if ($flag) {
                $list->paginate(10)->getCollection()->each(function ($appointment) use (&$data) {
                    $data->push($appointment->basicData());
                });
                return $data;
            } else {
                $paginatedata = $list->paginate($paginate, ['*'], 'page', $pageNumber);

                $paginatedata->getCollection()->each(function ($appointment) use (&$data) {
                    $data->push($appointment->getData());
                });
                if ($request->route()->getName() == 'appointmentList') {
                    $result['list'] = self::convertNullsAsEmpty($data);
                } else {
                    $result['list'] = $data;
                }
                $result['total_count'] = $paginatedata->total();
                $result['last_page'] = $paginatedata->lastPage();
                $result['current_page'] = $paginatedata->currentPage();
                if ($request->payment_gateway) {
                    $result['payment_gateway'] = Setting::select('keyword', 'value')->where('slug', 'payment_gateway')->get();
                } elseif ($request->toxbox) {
                    $result['toxbox'] = Setting::select('keyword', 'value')->where('slug', 'tokbox')->get();
                }
                return self::send_success_response($result, '', $common);
            }
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
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
            $chk = Appointment::where(['doctor_id' => $doctor->id, 'appointment_date' => $appointment_date->toDateString(), 'start_time' => convertToUTC(Carbon::parse($request->start_time),'','H:i:s'), 'end_time' => convertToUTC(Carbon::parse($request->end_time),'','H:i:s')])->first();
            if ($chk) {
                if ($request->route()->getName() == "appointmentCreate") {
                    return self::send_bad_request_response('Appointment already exists');
                } else {
                    return self::send_bad_request_response(['message' => 'Appointment already exists', 'error' => 'Appointment already exists']);
                }
            }

            $appointment = new Appointment();
            $last_id = $appointment->latest()->first() ? $appointment->latest()->first()->id : 0;
            $appointment->appointment_reference = generateReference($user->id, $last_id, 'APT');
            $appointment->user_id = auth()->user()->id;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->appointment_type = $request->appointment_type; //1=online, 2=clinic
            $appointment->appointment_date = $appointment_date;
            $appointment->start_time = convertToUTC(Carbon::parse($request->start_time),'','H:i');
            $appointment->end_time = convertToUTC(Carbon::parse($request->end_time),'','H:i');
            $appointment->payment_type = $request->payment_type;
            $appointment->request_type = 1;
            $appointment->appointment_status = 1;
            if (isset($request->time_zone) && !empty($request->time_zone)) {
                $appointment->time_zone = $request->time_zone;
            }
            $appointment->save();

            $log = new AppointmentLog;
            $log->appointment_id = $appointment->id;
            $log->request_type = 1;
            $log->description = config('custom.appointment_log_message.1');
            $log->status = $appointment->appointment_status;
            $log->created_at = convertToUTC(Carbon::now());
            $log->save();

            /* Notification */
            auth()->user()->notify(new AppointmentNoty($appointment));
            $doctor->notify(new AppointmentNoty($appointment));

            $name = $doctor->first_name . ' ' . $doctor->last_name;
            $app_date = Carbon::parse($appointment->appointment_date)->format('d/m/Y');
            $start_time = Carbon::parse($request->start_time)->format('h:i A');
            $end_time = Carbon::parse($request->end_time)->format('h:i A');
            $reference = $appointment->appointment_reference;

            /* Mail */
            $template = EmailTemplate::where('slug', 'book_appointment')->first();
            if ($template) {
                $body = ($template->content); // this is template dynamic body. You may get other parameters too from database.

                $a1 = array('{{username}}', '{{doctor}}', '{{app_date}}', '{{start_time}}', '{{end_time}}', '{{reference}}', '{{config_app_name}}', '{{custom_support_phone}}', '{{custom_support_email}}');
                $a2 = array($user->first_name, $name, $app_date, $start_time, $end_time, $reference, config('app.name'), config('custom.support_phone'), config('custom.support_email'));

                $response = str_replace($a1, $a2, $body); // this will replace {{username}} with $data['username']

                $mail = [
                    'body' => html_entity_decode(htmlspecialchars_decode($response)),
                    'subject' => $template->subject,
                ];

                $mailObject = new SendInvitation($mail); // you can make php artisan make:mail MyMail
                Mail::to($user->email)->send($mailObject);
            }
            /* MOBILE NOTY */

            $notifydata['device_id'] = $doctor->device_id;

            $device_type = $doctor->device_type;

            $nresponse['from_name'] = auth()->user()->first_name . ' ' . auth()->user()->last_name;

            $message = $nresponse['from_name'] . ' has booked an appointment on ' . $app_date . ' at ' . $start_time . ' to ' . $end_time . ' reference #' . $reference . '!';

            $notifydata['message'] = $message;
            $notifydata['notifications_title'] = 'Appointment Schedule';
            $nresponse['type'] = 'Booking';
            $notifydata['additional_data'] = $nresponse;
            if ($device_type == 'Android' && (!empty($notifydata['device_id']))) {
                sendFCMNotification($notifydata);
            }
            if ($device_type == 'IOS' && (!empty($notifydata['device_id']))) {
                sendFCMiOSMessage($notifydata);
            }

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
                $speciality_amt = ($speciality) ? $speciality->amount : 0;
                $amount = $speciality_amt * $request->selected_slots;
                $transaction_charge = ($amount * ($getSettings['trans_percent'] / 100));
                $total_amount = $amount + $transaction_charge;
                $tax_amount = ($total_amount * $getSettings['tax_percent'] / 100);

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

            // $requested_amount = $payment->total_amount - ($payment->tax_amount + $payment->transaction_charge);
            // $doctor->depositFloat($requested_amount);

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
                    if (!$user->hasStripeId()) {
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
                    // $payment->transaction_charge = $stripeCharge->fee_details[0]->amount / 100;
                    $payment->save();
                    DB::commit();
                    return self::send_success_response($appointment->getData(), 'Appointment has been scheduled.');

                } else {
                    DB::rollBack();
                    return self::send_bad_request_response(['message' => 'Payment processing failed', 'error' => 'Payment processing failed']);
                }
            }

            DB::commit();

            return self::send_success_response($appointment->getData(), 'Appointment has been scheduled.');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getsignature($doctor_id)
    {

        $sign = Signature::select('id', 'signature_image')->whereUserId($doctor_id)->orderBy('id', 'desc')->first();
        if ($sign && !empty($sign->signature_image) && Storage::exists('images/signature/' . $sign->signature_image)) {
            $img = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('app/public/images/signature/' . $sign->signature_image, now()->addMinutes(5)) : Storage::url('app/public/images/signature/' . $sign->signature_image);
            $data = [
                'id' => $sign->id,
                'signature_image' => $img,
            ];
            return self::send_success_response($data, 'Signature fetched Successfully');
        } else {
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
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(35, $lang_id);
        $common['footer'] = getLangContent(9, $lang_id);

        $rules = array(
            'consumer_id' => 'nullable|numeric|exists:users,id',
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
            'page' => 'nullable|numeric',
        );
        if ($request->language_id) {
            $rules['language_id'] = 'integer|exists:languages,id';
        }

        $valid = self::customValidation($request, $rules, $common);
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
            
            if(auth()->user()->hasrole('doctor')){
                $list = $list->where('doctor_id',auth()->user()->id);
            }
            
            $data = collect(); 
            $paginatedata = $list->paginate($paginate, ['*'], 'page', $pageNumber);
            $paginatedata->getCollection()->each(function ($prescription) use (&$data) {
                $data->push($prescription->getData());
            });
            $result['list'] = $data;
            $result['total_count'] = $paginatedata->total();
            $result['last_page'] = $paginatedata->lastPage();
            $result['current_page'] = $paginatedata->currentPage();

            if ($data) {
                return self::send_success_response($result, 'Prescription Details Fetched Successfully', $common);
            } else {
                return self::send_bad_request_response('No Records Found', $common);
            }

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
        }
    }

    public function prescriptionView($pid)
    {
        $list = Prescription::where('id', $pid);
        if ($list->get()) {
            $data = collect();
            $list->each(function ($prescription) use (&$data) {
                $data->push($prescription->getData());
            });
            return self::send_success_response($data, 'Prescription Details Fetched Successfully');
        } else {
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
            $cancel = 0;
            $appointment = Appointment::find($request->appointment_id);
            if ($request->status == 6) {
                if ($appointment->appointment_status == 1 || $appointment->appointment_status == 2) {
                    $cancel = 1;
                }
            }
            $appointment->appointment_status = $request->status;
            if (isset($request->request_type)) {
                $appointment->request_type = $request->request_type;
            }
            $appointment->save();

            $log = new AppointmentLog;
            $log->appointment_id = $appointment->id;
            $log->description = config('custom.appointment_log_message.' . $request->status . '');
            if (isset($request->request_type)) {
                $log->request_type = $request->request_type;
            }
            $log->status = $appointment->appointment_status;
            $log->save();
            if ($request->status == 5 && $appointment->payment->total_amount > 0) { // refund approved
                $user = User::find($appointment->user_id);
                $requested_amount = $appointment->payment->total_amount - $appointment->payment->transaction_charge;
                $user->depositFloat($requested_amount);

                //($appointment->payment->transaction_charge > $appointment->payment->tax_amount) ? $value = $appointment->payment->transaction_charge - $appointment->payment->tax_amount :  $value = $appointment->payment->tax_amount - $appointment->payment->transaction_charge;
                $value = $appointment->payment->transaction_charge + $appointment->payment->tax_amount;
                $withdraw_amount = $appointment->payment->total_amount - ($value);
                $doctor = User::find($appointment->doctor_id);
                $doctor->withdrawFloat($withdraw_amount);
            }
            if ($request->status == 6 && $cancel == 1) {
                $user = User::find($appointment->user_id);
                $requested_amount = $appointment->payment->total_amount - $appointment->payment->transaction_charge;
                $user->depositFloat($requested_amount);
            }

            $consumer = User::find($appointment->user_id);
            $consumer->notify(new AppointmentNoty($appointment));
            $provider = User::find($appointment->doctor_id);
            $provider->notify(new AppointmentNoty($appointment));

            return self::send_success_response([], 'Status updated sucessfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function scheduleList(Request $request)
    {
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        if ($request->is_schedule_timing) {
            $common['lang_content'] = getLangContent(29, $lang_id);
        } else {
            $common['lang_content'] = getLangContent(22, $lang_id);
        }
        $common['footer'] = getLangContent(9, $lang_id);

        $rules = array(
            'provider_id' => 'required|numeric|exists:users,id',
        );
        if ($request->language_id) {
            $rules['language_id'] = 'integer|exists:languages,id';
        }
        $valid = self::customValidation($request, $rules, $common);
        if ($valid) {return $valid;}

        try {
            $data = collect();
            if (auth()->user()) {
                updateLastSeen(auth()->user());
                $result['provider_details'] = $user = User::find($request->provider_id);
                $list = ScheduleTiming::where('provider_id', $request->provider_id)->get();
                $list->each(function ($schedule_timing) use (&$data) {
                    $data->push($schedule_timing->getData());
                });
                $result['list'] = $data;
                // dd($data);
                $result['specialities'] = $user->getProviderSpecialityAttribute();
            }
            return self::send_success_response($result, 'Schedule Details Fetched Successfully', $common);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
        }
    }

    public function scheduleListForPatient(Request $request)
    {
        try {
            $request_day = strtolower(Carbon::parse(str_replace('/', '-', $request->selected_date))->format('l'));
            $selectedDate = Carbon::parse(str_replace('/', '-', $request->selected_date))->format('Y-m-d');
            $list1 = ScheduleTiming::where('provider_id', $request->provider_id)->where('appointment_type', 1)->first();
            $list2 = ScheduleTiming::where('provider_id', $request->provider_id)->where('appointment_type', 2)->first();
            $array1 = json_decode($list1->working_hours, true);
            $array1 = $array1[$request_day]; //online
            $array2 = json_decode($list2->working_hours, true);
            $array2 = $array2[$request_day]; //offline
            $final = array_merge($array1, $array2);
            $speciality = UserSpeciality::find($request->speciality_id);
            $speciality_seconds = (isset($speciality->duration))?$speciality->duration:600;

            $minutes = (($speciality_seconds / 60) % 60);
            $interval = $minutes;
            sort($final);
            
            // dd($final);
            $results = [];
            (auth()->user()->timezone)? $zone = auth()->user()->timezone->name : $zone = 'Asia/Kolkata';
            // date_default_timezone_set($zone);
            foreach ($final as $item) {
                $stime = explode('-', $item);
                // $startTime = Carbon::parse($stime[0]);
                // $endTime = Carbon::parse($stime[1]);
                $startTime = convertToLocal(Carbon::parse($stime[0]),$zone);
                $endTime = convertToLocal(Carbon::parse($stime[1]),$zone);
                $sseconds = $endTime->diffInSeconds($startTime);
// dd($endTime->gt());
                if ($sseconds >= $speciality_seconds) {
                    // date_default_timezone_set('US/Eastern');
                    
                    // $currentTime = strtotime(date('H:i:s'));
                    $currentTime = strtotime(Carbon::now()->format('H:i:s'));
                    // dd(date('H:i:s'));
                    $startTimeSeconds = strtotime($startTime->format('H:i:s'));
                    $endTimeSeconds = strtotime($endTime->format('H:i:s'));

                    if ($selectedDate == convertToLocal(Carbon::now(),$zone,'Y-m-d')) { //current date check

                        // if ($startTimeSeconds >= $currentTime) { // if currenttime check
                            $startPlusInterval = strtotime('+' . $interval . ' minutes', $startTimeSeconds);
                            // $startPlusInterval = $startTime->addMinutes($interval);
                            if ($startPlusInterval <= $endTimeSeconds) {
                                $start = $this->roundToNearestMinuteInterval($startTime, $interval);
                                $end = $this->roundToNearestMinuteInterval($endTime, $interval);
                                for (; $start <= $end; $start += $interval * 60) {
                                    // $results[] = date('H:i:s', $start)
                                    $temp = strtotime('+' . $interval . ' minutes', $start);
                                    // dd($start,$currentTime,Carbon::now()->date('H:i:s'),$startTime);
                                    if ($start >= $currentTime){ 
                                    if ($temp <= $endTimeSeconds) {
                                        $chk = Appointment::where(['doctor_id' => $request->provider_id, 'appointment_date' => $selectedDate, 'start_time' => Carbon::parse($start)->format('H:i:s')])->first();
                                        if (!$chk) {
                                            $results[] = $start . '-' . $temp;
                                        }
                                        // $results[] = date('h:i A', $start).'-'.date('h:i A',$temp);
                                    }
                                    }
                                }
                            }
                        // } // if currenttime check
                    } else {
                        $startPlusInterval = strtotime('+' . $interval . ' minutes', $startTimeSeconds);
                        if ($startPlusInterval <= $endTimeSeconds) {
                            $start = $this->roundToNearestMinuteInterval($startTime, $interval);
                            $end = $this->roundToNearestMinuteInterval($endTime, $interval);
                            for (; $start <= $end; $start += $interval * 60) {
                                // $results[] = date('H:i:s', $start)
                                $temp = strtotime('+' . $interval . ' minutes', $start);
                                if ($temp <= $endTimeSeconds) {
                                    $chk = Appointment::where(['doctor_id' => $request->provider_id,'appointment_date' => $selectedDate, 'start_time' => Carbon::parse($start)->format('H:i:s')])->first();
                                    if (!$chk) {
                                        $results[] = $start . '-' . $temp;
                                    }
                                    // $results[] = date('h:i A', $start).'-'.date('h:i A',$temp);
                                }
                            }
                        }
                    }
                }
            }
            // dd($results);
            $newresults = [];
            if ($results) {
                if ($request->route()->getName() == "scheduleListPatient") {//for mobile
                    $cnt =0;
                    foreach ($results as $key=>$res) {
                        $exp = explode('-', $res);
                        $firstElement = $exp[0];
                        $app_type = $this->getAppointmentType($array1,$array2,$exp[0]);
                        $newresults[$cnt]['appointment_type'] = $app_type;
                        $newresults[$cnt]['time'] = date('h:i A',$exp[0]) . ' - ' . date('h:i A',$exp[1]);
                        $cnt++;
                    }
                }else{ //for web
                $mor=$aft=$eve=0;
                foreach ($results as $key=>$res) {
                    $exp = explode('-', $res);
                    $firstElement = $exp[0];
                    $app_type = $this->getAppointmentType($array1,$array2,$exp[0]);
                    $morning = strtotime('12:00:00');
                    $afternoon = strtotime('16:00:00');
                    if ($firstElement <= $morning) {
                        $newresults['morning'][$mor]['appointment_type'] = $app_type;
                        $newresults['morning'][$mor]['time'] = date('h:i A',$exp[0]) . ' - ' . date('h:i A',$exp[1]);
                        $mor++;
                    } elseif ($firstElement <= $afternoon) {
                        $newresults['afternoon'][$aft]['appointment_type'] = $app_type;
                        $newresults['afternoon'][$aft]['time'] = date('h:i A',$exp[0]) . ' - ' . date('h:i A',$exp[1]);
                        $aft++;
                    } else {
                        $newresults['evening'][$eve]['appointment_type'] = $app_type;
                        $newresults['evening'][$eve]['time'] = date('h:i A',$exp[0]) . ' - ' . date('h:i A',$exp[1]);
                        $eve++;
                    }
                }
            }
            }
            // dd($newresults);
            // sort($results);
            return self::send_success_response($newresults, 'Schedule Details Fetched Successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }

    }
    public function getAppointmentType($array1,$array2,$time)
    {
        (auth()->user()->timezone)? $zone = auth()->user()->timezone->name : $zone = 'Asia/Kolkata';
        $app_type ='';
        foreach ($array1 as $item) {
            $stime = explode('-', $item);
            $startTime = convertToLocal(Carbon::parse($stime[0]),$zone);
            $endTime = convertToLocal(Carbon::parse($stime[1]),$zone);
            $startTime = strtotime($startTime->format('H:i:s'));
            $endTime = strtotime($endTime->format('H:i:s'));
            if($time>=$startTime && $time<=$endTime){
                $app_type = 1;
            }
        }
        foreach ($array2 as $item) {
            $stime = explode('-', $item);
            $startTime = convertToLocal(Carbon::parse($stime[0]),$zone);
            $endTime = convertToLocal(Carbon::parse($stime[1]),$zone);
            $startTime = strtotime($startTime->format('H:i:s'));
            $endTime = strtotime($endTime->format('H:i:s'));
            if($time>=$startTime && $time<=$endTime){
                $app_type = 2;
            }
        }
        return $app_type;
    }
    public function roundToNearestMinuteInterval($time, $interval)
    {
        $timestamp = strtotime($time);
        $rounded = (($timestamp / ($interval * 60)) * ($interval * 60));
        return $rounded;
    }

    public function scheduleCreate(Request $request)
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
                    foreach ($incoming as $item) {
                        $t = explode('-',$item);
                        $t1 = convertToUTC(Carbon::parse($t[0]),'','H:i'); 
                        $t2 = convertToUTC(Carbon::parse($t[1]),'','H:i'); 
                        $b = $t1.'-'.$t2;
                        array_push($day_array, $b);
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
                        $incoming = explode(',', $request->working_hours);
                    foreach($incoming as $item){
                        $t = explode('-',$item);
                        $t1 = convertToUTC(Carbon::parse($t[0]),'','H:i'); 
                        $t2 = convertToUTC(Carbon::parse($t[1]),'','H:i'); 
                        $b[] = $t1.'-'.$t2;
                    }

                    $array[config('custom.days.' . $request->day)] = $b;
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
                if (($key = array_search($request->working_hours, $inner_arr, true)) !== false) {
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
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(23, $lang_id);
        $common['footer'] = getLangContent(9, $lang_id);

        try {
            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';

                $valid = self::customValidation($request, $rules, $common);
                if ($valid) {return $valid;}
            }
            $user = $request->user();

            if ($user->hasRole('patient')) {
                $saved_cards = collect();
                foreach ($user->paymentMethods() as $paymentMethod) {
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
                return self::send_success_response($saved_cards->toArray(), 'OK', $common);
            } else {
                return self::send_bad_request_response(['message' => 'Invalid request', 'error' => 'Invalid request'], $common);
            }

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
        }
    }

    public function invoiceList(Request $request)
    {
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(14, $lang_id);
        $common['footer'] = getLangContent(9, $lang_id);

        try {
            $rules = array(
                'count_per_page' => 'nullable|numeric',
                'order_by' => 'nullable|in:desc,asc',
                'page' => 'nullable|numeric',
            );
            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
            }
            $valid = self::customValidation($request, $rules, $common);
            if ($valid) {return $valid;}

            $paginate = $request->count_per_page ? $request->count_per_page : 10;
            $order_by = $request->order_by ? $request->order_by : 'desc';
            $pageNumber = $request->page ? $request->page : 1;

            $invoice_list = collect();
            $user = $request->user();
            updateLastSeen(auth()->user());
            $payments = [];
            if ($user->hasRole(['patient'])) {
                $payments = $user->payment();
            }
            if ($user->hasRole(['doctor'])) {
                $payments = $user->providerPayment();
            }
            $data = collect();
            $paginatedata = $payments->paginate($paginate, ['*'], 'page', $pageNumber);
            $paginatedata->getCollection()->each(function ($payment) use (&$data) {
                $data->push($payment->getData());
            });
            $result['list'] = $data;
            $result['total_count'] = $paginatedata->total();
            $result['last_page'] = $paginatedata->lastPage();
            $result['current_page'] = $paginatedata->currentPage();

            return self::send_success_response($result, 'OK', $common);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
        }
    }

    public function viewInvoice(Request $request)
    {
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(15, $lang_id);
        $common['footer'] = getLangContent(9, $lang_id);

        try {

            $rules = array(
                'invoice_id' => 'required|exists:payments,id',
            );

            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
            }
            $valid = self::customValidation($request, $rules, $common);
            if ($valid) {return $valid;}

            $result = [];
            $user = $request->user();
            if ($user->hasRole('patient')) {
                $payment = $user->payment()->where('payments.id', $request->invoice_id)->first();
            }
            if ($user->hasRole('doctor')) {
                $payment = $user->providerPayment()->where('payments.id', $request->invoice_id)->first();
            }
            if ($payment) {
                $result = [
                    'payment' => $payment->getData(),
                ];
            } else {
                return self::send_bad_request_response(['message' => 'Invoice not found with ID given', 'error' => 'Invoice not found with ID given'], $common);
            }

            return self::send_success_response($result, 'OK', $common);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
        }
    }

    public function calendarList(Request $request)
    {
        $common = [];
        $lang_id = ($request->language_id) ? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8, $lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(13, $lang_id);
        $common['footer'] = getLangContent(9, $lang_id);

        try {
            if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
                $valid = self::customValidation($request, $rules, $common);
                if ($valid) {return $valid;}
            }
            $user = auth()->user();

            $list = Appointment::orderBy('created_at', 'DESC');
            $data = collect();

            if ($user->hasRole('patient')) {
                $list = $list->whereUserId($user->id);
            } elseif ($user->hasRole('doctor')) {
                $list = $list->whereDoctorId($user->id);
            }

            $list->each(function ($appointment) use (&$data) {
                $data->push($appointment->basicData());
            });

            return self::send_success_response($data, 'Appointment Calender Data Fetched Successfully', $common);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage(), $common);
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
        if ($request->route()->getName() == "saveCallLog") {
            $rules['call_type'] = 'required';
        }
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $user = auth()->user();

            $callLog = CallLog::where('appointment_id', $request->appointment_id)->where('from', $user->id)->where('to', $request->call_to)->whereNull('end_time')->first();
            if ($callLog) {
                $log = $callLog;
            } else {
                $log = $user->callLog()->create([
                    'appointment_id' => $request->appointment_id,
                    'from' => $user->id,
                    'to' => $request->call_to,
                    'type' => $request->type,
                    'start_time' => $request->start_time,
                ]);

                // $app = Appointment::find($request->appointment_id);
                // $consumer = User::find($app->user_id);
                // $consumer->notify(new AppointmentNoty($app));
                // $provider = User::find($app->doctor_id);
                // $provider->notify(new AppointmentNoty($app));
            }

            if ($request->route()->getName() == "saveCallLog") {
                $appoinments_details = Appointment::Find($request->appointment_id);

                $patient = User::Find($appoinments_details->user_id);
                $doctor = User::Find($appoinments_details->doctor_id);
                $response = array();
                $response['patient_id'] = $patient->id;
                $response['patient_name'] = $patient->first_name . ' ' . $patient->last_name;
                $response['patient_image'] = getUserProfileImage($patient->id);
                $response['doctor_id'] = $doctor->id;
                $response['doctor_name'] = $doctor->first_name . ' ' . $doctor->last_name;
                $response['doctor_image'] = getUserProfileImage($doctor->id);

                if ($user->hasRole('doctor')) {
                    $notifydata['device_id'] = $patient->device_id;
                    $device_type = $patient->device_type;
                    $notifydata['message'] = 'Incoming call from ' . $doctor->first_name . ' ' . $doctor->last_name;
                }

                if ($user->hasRole('patient')) {
                    $notifydata['device_id'] = $doctor->device_id;
                    $device_type = $doctor->device_type;
                    $notifydata['message'] = 'Incoming call from ' . $patient->first_name . ' ' . $patient->last_name;
                }
                $response['appoinment_id'] = $request->appointment_id;
                $response['type'] = $request->call_type;

                $response['tokbox'] = Setting::select('keyword', 'value')->where('slug', 'tokbox')->get();
                $response['call_log'] = $log;
                $notifydata['notifications_title'] = 'Incoming call';
                $notifydata['additional_data'] = $response;

                if ($device_type == 'Android' && (!empty($notifydata['device_id']))) {
                    sendFCMNotification($notifydata);
                }
                if ($device_type == 'IOS' && (!empty($notifydata['device_id']))) {
                    sendFCMiOSMessage($notifydata);
                }
                return self::send_success_response($response, 'Log Saved Successfully');
            } else {
                return self::send_success_response($log, 'Log Saved Successfully');
            }
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

            $log = CallLog::where('id', $request->call_log_id)->whereNull('end_time')->first();

            if ($log) {
                $duration = Carbon::parse($request->end_time)->diffInSeconds(Carbon::parse($log->start_time));
                $log->end_time = $request->end_time;
                $log->duration = ($duration > 0) ? $duration : 0;
                $log->save();

                $app = Appointment::find($log->appointment_id);

                if ($app->call_status == 0) {
                    $app->appointment_status = 3;
                    $app->call_status = 1;
                    $app->save();

                    $applog = new AppointmentLog;
                    $applog->appointment_id = $log->appointment_id;
                    $applog->request_type = 1;
                    $applog->description = config('custom.appointment_log_message.3');
                    $applog->status = 3; //completed
                    $applog->save();

                    $doctor = User::find($app->doctor_id);
                    //($app->payment->transaction_charge > $app->payment->tax_amount) ? $value = $app->payment->transaction_charge - $app->payment->tax_amount :  $value = $app->payment->tax_amount - $app->payment->transaction_charge;
                    $value = ($app->payment->transaction_charge + $app->payment->tax_amount);
                    $requested_amount = $app->payment->total_amount - $value;
                    $doctor->depositFloat($requested_amount);
                }
            }

            return self::send_success_response('Log updated Successfully');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function makeCall(Request $request)
    {
        $rules = array(
            'appointment_id' => 'required|exists:appointments,id',
            'call_type' => 'required',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

        try {
            $user = auth()->user();

            $appoinments_details = Appointment::Find($request->appointment_id);

            $patient = User::Find($appoinments_details->user_id);
            $doctor = User::Find($appoinments_details->doctor_id);
            $response = array();
            $response['patient_id'] = $patient->id;
            $response['patient_name'] = $patient->first_name . ' ' . $patient->last_name;
            $response['patient_image'] = getUserProfileImage($patient->id);
            $response['doctor_id'] = $doctor->id;
            $response['doctor_name'] = $doctor->first_name . ' ' . $doctor->last_name;
            $response['doctor_image'] = getUserProfileImage($doctor->id);

            if ($user->hasRole('doctor')) {

                $notifydata['device_id'] = $patient->device_id;
                $device_type = $patient->device_type;
                $notifydata['message'] = 'Incoming call from ' . $doctor->first_name . ' ' . $doctor->last_name;

            }

            if ($user->hasRole('patient')) {

                $notifydata['device_id'] = $doctor->device_id;
                $device_type = $doctor->device_type;
                $notifydata['message'] = 'Incoming call from ' . $patient->first_name . ' ' . $patient->last_name;
            }
            $response['appoinment_id'] = $request->appointment_id;
            $response['type'] = $request->call_type;

            $response['tokbox'] = Setting::select('keyword', 'value')->where('slug', 'tokbox')->get();

            //$response['sessionId'] = $appoinments_details['tokboxsessionId'];
            //$response['token'] = $appoinments_details['tokboxtoken'];
            //$response['tokbox_apiKey'] =$this->tokbox_apiKey;
            //$response['tokbox_apiSecret'] =$this->tokbox_apiSecret;
            $notifydata['notifications_title'] = 'Incoming call';
            $notifydata['additional_data'] = $response;

            if ($device_type == 'Android' && (!empty($notifydata['device_id']))) {
                sendFCMNotification($notifydata);
            }
            if ($device_type == 'IOS' && (!empty($notifydata['device_id']))) {
                sendFCMiOSMessage($notifydata);
            }
            //$this->call_details($response['invite_id'],$response['from_user_id'],$response['to'],$user_data['call_type']);
            //$result = $this->data_format($response_code,$response_message,$response);
            return self::send_success_response($response, 'Make Call');
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    /*private function process_card_for_charge_later($user, $card_details)
    {

    $StripePayment = new \App\Stripe\StripePayment();

    $stripe_cus_exist = $StripePayment->check_stripe_customer_exist($user->id);
    if ($stripe_cus_exist) {
    $stripe_res = $StripePayment->add_card_to_existing_stripe_customer($user->id, $card_details['setup_intent_id']);
    //Log::info('Order - Add Card to Stripe Customer', ['orderId' => $order_id]);
    } else {
    $stripe_res = $StripePayment->create_stripe_customer_and_save_card($user, $card_details['setup_intent_id']);
    //Log::info('Order - Create Stripe Customer & Save Card', ['orderId' => $order_id]);
    }

    if ($stripe_res['status'] === '1') {
    return TRUE;
    } elseif ($stripe_res['status'] === '0') {
    return $stripe_res['message'];
    } else {
    return $stripe_res['message'];
    }
    }*/

    public function create_setup_intent(Request $request)
    {
        try {

            $response_array = [];
            // check api_key
            $value = Setting::where("slug","payment_gateway")->where('keyword','stripe_live_api_key')->pluck('value');
            
            if ( $value && ($request->api_key == $value[0])) {

                $StripePayment = new \App\Stripe\StripePayment();

                $intent_param = [
                    'payment_method_types' => ['card'],
                ];

                $stripe_setup_intent = \Stripe\SetupIntent::create($intent_param);

                $response_array = ['Response' => [
                    'response_code' => '1',
                    'response_message' => 'setupintent created successfully',
                ],
                    'data' => [
                        'intent_client_secret' => $stripe_setup_intent->client_secret,
                        'payment_method' => '',
                    ],
                ];

            } else {
                //return error message if authencation failed
                $response_array = ['Response' => [
                    'response_code' => '-1',
                    'response_message' => 'authentication failed',
                ],
                    'data' => (object) [],
                ];
            }
        } catch (\Exception | \Throwable $e) {
            $response_array = [
                'Response' => [
                    'response_code' => '-1',
                    'response_message' => $e->getMessage(),
                ],
                'data' => (object) [],
            ];
        }
        return $this->convertNullsAsEmpty($response_array);
    }
}
