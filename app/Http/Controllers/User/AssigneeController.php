<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartTaskRequest;
use App\Http\Requests\SubmitTaskRequest;
use App\Mail\TaskVerificationMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AssigneeController extends Controller
{

    
    public function showAssigneeTasks()
    {
        $user = Auth::user();

        $tasks = $user->tasks;
        $tasksData = [];

        foreach ($tasks as $task) {

            $taskAssigner =  User::find($task->task_assigner_id);


            if ($taskAssigner) {
                $tasksData[] = [
                    'id' => $task->id,
                    'task_name' => $task->task_name,
                    'task_description' => $task->task_description,
                    'status' => $task->status,
                    'task_assigner_id' => $task->task_assigner_id,
                    'task_assigner_first_name' => $taskAssigner->first_name,
                    'task_assigner_last_name' => $taskAssigner->last_name,

                ];
            }
        }

        return response()->json([
            'tasks' => $tasksData,
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
        $user = User::find($request->user_id);
        $task = Task::find($request->task_id);
        
        $taskAssigner = User::find($task->task_assigner_id);


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


   
}
