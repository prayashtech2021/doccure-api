<?php

use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\ActivityLog;

function getUserProfileImage($user_id)
{
    $user = User::find($user_id);
    if ($user && !empty($user->profile_image) && Storage::exists('profile-images/' . $user->profile_image)) {
        return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('profile-images/' . $user->profile_image, now()->addMinutes(5)) : Storage::url('profile-images/' . $user->profile_image);

    } else {
        return URL::asset('img/profile_image.jpg');
    }
}
function getDriverBadgeImage($user_id)
{
    $user = User::find($user_id);
    if ($user && !empty($user->driver->tfl_badge_image) && Storage::exists('badge-images/' . $user->driver->tfl_badge_image)) {
        return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('badge-images/' . $user->driver->tfl_badge_image, now()->addMinutes(5)) : Storage::url('badge-images/' . $user->driver->tfl_badge_image);

    } else {
        return URL::asset('img/badge.jpg');
    }
}
function getDriverLicenseImage($user_id)
{
    $user = User::find($user_id);
    if ($user && !empty($user->driver->license_image) && Storage::exists('license-images/' . $user->driver->license_image)) {
        return (config('filesystems.default') == 's3') ? Storage::temporaryUrl('license-images/' . $user->driver->license_image, now()->addMinutes(5)) : Storage::url('license-images/' . $user->driver->license_image);

    } else {
        return URL::asset('img/license.jpg');
    }
}

function removeMetaColumn($model)
{
    $model->makeHidden(['created_by', 'updated_by', 'updated_at', 'deleted_by']);
}
