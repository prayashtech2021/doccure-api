<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class NotificationController extends Controller {

	public function notificationList(Request $request) {
		try {
			$notifications = auth()->user()->notifications;
			
			foreach ($notifications as $key => $notification) {
				$array[] = [
					'message' => $notification->data['message'],
					'created_at' => $notification->created_at,
					'date_diff' => $notification->created_at->diffForHumans(),
					'read_status' => ($notification->read_at != '')? 0 : 1, 
				];
			}
			$count = auth()->user()->unreadNotifications()->count();
			$data = ['list'=>$array,'unread_count'=>$count];
			
			if ($array) {
				return self::send_success_response($data, 'Notification list fetched successfully');
			} else {
				return self::send_success_response($data, 'No Records Found');
			}
		} catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
	}

	public function markNotificationsAsRead(Request $request) {
		try {
			$user = auth()->user();
			$user->unreadNotifications->markAsRead();
			return self::send_success_response([], 'Marked as read');
		} catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage());
        }
	}
}
