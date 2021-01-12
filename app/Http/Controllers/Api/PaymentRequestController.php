<?php

namespace App\Http\Controllers\Api;

use App\ActivityLog;
use App\Http\Controllers\Controller;
use App\Notifications\RequestPayment;
use App\PaymentRequest;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class PaymentRequestController extends Controller
{

    public function driverPaymentRequest(Request $request)
    {
        $curdate = date('dmYHis');

        try {
            $user = auth()->user();
            if ($user->hasRole('driver')) {
                $validator = Validator::make($request->all(), [
                    'request_amount' => 'required|regex:/^\d*(\.\d{1,2})?$/',
                ]);
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
                }

                DB::beginTransaction();

                $user = User::with('driver', 'driver.terminal')->find(auth()->user()->id);
                if (!$user) {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'User not found.']);
                }
                if ($user->balanceFloat < $request->request_amount) {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'Not sufficient balance.']);
                } elseif ($request->request_amount == 0 || $request->request_amount < 0) {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'Invalid request amount.']);
                }
                if ((float)$request->request_amount == $user->balanceFloat) {
                    $request_type = 1;
                } else {
                    $request_type = 2;
                }
                $query = new PaymentRequest();

                $query->company_id = $user->company_id;
                $query->request_id = 'PR' . $user->driver->machine_id . '_' . $curdate;
                $query->terminal_id = $user->driver->terminal_id;
                $query->driver_id = $user->driver->id;
                $query->request_type = $request_type;
                $query->request_amount = $request->request_amount;
                $query->status = 1;
                $query->created_by = $user->id;
                $query->save();
                $user->withdrawFloat($request->request_amount);

                DB::commit();

                $user->notify(new RequestPayment($query));
                foreach (User::role(['company_admin', 'cash_distributor'])->get() as $adminUser) {
                    $adminUser->notify(new RequestPayment($query));
                }

                return response()->json(['success' => true, 'code' => 200, 'message' => 'Payment requested successfully!']);

            } else {
                return response()->json(['success' => false, 'code' => 401, 'error' => 'Only Driver role can access']);
            }
        } catch (Exception | Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function updatePaymentRequest(Request $request)
    {
        try {
            $user = auth()->user();
            $user_id = auth()->user()->id;
            if ($user->hasRole(['cash_distributor','driver'])) {
                DB::beginTransaction();
                $rules = array(
                    'payment_request_id' => 'required',
                    'status' => 'required',
                );
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'code' => 401, 'error' => $validator->errors()]);
                }

                if ($request->status == 2 || $request->status == 3 || $request->status == 4) {
                    $result = PaymentRequest::find($request->payment_request_id);
                    $user_detail = User::with('driver', 'driver.terminal')->find($result->driver->user->id);
                    $new_status = "In Progress";
                    $result->status = $request->status;
                    if($user->hasRole('cash_distributor')){
                    $result->cash_distributor_id = auth()->user()->id;
                    }
                    if ($request->status == 3) { //only when complete request
                        $new_status = "Completed";
                        if ($user_detail->balanceFloat < $result->request_amount) {
                            return response()->json(['success' => false, 'code' => 401, 'error' => 'Insufficient balance in the wallet']);
                        }
                        $result->payment_date = date('Y-m-d H:i:s');
                    }
                    if ($request->status == 4) { //for cancel the request
                        $new_status = "Cancelled";
                        $user_detail->depositFloat($result->request_amount);
                        $result->cancelled_date = date('Y-m-d H:i:s');
                    }
                    $result->updated_by = isset(auth()->user()->id) ? auth()->user()->id : '1';
                    $result->save();

                    $message = "Status has been changed to " . $new_status . " for " . $result->request_id;
                    createActivityLog(2,$message);

                } else {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'Invalid Status Code']);
                }
                DB::commit();
                return response()->json(['success' => true, 'code' => 200, 'message' => 'Status updated successfully!']);
            } else {
                return response()->json(['success' => false, 'code' => 500, 'error' => 'Only Cash Distributor can access']);
            }

        } catch (Exception | Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);
        }

    }

    /* Cash distributor Payment Request List */
    public function paymentRequestList(Request $request,$id=NULL)
    {
        $validator = Validator::make($request->all(), [
            'request_status' => 'required|integer|between:1,4', // 1=> Requested , 2=> In Progress , 3=> Success , 4=> Cancelled
            'from_date' => 'nullable|date_format:d-m-Y',
            'to_date' => 'nullable|date_format:d-m-Y',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
        }
        $user_id = (!empty($id))?$id:auth()->user()->id;
        $user = auth()->user();
        if ($user->hasRole(['cash_distributor','company_admin'])) {
            $withdraw_list = PaymentRequest::select('*', DB::raw('(CASE WHEN payment_requests.request_type = 1 THEN "Full Request" WHEN payment_requests.request_type = 2 THEN "Partial" END) AS request_type_name'), DB::raw('(CASE WHEN status = 1 THEN "Requested" WHEN status = 2 THEN "In Progress" WHEN status = 3 THEN "Success" ELSE "Cancelled" END) AS status_name'))

                ->where('company_id', auth()->user()->company_id)
                ->where('payment_requests.status', $request->request_status);
            if ($request->request_status == 2 || $request->request_status == 3 || $request->request_status == 4) { //new payment requests
                $withdraw_list = $withdraw_list->where('payment_requests.cash_distributor_id', $user_id);
            }
            /* Date filter */
            if (!empty($request->from_date) && !empty($request->to_date)) {
                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                $withdraw_list = $withdraw_list->whereDate('payment_requests.created_at', '>=', $from_date)->whereDate('payment_requests.created_at', '<=', $to_date);
            }

            // Advanced Search
            if ($request->driver_name != '') {
                $driver_name = $request->driver_name;
                $withdraw_list = $withdraw_list->whereHas('driver.user', function ($qry) use ($driver_name) {
                    $qry->where('name', "LIKE", '%' . $driver_name . '%');
                });
            }
            if ($request->mobile_number != '') {
                $mobile_number = $request->mobile_number;
                $withdraw_list = $withdraw_list->whereHas('driver.user', function ($qry) use ($mobile_number) {
                    $qry->where('mobile_number', "LIKE", '%' . $mobile_number . '%');
                });
            }
            if ($request->machine_id != '') {
                $machine_id = $request->machine_id;
                $withdraw_list = $withdraw_list->whereHas('driver', function ($qry) use ($machine_id) {
                    $qry->where('machine_id', "LIKE", '%' . $machine_id . '%');
                });
            }
            if ($request->request_id != '') {
                $request_id = $request->request_id;
                $withdraw_list = $withdraw_list->where('payment_requests.request_id', "LIKE", '%' . $request_id . '%');
            }
            if ($request->request_type != '' && ($request->request_type == 'Full' || $request->request_type == 'Partial')) {
                $request_type = $request->request_type == 'Full' ? 1 : 2;
                $withdraw_list = $withdraw_list->where('payment_requests.request_type', "=", $request_type);
            }
            $status_filter = $withdraw_list->orderBy('id', 'desc')->get();

            $data = $this->getDriverData($status_filter);

                return response()->json(['success' => true, 'code' => 200, 'data' => $data]);
            
        } else {
            return response()->json(['success' => false, 'code' => 500, 'error' => 'Only Cash Distributor can access']);
        }
    }

    //for driver
    public function driverRequestList(Request $request)
    {
        $user_id = auth()->user()->id;
        $user = auth()->user();
        if ($user->hasRole('driver')) {
            $validator = Validator::make($request->all(), [
                'from_date' => 'nullable|date_format:d-m-Y',
                'to_date' => 'nullable|date_format:d-m-Y',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
            }
            $withdraw_list = PaymentRequest::select('*', DB::raw('(CASE WHEN payment_requests.request_type = 1 THEN "Full Request" WHEN payment_requests.request_type = 2 THEN "Partial" END) AS request_type_name'), DB::raw('(CASE WHEN status = 1 THEN "Requested" WHEN status = 2 THEN "In Progress" WHEN status = 3 THEN "Success" ELSE "Cancelled" END) AS status_name'))

                ->where('company_id', auth()->user()->company_id)
                ->where('driver_id', auth()->user()->driver->id);

            /* Date filter */
            if (!empty($request->from_date) && !empty($request->to_date)) {
                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                $withdraw_list = $withdraw_list->whereDate('payment_requests.created_at', '>=', $from_date)->whereDate('payment_requests.created_at', '<=', $to_date);
            }
            $withdraw_list = $withdraw_list->orderBy('id', 'desc')
                ->get();
            $data = $this->getDriverData($withdraw_list);

                return response()->json(['success' => true, 'code' => 200, 'data' => $data]);
           
        } else {
            return response()->json(['success' => false, 'code' => 500, 'error' => 'Only Driver can access']);
        }
    }

    /* Admin login Payment request list and All cash payment made out list */

    public function adminPaymentRequestList(Request $request)
    {
        try {
            $user = auth()->user();
            if ($user->hasRole('company_admin')) {
                $validator = Validator::make($request->all(), [
                    'from_date' => 'nullable|date_format:d-m-Y',
                    'to_date' => 'nullable|date_format:d-m-Y',
                ]);
                if ($validator->fails()) {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'Validation Error', 'error_details' => $validator->errors()]);
                }
                $result = PaymentRequest::select('*', DB::raw('(CASE WHEN payment_requests.request_type = 1 THEN "Full Request" WHEN payment_requests.request_type = 2 THEN "Partial" END) AS request_type_name'), DB::raw('(CASE WHEN status = 1 THEN "Requested" WHEN status = 2 THEN "In Progress" WHEN status = 3 THEN "Success" ELSE "Cancelled" END) AS status_name'))

                    ->where('company_id', auth()->user()->company_id);
                /* Date filter */
                if (!empty($request->from_date) && !empty($request->to_date)) {
                    $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                    $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                    $result->whereDate('payment_requests.created_at', '>=', $from_date)->whereDate('payment_requests.created_at', '<=', $to_date);
                } else {
                    if ($request->request_filter == 1) { //todays filter
                        $result->whereDate('payment_requests.created_at', Carbon::today());
                    } elseif ($request->request_filter == 2) { //this month
                        $result->whereDate('payment_requests.created_at', '>=', Carbon::now()->startOfMonth()->toDateString())->whereDate('payment_requests.created_at', '<=', Carbon::now()->toDateString());
                    } elseif ($request->request_filter == 3) { // 3 month
                        $result->whereDate('payment_requests.created_at', '>=', Carbon::now()->submonths(2)->startOfMonth()->toDateString())
                            ->whereDate('payment_requests.created_at', '<=', Carbon::now()->toDateString());
                    }
                }

                if ($request->type == 1) { // for all cash payment made out
                    $result->where('payment_requests.status', 3);
                } elseif ($request->type == 2) {
                    $result->whereIn('payment_requests.status', [1, 2, 4]);
                } elseif ($request->type == 3 && (isset($request->driver_id))) { //Admin->user manage ->particular drivers payment request detail
                    $result->where('payment_requests.driver_id', $request->driver_id);
                }
                // Advanced Search
                if ($request->driver_name != '') {
                    $driver_name = $request->driver_name;
                    $result = $result->whereHas('driver.user', function ($qry) use ($driver_name) {
                        $qry->where('name', "LIKE", '%' . $driver_name . '%');
                    });
                }
                if ($request->mobile_number != '') {
                    $mobile_number = $request->mobile_number;
                    $result = $result->whereHas('driver.user', function ($qry) use ($mobile_number) {
                        $qry->where('mobile_number', "LIKE", '%' . $mobile_number . '%');
                    });
                }
                if ($request->machine_id != '') {
                    $machine_id = $request->machine_id;
                    $result = $result->whereHas('driver', function ($qry) use ($machine_id) {
                        $qry->where('machine_id', "LIKE", '%' . $machine_id . '%');
                    });
                }
                if ($request->request_id != '') {
                    $request_id = $request->request_id;
                    $result = $result->where('payment_requests.request_id', "LIKE", '%' . $request_id . '%');
                }
                if ($request->request_type != '' && ($request->request_type == 'Full' || $request->request_type == 'Partial')) {
                    $request_type = $request->request_type == 'Full' ? 1 : 2;
                    $result = $result->where('payment_requests.request_type', "=", $request_type);
                }
                if ($request->status != '' && ($request->status == 'New' || $request->status == 'In Progress' || $request->status == 'Success' || $request->status == 'Cancelled')) {
                    $status = $request->status == 'New' ? 1 : ($request->status == 'In Progress' ? 2 : ($request->status == 'Success' ? 3 : 4));
                    $result = $result->where('payment_requests.status', "=", $status);
                }

                $data = $result->orderBy('id', 'desc')
                    ->get();
                $data = $this->getDriverData($data);

                    return response()->json(['success' => true, 'code' => 200, 'data' => $data]);
               
            } else {
                return response()->json(['success' => false, 'code' => 500, 'error' => 'Only Admin can access']);
            }
        } catch (\Exception | \Throwable $e) {
            return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function getDriverData($data)
    {
        $data->transform(function ($item, $key) {
            $item->machine_id = (isset($item->driver->machine_id)) ? $item->driver->machine_id : '';
            $item->profile_image = (isset($item->driver->user->id)) ? getUserProfileImage($item->driver->user->id) : '';
            $item->user_id = (isset($item->driver->user->id)) ? $item->driver->user->id : '';
            $item->driver_name = (isset($item->driver->user->name)) ? $item->driver->user->name : '';
            $item->driver_mobile_number = (isset($item->driver->user->mobile_number)) ? $item->driver->user->mobile_number : '';
            $item->cash_distributor_profile_image = (isset($item->cashDistributor->id)) ? getUserProfileImage($item->cashDistributor->id) : '';
            $item->cash_distributor_name = (isset($item->cashDistributor->name)) ? $item->cashDistributor->name : '';
            $item->cash_distributor_mobile_number = (isset($item->cashDistributor->mobile_number)) ? $item->cashDistributor->mobile_number : '';
            $item->terminal_name = (isset($item->terminal->name)) ? $item->terminal->name : '';
            unset($item->driver);
            unset($item->terminal);
            unset($item->cashDistributor);
            removeMetaColumn($item);
            return $item;
        });
        $data = $data->toArray();

        return $data;
    }

}
