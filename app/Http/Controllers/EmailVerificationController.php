<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify($id)
    {
        User::findOrFail($id)->update(['email_verified_at' => now()]);

        return view('success');
    }
}