@component('mail::message')
# Hi There,

An account has been created for you in {{ config('app.name') }}. You can use the below information to login to your account.

Email: {{$data['email']}}
Password: {{$data['password']}}

@component('mail::button', ['url' => $data['url']])
Click here to Login
@endcomponent

Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
