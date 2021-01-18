<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Appointment;
use App\TimeZone;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            $paginate = $request->count_per_page?$request->count_per_page:10;

            $order_by = $request->order_by?$request->order_by:'desc';
            $list = Appointment::orderBy('created_at', $order_by);

            $status = $request->appointment_status;
            if($status){
                switch ($status){
                    case 1:
                        $list = $list->where('appointment_status', false)->whereDate('appointment_date','>=', convertToUTC(now()));
                        break;
                    case 2:
                        $list = $list->where('appointment_status', true)->whereDate('appointment_date','<=', convertToUTC(now()));
                        break;
                    case 3:
                        $list = $list->where('appointment_status', false)->whereDate('appointment_date','<', convertToUTC(now()));
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
            $list->paginate($paginate)->getCollection()->each(function ($appointment) use (&$data){
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
            'appointment_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        if ($validator->fails()) {
            return self::send_bad_request_response($validator->errors()->first());
        }

        try {
            $appointment = new Appointment();
            $last_id = $appointment->latest()->first() ? $appointment->latest()->first()->id : 0;
            $appointment->appointment_reference = 'APT' . str_pad($last_id + 1, 12, "0", STR_PAD_LEFT);
            $appointment->user_id = 4;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->appointment_type = $request->appointment_type;
            $appointment->appointment_date = convertToUTC(Carbon::createFromFormat('d/m/Y', $request->appointment_date));
            $appointment->start_time = $request->start_time;
            $appointment->end_time = $request->end_time;
            $appointment->payment_type = 1;
            $appointment->save();

            return self::send_success_response($appointment->getData());

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
