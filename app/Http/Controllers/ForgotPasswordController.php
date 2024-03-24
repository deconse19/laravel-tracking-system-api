<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        // Retrieve the token from the request query parameters
        $token = $request->query('token');

        // You can add additional logic here if needed, such as validating the token
        // against the user's record or checking if the token is valid

        // Assuming the token is valid, you can display a success message
        $successMessage = "Your password reset was successful. Please enter your new password.";

        // Pass the success message and token to the view
        return view('passwordreset', compact('successMessage', 'token'));
    }
}
