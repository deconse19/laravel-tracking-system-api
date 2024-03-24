<x-mail::message>
# Email Verification

Welcome {{$user['first_name']}},

<x-mail::button :url="route('verification.verify', ['id' => $user['id']])">
Verify
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
