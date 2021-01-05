@component('mail::message')
# Hi There,

Thank you for register with {{ config('app.name') }} account. You have completed the registration and your account has been activated once the verification process completed.
 
You will receive an another e-mail, thank you for your patience.

Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
