<x-mail::message>
# Task 

Task {{$task['task_name']}} is assigned for you


<x-mail::button :url="route('verification.verify', ['id' => $user['id']])">
View
</x-mail::button>



Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
