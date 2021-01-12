@component('mail::message')
# Hi There,

Thank you for register with {{ config('app.name') }} account. <br>
Your Verification code is <h2><b>{{$data['verification_code']}}</b></h2>.
@component('mail::button', ['url' => $data['url']])
Verify Your Email Address
@endcomponent
Please click the above link to verify your email address. <br><br>
Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
