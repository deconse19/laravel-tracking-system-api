<x-mail::message>
# Hey {{$user['first_name']}}

<strong>{{$task['task_name']}}</strong><br>
{{$task['task_description']}}



Thanks, <br>
{{$assignerFullName}}

<x-mail::button :url="route('verification.verify', ['id' => $user['id']])">
Start Task
</x-mail::button>
</x-mail::message>