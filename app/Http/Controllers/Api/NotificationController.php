<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class NotificationController extends Controller {

	public function notificationList(Request $request) {
		$common = [];
        $lang_id = ($request->language_id)? getLang($request->language_id) : defaultLang();
        $common['header'] = getLangContent(8,$lang_id);
        $common['setting'] = getSettingData();
        $common['menu'] = getAppMenu();
        $common['lang_content'] = getLangContent(21,$lang_id);
        $common['footer'] = getLangContent(9,$lang_id);

		try {
			if ($request->language_id) {
                $rules['language_id'] = 'integer|exists:languages,id';
				
            	$valid = self::customValidation($request, $rules,$common);
            	if ($valid) {return $valid;}
			}
			$notifications = auth()->user()->notifications;
			$array = [];
			
			foreach ($notifications as $key => $notification) {
				$notytime =convertToLocal(Carbon::parse($notification->created_at), auth()->user()->time_zone);
				$array[] = [
					'message' => $notification->data['message'],
					'created_at' => $notytime->format('Y-m-d H:i:s'),
					'date_diff' => $notytime->diffForHumans(),
					'read_status' => ($notification->read_at != '')? 0 : 1, 
				];
			}
			$count = auth()->user()->unreadNotifications()->count();
			$data = [
				'list'=>$array,
				'unread_count'=>$count,
				'total_count' => count($array),
			];
			
			($array)? $msg = 'Notification list fetched successfully' : $msg = 'No Records Found';
			
			return self::send_success_response($data,$msg,$common);
			
		} catch (Exception | Throwable $e) {
            return self::send_exception_response($exception->getMessage(),$common);
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
