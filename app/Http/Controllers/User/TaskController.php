<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddTaskRequest;
use App\Http\Requests\StartTaskRequest;
use App\Http\Requests\SubmitTaskRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\VerifyTaskRequest;
use App\Mail\ApprovalTaskMail;
use App\Mail\TaskVerificationMail;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

    public function showCompletedTask(Request $request)
    {

        $task = Task::where('status', 'verified')->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $task

        ], 200);
    }

    public function addTask(TaskRequest $request)
    {
        $department = Department::find($request->department_id);

        $user = User::find($request->user_id);

        if (!$user->department()->where('id', $department->id)->exists()) {
            return response()->json([
                'message' => 'The specified user does not belong to the specified department.'
            ], 400);
        }

        $task = Task::create($request->validated());

        $user->tasks()->attach($task);

        Mail::to($user->email)->send(new ApprovalTaskMail($user, $task));

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

    public function startTask(StartTaskRequest $request)
    {
        $user = User::find($request->user_id);
        $task = Task::find($request->task_id);

        $pivotData = $user->tasks()->where('task_id', $request->task_id)->first()->pivot;

        if ($pivotData->started_at !== null) {

            return response()->json([
                'message' => 'This task is already been taken',
            ], 400);
        }

        $task->update([
            'status' => 'in progress',
        ]);

        $user->tasks()->updateExistingPivot($task, [
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Task started',
            'success' => true,
        ]);
    }


    public function submitTask(SubmitTaskRequest $request)
    {
        $task = Task::find($request->task_id);
        $user = User::find($request->user_id);
        $taskAssigner = User::find($request->task_assigner_id);


        $pivotData = $user->tasks()->where('task_id', $request->task_id)->first()->pivot;

        if ($pivotData->started_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'This task is not started yet.',
            ], 400);
        }

        if ($pivotData->submitted_at !== null) {

            return response()->json([
                'message' => 'This task is already been submitted',
            ], 400);
        }


        if (!$taskAssigner || $taskAssigner->id !== $task->task_assigner_id) {

            return response()->json([
                'success' => false,
                'message' => 'The task assigner ID is not associated with the specified task.',
            ], 400);
        }

        $user->tasks()->updateExistingPivot($task, [
            'submitted_at' => now(),
        ]);

        $task->update([
            'status' => 'submitted'
        ]);
        Mail::to($taskAssigner->email)->send(new TaskVerificationMail($user));

        return response()->json([
            'message' => 'Task submitted',
            'success' => true,
        ]);
    }

    public function verifyTask(VerifyTaskRequest $request)
    {
        $task = Task::find($request->task_id);
        $user = User::find($request->user_id);

        $pivotData = $user->tasks()->where('task_id', $request->task_id)->first()->pivot;

        if ($pivotData->started_at === null && $pivotData->submitted_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'This task is not started yet.',
            ], 400);
        }

        if ($pivotData->verified_at !== null) {

            return response()->json([
                'message' => 'This task is already been verified',
            ], 400);
        }


        $user->tasks()->updateExistingPivot($task, [
            'verified_at' => now(),
        ]);

        $task->update([
            'status' => 'verified'
        ]);
        return response()->json([
            'message' => 'Task verified',
            'success' => true,
        ]);
    }


}
