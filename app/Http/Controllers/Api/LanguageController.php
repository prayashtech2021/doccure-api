<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Language;

class LanguageController extends Controller
{
    public function list(Request $request)
    {
        try {
            $language = Language::orderBy('name')->get();
            removeMetaColumn($language);
            return self::send_success_response($language);

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function update(Request $request)
    {
        $rules = self::customValidation($request, [
            'language_id' => 'required|exists:languages,id',
        ]);
        if($rules){ return $rules;}
        try {
            $user = auth()->user();
            if($user->language_id != $request->language_id){
                $user->language_id = $request->language_id;
                $user->save();
                removeMetaColumn($user);
                return self::send_success_response($user,'User Language updated successfully');
            }
            removeMetaColumn($user);
            return self::send_success_response($user,'Already exist');

        } catch (Exception | Throwable $exception) {
            return self::send_exception_response($exception->getMessage());
        }
    }
}
