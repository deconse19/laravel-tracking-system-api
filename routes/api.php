<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
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

Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/positions', [PositionController::class, 'index']);



Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
    Route::get('/task', [UserController::class, 'showTask']);
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
    Route::get('/complete', [TaskController::class, 'showCompletedTask']);
    Route::post('add', [TaskController::class, 'addTask']);
    Route::post('update', [TaskController::class, 'updateTask']);

    Route::post('/start', [TaskController::class, 'startTask']);
    Route::post('/submit', [TaskController::class, 'submitTask']);
    Route::post('/verify', [TaskController::class, 'verifyTask']);
});


Route::group(['prefix' => 'department', 'middleware' => 'auth:api', 'throttle:60,1'], function () {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::post('/assignee', [DepartmentController::class, 'showAssignee']);
    
    Route::post('/assigner', [DepartmentController::class, 'showAssigner']);
    // Route::post('add', [TaskController::class, 'addTask']);
    // Route::post('update', [TaskController::class, 'updateTask']);
});

Route::group(['prefix' => 'position', 'throttle:60,1'], function () {
    Route::get('/show', [PositionController::class, 'showPosition']);
});