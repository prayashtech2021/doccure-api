@component('mail::message')
# Hi There,

Your account has been activated, kindly use your e-mail address and password to login.
 
@component('mail::button', ['url' => $data['url']])
Click here to Login
@endcomponent

Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
