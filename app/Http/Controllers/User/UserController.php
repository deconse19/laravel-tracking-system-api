<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request){

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
            'success' => true,
            'message' => 'User successfully updated'
        ]);
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
            // Return error response if old password does not match
            return response()->json([
                'message' => 'Old password does not match'
            ], 422);
        }
    }

}
