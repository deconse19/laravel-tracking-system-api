<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something gr  eat!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/signup', [AuthController::class,  'signUp']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
    Route::post('profile/update', [UserController::class, 'updateProfile']);
});

Route::group(['prefix' => 'password'], function () {
    Route::post('change', [UserController::class, 'changePassword'])->middleware('auth:api');
    Route::post('forgot', [AuthController::class, 'forgotPassword']);
    Route::post('check', [AuthController::class, 'checkResetToken']);
    Route::post('reset', [AuthController::class, 'resetPassword']);
});



Route::group(['prefix' => 'task', 'middleware' => 'auth:api', 'throttle:60,1'], function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::post('add', [TaskController::class, 'addTask']);
    Route::post('update', [TaskController::class, 'updateTask']);
});
