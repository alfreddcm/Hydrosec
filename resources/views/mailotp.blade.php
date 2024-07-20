@component('mail::message')

Hello!

You have received an OTP: {{ $otpcode }}

@component('mail::button', ['url' => 'https://www.google.com'])
Click here
@endcomponent
Thanks,<br>
{{ config('app.name') }}

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. @lang('All Rights Reserved.')
@endcomponent
@endslot
@endcomponent
