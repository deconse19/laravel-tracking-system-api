<x-mail::message>
<div style="text-align: center;">
<strong style="font-size: 24px;">You have a new task</strong><br><br><br><br>
</div>

Hey {{$user['first_name']}},<br><br><br>

<strong>{{$task['task_name']}}</strong><br>
{{$task['task_description']}}<br><br><br><br>

Thanks, <br>
{{$assignerFullName}}

<x-mail::button :url="route('verification.verify', ['id' => $user['id']])">
Start Task
</x-mail::button>
</x-mail::message>
