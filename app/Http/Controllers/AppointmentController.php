<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\TimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use \Exception;
use Illuminate\Support\Facades\Cookie;
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
            $list = new Appointment();
            $appointment_date = $request->appointment_date ? Carbon::createFromFormat($request->appointment_date, 'd-m-Y') : null;

            $list->when($appointment_date, function ($qry) use ($appointment_date) {
                $qry->whereDate('appointment_date', convertToUTC($appointment_date));
            });

            if ($user->hasRole('patient')) {
                $list = $list->whereUserId($user->id)->get();
            } elseif ($user->hasRole('doctor')) {
                $list = $list->whereUserId($user->id)->get();
            } elseif ($user->hasRole('doctor')) {
                $list = $list->get();
            }
            return self::send_success_response($list);
        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $appointment = new Appointment();
            $last_id = $appointment->lastest()->first() ? $appointment->lastest()->first()->id : 0;
            $appointment->appointment_reference = 'APT' . str_pad($last_id + 1, 12, "0", STR_PAD_LEFT);
            $appointment->user_id = $request->user_id;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->appointment_type = $request->appointment_type;
            $appointment->start_time = convertToUTC($request->start_time);
            $appointment->end_time = $request->end_time;
            $appointment->payment_type = 1;
            $appointment->save();

            return self::send_success_response($appointment->toArray());

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
