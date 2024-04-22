<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeRoleRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\RestoreUserRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    public function recentTaskDetails()
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'Admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $tasks = Task::select('tasks.id', 'tasks.task_name', 'tasks.task_assigner_id', 'users.first_name as assigner_first_name', 'users.last_name as assigner_last_name')
            ->leftJoin('users', 'tasks.task_assigner_id', '=', 'users.id')
            ->with(['users:id,first_name,last_name'])
            ->get();

        $tasks = $tasks->map(function ($task) {
            $task['assignee'] = $task['users'];
            unset($task['users']);
            return $task;
        });

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Change Role
     * 
     * 
     * Update user role
     *
     * @param int $user_id
     * @param string $role
     * @return \Illuminate\Http\JsonResponse
     */

    public function changeRole(ChangeRoleRequest $request)
    {

        $user = User::find($request->input('user_id'));

        $user->update(['role' => $request->input('role')]);

        return response()->json([

            'message' => 'Role Updated',
            'data' => [

                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' =>  $user->role,
            ]
        ]);
    }

    public function deleteUser(DeleteUserRequest $request)
    {


        $data = User::find($request->user_id);
        $data->delete();

        return response()->json([
            'message' => 'user deleted',

        ]);
    }
    /**
     * Restore a user
     *
     * @param RestoreUserRequest $request
     * @return JsonResponse
     */

    public function restoreUser(RestoreUserRequest $request)
    {

        $data = User::withTrashed()->find($request->user_id);
        $data->restore();

        return response()->json([
            'message' => 'user restored',

        ]);
    }
}
