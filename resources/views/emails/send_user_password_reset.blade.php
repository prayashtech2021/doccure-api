@component('mail::message')
# Hi There,

Please click the following link to reset the password.
 
@component('mail::button', ['url' => $data['url']])
Reset Password
@endcomponent

Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
