<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $token = $request->query('token');
      

        return view('passwordreset', compact('token'));
    }
}
