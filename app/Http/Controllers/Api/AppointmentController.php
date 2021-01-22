<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Appointment;
use App\Payment;
use App\TimeZone;
use App\User;
use App\Prescription;
use App\PrescriptionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \Exception;
use \Throwable;
use Closure;

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

    public function list(Request $request)
    {
        try {
            $user = $request->user();
            $paginate = $request->count_per_page ? $request->count_per_page : 10;

            $order_by = $request->order_by ? $request->order_by : 'desc';
            $list = Appointment::orderBy('created_at', $order_by);

            $status = $request->appointment_status;
            if ($status) {
                switch ($status) {
                    case 1:
                        $list = $list->where('appointment_status', false)->whereDate('appointment_date', '>=', convertToUTC(now()));
                        break;
                    case 2:
                        $list = $list->where('appointment_status', true)->whereDate('appointment_date', '<=', convertToUTC(now()));
                        break;
                    case 3:
                        $list = $list->where('appointment_status', false)->whereDate('appointment_date', '<', convertToUTC(now()));
                        break;
                }
            }

            $appointment_date = $request->appointment_date ? Carbon::createFromFormat('d/m/Y', $request->appointment_date) : null;
            $list = $list->when($appointment_date, function ($qry) use ($appointment_date) {
                return $qry->whereDate('appointment_date', convertToUTC($appointment_date));
            });

            if ($user->hasRole('patient')) {
                $list = $list->whereUserId($user->id);
            } elseif ($user->hasRole('doctor')) {
                $list = $list->whereDoctorId($user->id);
            }

            $data = collect();
            $list->paginate($paginate)->getCollection()->each(function ($appointment) use (&$data) {
                $data->push($appointment->getData());
            });

            return self::send_success_response($data);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'appointment_type' => 'required',
            'payment_type' => 'required',
            'appointment_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }
        DB::beginTransaction();

        try {

            $user = $request->user();
            $doctor = User::find($request->doctor_id);

            $booking_hours = Carbon::createFromFormat('h:i:s', $request->start_time)->diffInHours(Carbon::createFromFormat('h:i:s', $request->end_time));

            /**
             * Appointment
             */

            $appointment = new Appointment();
            $last_id = $appointment->latest()->first() ? $appointment->latest()->first()->id : 0;
            $appointment->appointment_reference = generateReference($user->id, $last_id, 'APT');
            $appointment->user_id = 4;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->appointment_type = $request->appointment_type;
            $appointment->appointment_date = convertToUTC(Carbon::createFromFormat('d/m/Y', $request->appointment_date));
            $appointment->start_time = $request->start_time;
            $appointment->end_time = $request->end_time;
            $appointment->payment_type = 1;
            $appointment->save();

            /**
             * Payment
             */

            $payment = new Payment();
            $last_id = $payment->latest()->first() ? $payment->latest()->first()->id : 0;
            $payment->appointment_id = $appointment->id;
            $payment->invoice_no = generateReference($user->id, $last_id, 'INV');
            $payment->payment_type = $request->payment_type;
            $payment->total_amount = $doctor->amount * $booking_hours;

            $payment->currency_code = $doctor->currency_code ?? config('cashier.currency');
            $payment->save();

            if ($request->payment_type == 1 || $request->payment_type == 2) {

                $billable_amount = (int)($payment->total_amount * 100);

                $payment_options = [
                    'description' => config('app.name') . ' appointment reference #' . $appointment->appointment_reference,
                    'metadata' => [
                        'reference' => $appointment->appointment_reference,
                    ]
                ];

                if ($request->payment_type == 1) {
                    $paymentMethod = $user->findPaymentMethod($request->payment_method);

                    if (!$paymentMethod) {
                        DB::rollBack();
                        return self::send_bad_request_response(['message' => 'Payment processing failed', 'error' => 'Payment processing failed']);
                    }

                    $paymentIntent = $user->charge($billable_amount, $paymentMethod->id, $payment_options);
                } else {
                    $user->updateDefaultPaymentMethod($request->payment_method);
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

    Public function savePrescription(Request $request){
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required',
           /* 'drug_name' => 'required',
            'quantity' => 'required|integer',
            'type' => 'required|string',
            'days' => 'required|integer',
            'time' => 'required'*/
        ]);

        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }
        DB::beginTransaction();

        try {
            $prescription = new Prescription();
            $prescription->appointment_id = $request->appointment_id;
            if($request->signature_id){
                $prescription->signature_id = $request->signature_id;
            }
            $prescription->save();

           /* $prescription_details = new PrescriptionDetail();
            $prescription_details->prescription_id = $prescription->id;
            $prescription_details->drug_name = $request->drug_name;
            $prescription_details->quantity = $request->quantity;
            $prescription_details->type = $request->type;
            $prescription_details->days = $request->days;
            $prescription_details->time = $request->time;
            $prescription_details->save();
            DB::commit();
*/
            return self::send_success_response([],'Prescription Added Successfully');

        } catch (Exception | Throwable $exception) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function prescriptionList(){
    
        try {
            $list = Prescription::with('prescriptionDetails')->get();

            return self::send_success_response($list,'Prescription Added Successfully');

        } catch (Exception | Throwable $exception) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function savedCards(Request $request){
        try{
            $user = $request->user();

            if($user->hasRole('patient')){
                $saved_cards = collect();

                $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                $stripeCustomer = $user->asStripeCustomer();

                $paymentMethods = $stripe->paymentMethods->all([
                    'customer' => $stripeCustomer->id,
                    'type' => 'card',
                ]);

                foreach ($paymentMethods as $paymentMethod) {
                    $saved_cards->push([
                        'id' => $paymentMethod->id,
                        'brand' => $paymentMethod->card->brand,
                        'last4' => $paymentMethod->card->last4,
                        'name' => ucwords($paymentMethod->billing_details->name)
                    ]);
                }

                return self::send_success_response($saved_cards->toArray());
            }else{
                return self::send_bad_request_response(['message' => 'Invalid request', 'error' => 'Invalid request']);
            }

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
