@component('mail::message')
# Hi There,

Your password has been changed, you can login using your e-mail address and password="{{$data['pass']}}".


Regards,<br>
<span style="color:#11cdef;">{{ config('app.name') }} Team</span><br>
<small>{{ config('custom.support_phone') }} <br> {{ config('custom.support_email') }}</small>
@endcomponent
