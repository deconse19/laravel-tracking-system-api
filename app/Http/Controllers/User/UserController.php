<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function showTask()
    {
        $user = User::find(Auth::user()->id);

        $tasks = $user->tasks()->get();
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


    public function updateProfile(UpdateProfileRequest $request)
    {

        Auth::user()->id;
        User::updateOrCreate($request->only([
            'first_name',
            'last_name',
            'contact_number',
            'gender',
            'birthdate',
            'address',
            
            'department_id',
            'position_id',
            'company_id'

        ]));

        return response()->json([
            'message' => 'User successfully updated'
        ], 200);
    }
    public function changePassword(ChangePassRequest $request)
    {

        $user = User::find(Auth::user()->id);


        if (Hash::check($request->old_password, $user->password)) {

            $user->password = bcrypt($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } else {

            return response()->json([
                'message' => 'Old password does not match'
            ], 422);
        }
    }
}
