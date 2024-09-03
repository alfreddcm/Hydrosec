@component('mail::message')

Hello!

You have received an OTP: {{ $otpcode }}

If you did not create an account, no further action is required.


Thanks,<br>
{{ config('app.name') }}

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. @lang('All Rights Reserved.')
@endcomponent
@endslot
@endcomponent
