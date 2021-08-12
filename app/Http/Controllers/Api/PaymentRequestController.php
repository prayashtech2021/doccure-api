<?php

namespace App\Http\Controllers\Api;

use App\ActivityLog;
use App\Http\Controllers\Controller;
use App\Notifications\RequestPayment;
use App\PaymentRequest;
use App\User;
use App\AccountDetail;
use Illuminate\Support\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class PaymentRequestController extends Controller
{

    public function list(Request $request)
    {
        $rules = array(
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

            $data = collect();
            $list = PaymentRequest::orderBy('id', $order_by);
            $list->paginate($paginate, ['*'], 'page', $pageNumber)->getCollection()->each(function ($paymentRequest) use (&$data) {
                $data->push($paymentRequest->getData());
            });
            return self::send_success_response($data, 'Payment Request content fetched successfully');
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
        
    }
    
    public function accountUpdate(Request $request)
    {
            $rules = array(
                'account_name' => 'required|string|max:50',
                'account_number' => 'required|string|min:9|max:18|unique:account_details,user_id,'.auth()->user()->id,
                'bank_name' => 'required|string|max:50',
                'branch_name' => 'required|string|max:50',
                'ifsc_code' => 'nullable|string|max:10',
            );
        
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            $account = AccountDetail::where('user_id',auth()->user()->id)->first();
            if (!$account) {
                $account = new AccountDetail();
                $account->created_by = auth()->user()->id;
            }else{
                $account->updated_by = auth()->user()->id;
            }
        $account->user_id = auth()->user()->id;
        $account->account_name = $request->account_name;
        $account->account_number = $request->account_number;
        $account->bank_name = $request->bank_name;
        $account->branch_name = $request->branch_name;
        $account->ifsc_code = $request->ifsc_code;
        $account->save();

         return self::send_success_response([], 'Account deatils saved sucessfully');
        } catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
    }  
    

    public function requestPayment(Request $request)
    {
        $curdate = date('dmYHis');

        try {
            $user = auth()->user();
            if ($user->hasRole(['doctor','patient'])) {
                $rules = array(
                    'request_amount' => 'required|regex:/^\d*(\.\d{1,2})?$/',
                    'description' => 'nullable|string',
                );
                $valid = self::customValidation($request, $rules);
                if ($valid) {return $valid;}

                DB::beginTransaction();

                $user = auth()->user();
                if (!$user) {
                    return self::send_bad_request_response('User not found.');
                }
                if ($user->balanceFloat < $request->request_amount) {
                    return self::send_bad_request_response('No sufficient balance.');
                } elseif ($request->request_amount == 0 || $request->request_amount < 0) {
                    return self::send_bad_request_response('Invalid request amount.');
                }
                $account = AccountDetail::where('user_id',auth()->user()->id)->first();
                if(!$account){
                    return self::send_bad_request_response('Kindly fill user account details before payment request'); 
                }
                
                $request_type = ($user->hasRole('doctor'))?1:2;
                $query = new PaymentRequest();

                $query->user_id = $user->id;
                $query->reference_id = 'PR' . $user->id . '_' . $curdate;
                $query->description = $request->description;
                $query->currency_code = $user->currency_code;
                $query->request_amount = $request->request_amount;
                $query->request_type = $request_type;
                $query->status = 1; //new
                $query->created_by = $user->id;
                $query->save();
                $user->withdrawFloat($request->request_amount);

                DB::commit();

                $user->notify(new RequestPayment($query));
                foreach (User::role(['company_admin'])->get() as $adminUser) {
                    $adminUser->notify(new RequestPayment($query));
                }
                return self::send_success_response([], 'Payment requested successfully!');

            } else {
                return self::send_bad_request_response('Only Provider and Consumer can access');
            }
        } catch (Exception | Throwable $e) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function updatePaymentRequest(Request $request)
    {
        try {
            $user = auth()->user();
            $user_id = auth()->user()->id;
            if ($user->hasRole(['company_admin'])) {
                $rules = array(
                    'payment_request_id' => 'required|numeric|exists:payment_requests,id',
                    'status' => 'required|in:2,3',
                );
                $valid = self::customValidation($request, $rules);
                if ($valid) {return $valid;}
                DB::beginTransaction();

                if ($request->status == 2 || $request->status == 3) {
                    $result = PaymentRequest::find($request->payment_request_id);
                    $user_detail = User::find($result->user_id);
                    $result->status = $request->status;
                    $result->action_date = Carbon::now();
                    if ($request->status == 2) { //only when complete request
                        $new_status = "Paid";
                    }
                    if ($request->status == 3) { //for cancel the request
                        $new_status = "Rejected";
                        $user_detail->depositFloat($result->request_amount);
                    }
                    $result->updated_by = isset(auth()->user()->id) ? auth()->user()->id : '1';
                    $result->save();

                    $user_detail->notify(new RequestPayment($result));

                    // $message = "Status has been changed to " . $new_status . " for " . $result->reference_id;

                } else {
                    return response()->json(['success' => false, 'code' => 401, 'error' => 'Invalid Status Code']);
                }
                DB::commit();
                return self::send_success_response([], 'Status updated successfully!');
            } else {
                return self::send_bad_request_response('Only Admin can access');
            }

        } catch (Exception | Throwable $e) {
            DB::rollBack();
            return self::send_exception_response($exception->getMessage());
        }

    }

   
}
