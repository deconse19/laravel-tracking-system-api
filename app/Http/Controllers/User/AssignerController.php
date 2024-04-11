<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\VerifyTaskRequest;
use App\Mail\ApprovalTaskMail;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AssignerController extends Controller
{



    public function addTask(TaskRequest $request)
    {


        $department = Department::find($request->department_id);

        $user = User::find($request->user_id);

        if (!$user->department()->where('id', $department->id)->exists()) {
            return response()->json([
                'message' => 'The specified user does not belong to the specified department.'
            ], 400);
        }

        $task = Task::create([
            'task_assigner_id' => Auth::user()->id,
            'department_id' => $request->input('department_id'),
            'user_id' =>  $request->input('user_id'),
            'task_name' =>  $request->input('task_name'),
            'task_description' =>  $request->input('task_description'),
        ]);

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
        } elseif ($pivotData->submitted_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'This task has not been submitted yet.',
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

    public function showAssignedTasks()
    {

        $assignerId = Auth::user()->id;

        $tasks = Task::where('task_assigner_id', $assignerId)
            ->with(['users' => function ($query) {
                $query->select('users.id', 'users.first_name', 'users.last_name');
            }])
            ->select('tasks.id', 'tasks.task_name', 'tasks.status')
            ->get();

        return response()->json([

            'tasks' => $tasks

        ]);
    }

    // public function showAssigned()
    // {

    //     $user = User::find(Auth::user()->id);
    // }
}
