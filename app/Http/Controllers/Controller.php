<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Validator;
use Illuminate\Http\Request;

class Controller extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct() {

	}

	public static function customValidation($rules) {
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return response()->json(['success' => false, 'code' => 401, 'error' => $validator->errors()->first(), 'error_details' => $validator->errors()]);
		}
	}

	public static function customDelete($model, $id)
    {
        try {
			$use = "\Api\/".$model;
            $data = $use::withTrashed()->find($id);
            if ($data && $id) {
                if ($data->trashed()) {
                    $data->restore();
                    $data->deleted_by = null;
					$data->save();
					$msg='Record Activated successfully!';
                    session()->flash('success', 'Record Activated successfully!');
                } else {
                    $data->delete();
                    $data->deleted_by =auth()->user()->id;
					$data->save();
					$msg='Record Deleted successfully!';
                    session()->flash('success', 'Record Deleted successfully!');
                }

                return response()->json(['success' => true, 'code' => 200, 'message' => $msg]);
            } else {
                session()->flash('error', 'Sorry try again!');
                return response()->json(['success' => false, 'code' => 401, 'error' => 'Something went wrong, try again!']);
            }
        } catch (\Exception | \Throwable $e) {
            return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);
        }
    }

	/*
		             * =================================================================================================================================================
		             *
		             * API Helper functions
		             *
		             * =================================================================================================================================================
	*/

	/**
	 * @param $data
	 * @return \Illuminate\Http\JsonResponse
	 */

	public static function send_success_response($data, $message = 'OK') {
		$response_array = [
			"code" => "200",
			"message" => $message,
			"data" => $data,
		];

		return response()->json(self::convertNullsAsEmpty($response_array), 200);
	}

	public static function send_unauthorised_request_response($error_message) {
		$response_array = [
			"code" => 401,
			"message" => 'Unauthorized request',
			"data" => ['error' =>
				[
					'user_message' => 'Unauthorized request',
					'internal_message' => $error_message,
					'code' => '1001',
				],
			],
		];

		return response()->json(self::convertNullsAsEmpty($response_array), 401);
	}

	/**
	 * @param $validation_error_message
	 * @return \Illuminate\Http\JsonResponse
	 */
	public static function send_bad_request_response($error_message) {
		$response_array = [
			"code" => 400,
			"message" => $error_message,
			"data" => ['error' =>
				[
					'user_message' => 'Required parameters need to be filled and it must be valid.',
					'internal_message' => $error_message,
					'code' => '1002',
				],
			],
		];

		return response()->json(self::convertNullsAsEmpty($response_array), 400);
	}

	/**
	 * @param $error
	 * @return \Illuminate\Http\JsonResponse
	 */
	public static function send_exception_response($error_message) {
		$response_array = [
			"code" => 500,
			"message" => 'Something went wrong! Please try again later.',
			"data" => ['error' =>
				[
					'user_message' => 'Something went wrong. Kindly report on this.',
					'internal_message' => $error_message,
					'code' => '1003',
				],
			],
		];

		return response()->json(self::convertNullsAsEmpty($response_array), 500);
	}

	/**
	 * @param $error_message
	 * @return \Illuminate\Http\JsonResponse
	 */
	public static function send_access_forbidden_response($error_message) {
		$response_array = [
			"code" => 403,
			"message" => 'Forbidden',
			"data" => ['error' =>
				[
					'user_message' => 'Access forbidden for this request',
					'internal_message' => $error_message,
					'code' => '1004',
				],
			],
		];

		return response()->json(self::convertNullsAsEmpty($response_array), 403);
	}

	public static function send_request_not_found_response() {
		$response_array = [
			"code" => 404,
			"message" => 'Requested url is not found',
			"data" => ['error' =>
				[
					'user_message' => 'Requested url is not found',
					'internal_message' => 'Requested url is not found or invalid request method provided',
					'code' => '1005',
				],
			],
		];

		return response()->json(self::convertNullsAsEmpty($response_array), 404);
	}

	public static function convertNullsAsEmpty($response_array) {

		array_walk_recursive($response_array, function (&$value, $key) {
			$value = is_int($value) ? (string) $value : $value;
			$value = $value === null ? "" : $value;
		});

		return $response_array;
	}
}
