<x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="route('forgot.password', ['token' => $token])">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
