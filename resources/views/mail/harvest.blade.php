@component('mail::message')

# {{ $subject }}

{{ $body }}

@component('mail::button', ['url' => config('app.url')])
View Your Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
