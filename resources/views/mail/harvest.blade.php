@component('mail::message')
# {{ $subject }}

{{ $body }}

@component('mail::button', ['url' => route('dashboard')])
View Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
