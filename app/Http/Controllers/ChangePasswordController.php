<?php

// namespace App\Http\Controllers;

// use App\Http\Requests\ChangePasswordRequest;
// use App\Http\Requests\ResetPasswordRequest;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;

// class ChangePasswordController extends Controller
// {
//     public function changePassword(ChangePasswordRequest $request)
//     {

//         $user = User::find(Auth::user()->id);


//         if (Hash::check($request->old_password, $user->password)) {

//             $user->password = bcrypt($request->new_password);
//             $user->save();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Password changed successfully'
//             ]);
//         } else {
//             // Return error response if old password does not match
//             return response()->json([
//                 'message' => 'Old password does not match'
//             ], 422);
//         }
//     }
// }
