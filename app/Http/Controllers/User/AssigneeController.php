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
    public function countStatus()
    {
        $currentUser = Auth::user();

        $userTasks = $currentUser->tasks()->get();

        // dd($userTasks);
        $pendingCount = $userTasks->where('status', 'pending')->count();
        $inProgressCount = $userTasks->where('status', 'in progress')->count();
        $completedCount = $userTasks->where('status', 'verified')->count();

        return response()->json([
            'pending' => $pendingCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount,
            'id' => $currentUser->id
        ]);
    }

    public function showAssigneeTasks()
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'Assignee') {

            return response()->json([
                'message' => 'You are not an assignee'
            ], 401);
        }
        $tasks = $currentUser->tasks;

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'You have no tasks yet'
            ], 401);
        }

        $tasksData = [];

        foreach ($tasks as $task) {

            $taskAssigner =  User::find($task->task_assigner_id);

            if ($taskAssigner) {
                $tasksData[] = [
                    'id' => $task->id,
                    'task_name' => $task->task_name,
                    'task_description' => $task->task_description,
                    'task_assigner_id' => $task->task_assigner_id,
                    'task_assigner_first_name' => $taskAssigner->first_name,
                    'task_assigner_last_name' => $taskAssigner->last_name,
                    'created_at' => $task->created_at->toDateTimeString(),
                    'status' => $task->status,

                ];
            }
        }

        return response()->json([
            'tasks' => $tasksData,
        ]);
    }

    public function startTask(StartTaskRequest $request)
    {

        Auth::user();

        // if ($currentUser->role !== 'Assignee') {

        //     return response()->json([
        //         'message' => 'You are not an assignee'
        //     ], 401);
        // }
        $task = Task::find($request->task_id);
        // $pivotData = $currentUser->tasks()->where('task_id', $request->task_id)->first()->pivot;

        $pivotTimeData = $task->users()->first()->pivot;

        if ($pivotTimeData->started_at !== null) {

            return response()->json([
                'message' => 'This task is already been taken',
            ], 400);
        }

        $task->update([
            'status' => 'in progress',
        ]);

        $task->users()->updateExistingPivot($task->users()->first(), [
            'started_at' => now()
        ]);

        return response()->json([
            'message' => 'Task started',
            'success' => true,
        ]);
    }
    public function submitTask(SubmitTaskRequest $request)
    {
        $currentUser = Auth::user();
        $task = Task::find($request->task_id);

        $taskAssigner = User::find($task->task_assigner_id);

        // dd($task->users()->first()->pivot);
        $task->users()->updateExistingPivot($task->users()->first(), [
            'submitted_at' => now()
        ]);

        $pivotTimeData = $task->users()->first()->pivot;

        if ($pivotTimeData->started_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'This task is not started yet.',
            ], 400);
        }


        if (!$taskAssigner || $taskAssigner->id !== $task->task_assigner_id) {

            return response()->json([
                'success' => false,
                'message' => 'The task assigner ID is not associated with the specified task.',
            ], 400);
        }

        $currentUser->tasks()->updateExistingPivot($task, [
            'submitted_at' => now(),
        ]);

        $task->update([
            'status' => 'submitted'
        ]);
        Mail::to($taskAssigner->email)->send(new TaskVerificationMail($taskAssigner,$task));

        return response()->json([
            'message' => 'Task submitted',
            'success' => true,
        ]);
    }
}
