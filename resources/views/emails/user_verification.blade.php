@component('mail::message')
# Hi There,

Thank you for register with {{ config('app.name') }} account. Please click the below link to verify your email address.

@component('mail::button', ['url' => $data['url']])
Verify Your Email Address
@endcomponent

Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
