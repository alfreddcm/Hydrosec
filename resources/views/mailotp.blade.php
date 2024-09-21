@component('mail::message')

Hello!

You have received an OTP:
<div style="text-align: center; font-size: 24px; font-weight: bold;">
    @foreach(str_split($otpcode) as $digit)
        <span style="margin: 0 5px;">{{ $digit }}</span>
    @endforeach
</div><br>

If you did not request this OTP, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. @lang('All Rights Reserved.')
@endcomponent
@endslot
@endcomponent
