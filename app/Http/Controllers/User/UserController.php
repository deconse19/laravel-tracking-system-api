<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassRequest;
use App\Http\Requests\ChangeRoleRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\RestoreUserRequest;
use App\Http\Requests\ShowUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group User Controller
 *
 * APIs for users
 */

class UserController extends Controller
{
    /**
     * Show Assignee
     * 
     * Returns a list of users who have the "assignee" role in the specified department
     *
     * @param ShowUserRequest $request
     * @return JsonResponse
     */

    public function showAssignee(ShowUserRequest $request)
    {
        $assignee = User::where('department_id', $request->department_id)
            ->where('role', 'assignee')->get();


        if ($assignee->isEmpty()) {
            return response()->json([
                'message' => 'No user'
            ]);
        }

        $data = [];
        foreach ($assignee as $user) {
            $data[] = [
                'id' => $user->id,
                'fullname' => $user->first_name . ' ' . $user->last_name
            ];
        }
        return response()->json($data);
    }

    /**
     * 
     * Show Assigner
     * 
     * Returns a list of users who have the "assigner" role in the specified department
     *
     * @param ShowUserRequest $request
     * @return JsonResponse
     */


    public function showAssigner(ShowUserRequest $request)
    {
        $assigner = User::where('department_id', $request->department_id)
            ->where('role', 'assigner')->get();

        if ($assigner->isEmpty()) {
            return response()->json([
                'message' => 'No user'
            ]);
        }

        return response()->json([
            'list' => $assigner
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
    /**
     * 
     * Update user profile
     *
     * Update the authenticated user's profile information.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */



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

    /**
     * Change Password
     *
     * Update the authenticated user's password.
     *
     * @param ChangePassRequest $request
     * @return JsonResponse
     */

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
    /**
     * Deactivate User
     *
     * Deactivate a user by id
     *
     * @param int $user_id
     * @return \Illuminate\Http\JsonResponse
     */

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
