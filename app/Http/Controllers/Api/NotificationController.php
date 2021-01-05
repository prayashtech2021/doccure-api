<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class NotificationController extends Controller {

	public function notificationList(Request $request) {
		try {
			$notifications = auth()->user()->notifications;
			$arr = [];
			foreach ($notifications as $key => $notification) {
				$arr[$key]['message'] = $notification->data['message'];
				$arr[$key]['created_at'] = $notification->created_at;
				$arr[$key]['date_diff'] = $notification->created_at->diffForHumans();
			}
			if ($arr) {
				$count = auth()->user()->unreadNotifications()->count();
				return response()->json(['success' => true, 'code' => 200, 'total' => $count, 'data' => $arr]);
			} else {
				return response()->json(['success' => true, 'code' => 200, 'data' => []]);
			}
		} catch (\Exception | \Throwable $e) {
			return response()->json(['success' => false, 'code' => 500, 'error' => $e->getMessage()]);
		}
	}
	public function notificationCount(Request $request) {
		$count = auth()->user()->unreadNotifications()->count();
		return response()->json(['success' => true, 'code' => 200, 'total' => $count]);
	}

	public function markNotificationsAsRead(Request $request) {
		try {
			$user = auth()->user();
			$user->unreadNotifications->markAsRead();
			return response()->json(['status' => true, 'code' => 200, 'message' => 'Marked as read']);
		} catch (\Exception | \Throwable $exception) {
			return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again later.', 'error' => $exception->getMessage()]);
		}
	}
}
