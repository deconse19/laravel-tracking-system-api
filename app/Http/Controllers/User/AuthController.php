<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResetTokenRequest;
use App\Http\Requests\SignUpRequest;
use App\Mail\ForgotPasswordMail;
use App\Mail\ForgotPasswordVerification;
use App\Mail\VerificationMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
// use Illuminate\Http\Reques                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   t;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;


/**
 * @group Authentication
 *
 * APIs for authenticating users
 */

class AuthController extends Controller
{
    /**
     * Login
     * 
     * Authenticate a user
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if ($user->email_verified_at === null) {

                return response()->json(['Verify Email First'], 422);
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->sendError('Wrong password.', ['error' => 'Wrong password']);
            }

            $success = $user->createToken('Token')->accessToken;

            return response()->json([
                'message' => 'Successfully login',
                'data' => [
                    $user->first_name,
                    $user->last_name,
                    $user->role

                ],
                'token' => $success
            ], 200);
        } else {

            return response()->json([
                'message' => 'User not authorized'
            ], 401);
        }
    }

    /**
     * Sign Up
     * 
     * Sign up a new user
     *
     * @param SignUpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function signUp(SignUpRequest $request)
    {

        DB::beginTransaction();
        try {
            $user = User::create($request->validated());

            Mail::to($user->email)->send(new VerificationMail($user));

            DB::commit();

            return response()->json([
                'message' => 'Successfully registered',
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([

                'message' => $e->getMessage()

            ], 422);
        }
    }


    /**
     * Forgot Password
     *
     * Generate a password reset token for the given email address.
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::where('email', $request->email)->first();

            $token = Str::random(60);


            PasswordResetToken::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now()
            ]);


            DB::commit();

            Mail::to($user->email)->send(new ForgotPasswordMail($user, $token));


            return response()->json([
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([

                'message' => $e->getMessage()

            ], 422);
        }
    }

    /**
     * 
     * Check Reset Token
     * 
     * Check if the given password reset token is valid.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */


    public function checkResetToken(ResetTokenRequest $request)
    {
        $token = $request->input('token');
        $resetToken = PasswordResetToken::where('token', $token)->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'Invalid token.'
            ], 404);
        }

        if (Carbon::parse($resetToken->created_at)->addHour()->isPast()) {
            return response()->json([
                'message' => 'Token has expired.'
            ], 422);
        }

        if ($resetToken->used) {
            return response()->json([
                'message' => 'Token has already been used.'
            ], 422);
        }

        return response()->json([
            'message' => 'Token is valid.'
        ]);
    }

    /**
     * Reset Password
     *
     * Reset the user's password using the given password reset token.
     *
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function resetPassword(ResetPasswordRequest $request)
    {
        $resetToken = $request->input('token');
        $user = User::where('email', $request->email)->first();


        $tokenRecord = PasswordResetToken::where('email', $user->email)
            ->where('token', $resetToken)
            ->first();

        if (!$tokenRecord || $tokenRecord->used || Carbon::parse($tokenRecord->created_at)->addHour()->isPast()) {
            return response()->json([
                'message' => 'Invalid or expired token.'
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $tokenRecord->used = true;
        $tokenRecord->save();

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ]);
    }

    /**
     * Logout
     * 
     * Logs out the user (removes the access token)
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout()
    {

        Auth::user()->token()->revoke();
        return response([
            'status' => true

        ], 204);
    }
}
