<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddTaskRequest;
use App\Http\Requests\TaskRequest;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        Auth::user();

        $task = Task::paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $task

        ], 200);
    }

    public function addTask(TaskRequest $request)
    {
        $department = Department::find($request->department_id);
        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'The specified department does not exist.'
            ], 404);
        }

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'The specified user does not exist.'
            ], 404);
        }


        if (!$user->department()->where('id', $department->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'The specified user does not belong to the specified department.'
            ], 400);
        }

        $task = Task::create($request->validated());

        $user->tasks()->attach($task);

        return response()->json([
            'success' => true,
        ]);
    }




    public function updateTask(TaskRequest $request)
    {

        $departmnet = Department::findOrFail($request->department_id);

        $user = $departmnet->users()->findOrFail($request->user_id);

        $task = Task::findOrFail($request->task_id);
        $task->update($request->validated());

        $user->tasks()->sync([$task->id]);

        return response()->json([
            'success' => true,
        ]);
    }
}
