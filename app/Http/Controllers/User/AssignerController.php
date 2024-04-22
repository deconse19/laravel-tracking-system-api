<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteTaskRequest;
use App\Http\Requests\ShowUserRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\VerifyTaskRequest;
use App\Mail\ApprovalTaskMail;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AssignerController extends Controller
{

    public function countStatus()
    {
        Auth::user();

        $pendingCount = Task::where('status', 'pending')->count();
        $inProgressCount = Task::where('status', 'in progress')->count();
        $completedCount = Task::where('status', 'verified')->count();

        return response()->json([
            'pending' => $pendingCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount,

        ]);
    }


    public function specificTask()
    {
        Auth::user();

        $task = Task::with(['users' => function ($query) {
            $query->select('users.id', 'users.first_name', 'users.last_name', 'departments.name as department_name')
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id'); // Assuming first_name and last_name are fields in the users table
        }])
            ->where('id', request('task_id'))
            ->first();

        return $task;
    }

    public function addTask(TaskRequest $request)
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'Assigner') {
            return response()->json([
                'message' => 'You do not have permission to assign tasks.'
            ], 403);
        }

        $assignerFullName = $currentUser->first_name . ' ' . $currentUser->last_name;

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



        Mail::to($user->email)->send(new ApprovalTaskMail($user, $task, $assignerFullName));

        return response()->json([
            'message' => 'Task successfully added',


        ]);
    }

    public function updateTask(TaskRequest $request)
    {

        $currentUser = Auth::user();

        $assignee = null;
        if ($currentUser->role !== 'Assigner') {
            return response()->json([
                'message' => 'You do not have permission to assign tasks.'
            ], 403);
        }
        $task = Task::findOrFail($request->task_id);

        try {
            $department = Department::findOrFail($request->department_id);
            $assignee = $department->users()->findOrFail($request->assignee_id);
        } catch (\Exception $e) {

            $task->update($request->validated());
        }

        if ($currentUser->id !== $task->task_assigner_id) {
            return response()->json([
                'message' => 'You do not have permission to update this task.'
            ], 403);
        }
        if ($task->status === 'completed') {

            return response()->json([

                'message' => 'Task already completed'
            ], 400);
        }
        if ($task->status === 'in progress') {

            return response()->json([

                'message' => 'Task already in progress'
            ], 400);
        }

        $task['task_name'] = $request->task_name;
        $task['task_description'] = $request->task_description;
        $task->save();
        if ($assignee)
            $assignee->tasks()->sync([$task->id]);

        return response()->json([
            'message' => 'Task successfully updated',
        ]);
    }

    public function verifyTask(VerifyTaskRequest $request)
    {
        $task = Task::find($request->task_id);
        $assigner = Auth::user();

        $taskAssigner = Task::where('id', $task->id)
            ->where('task_assigner_id', $assigner->id)
            ->first();

        if (!$taskAssigner) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to verify this task.',
            ], 403);
        }

        $task->update([
            'status' => 'completed'
        ]);

        // dd($task->users()->first());
        $task->users()->updateExistingPivot($task->users()->first(), [
            'verified_at' => now(),
        ]);

        return response()->json([

            'message' => 'Task verified successfully.',
            'success' => true,
        ]);
    }

    public function recentTasks()
    {
        $assignerId = Auth::user()->id;

        $tasks = Task::where('task_assigner_id', $assignerId)
            ->with(['users' => function ($query) {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'departments.id as department_id', 'departments.name as department_name')
                    ->leftJoin('departments', 'users.department_id', '=', 'departments.id');
            }])
            ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function showAssignedAssignees()
    {
        $assignerId = Auth::user()->id;

        $assignees = User::whereHas('tasks', function ($query) use ($assignerId) {
            $query->where('task_assigner_id', $assignerId)
                ->where('status', 'in progress');
        })
            ->with(['department:id,name'])
            ->withCount([
                'tasks as total_tasks_count',
                'tasks as in_progress_tasks_count' => function ($query) use ($assignerId) {
                    $query->where('task_assigner_id', $assignerId)
                        ->where('status', 'in progress');
                }
            ])
            ->select('id', 'first_name', 'last_name', 'email', 'department_id')
            ->get();

        return response()->json([
            'assignees' => $assignees
        ]);
    }

    public function deleteTask(DeleteTaskRequest $request)
    {

        Auth::user();

        $task = Task::find($request->id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }
        if ($task->status === 'in progress') {

            return response()->json([
                'message' => 'task already in progress',
            ]);
        }

        if ($task->status === 'verified') {

            return response()->json([
                'message' => 'task already verified',
            ]);
        }
        $task->delete();

        return response()->json([
            'message' => 'task deleted',

        ]);
    }
    // public function showAssignedAssignees()
    // {
    //     $assignerId = Auth::user()->id;

    //     $assignees = User::whereHas('tasks', function ($query) use ($assignerId) {
    //         $query->where('task_assigner_id', $assignerId)
    //             ->where('status', 'in progress')->count();
    //     })
    //         ->with(['department:id,name'])
    //         ->withCount(['tasks as total_tasks_count', 'tasks as in_progress_tasks_count' => function ($query) use ($assignerId) {
    //             $query->where('task_assigner_id', $assignerId)
    //                 ->where('status', 'in progress');
    //         }])
    //         ->select('id', 'first_name', 'last_name', 'email', 'department_id')
    //         ->get();

    //     return response()->json([
    //         'assignees' => $assignees
    //     ]);
    // }
}
