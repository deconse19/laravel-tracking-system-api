<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\User\AdminController;
use App\Http\Controllers\User\AssigneeController;
use App\Http\Controllers\User\AssignerController;
use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;



Route::post('/signup', [AuthController::class,  'signUp']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/positions', [PositionController::class, 'index']);



Route::group(['prefix' => 'password'], function () {

    Route::post('forgot', [AuthController::class, 'forgotPassword']);
    Route::post('check', [AuthController::class, 'checkResetToken']);
    Route::post('reset', [AuthController::class, 'resetPassword']);
});

Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {

    Route::group(['prefix' => 'assignee', 'middleware' => 'auth:api'], function () {
        Route::get('/tasks', [AssigneeController::class, 'showAssigneeTasks']);
        Route::post('/task/start', [AssigneeController::class, 'startTask']);
        Route::post('/task/submit', [AssigneeController::class, 'submitTask']);
        Route::get('/tasks/counter', [AssigneeController::class, 'countStatus']);
    });

    Route::group(['prefix' => 'assigner', 'middleware' => 'auth:api'], function () {
        Route::post('/task/specific', [AssignerController::class, 'specificTask']);
        Route::post('/task/delete', [AssignerController::class, 'deleteTask']);

        Route::get('/tasks/counter', [AssignerController::class, 'countStatus']);
        Route::post('/task/verify', [AssignerController::class, 'verifyTask']);
        Route::get('/recent-tasks', [AssignerController::class, 'recentTasks']);
        Route::get('/assignees', [AssignerController::class, 'showAssignedAssignees']);
        Route::post('add', [AssignerController::class, 'addTask']);
        Route::post('update', [AssignerController::class, 'updateTask']);
    });

    Route::group(['prefix' => 'admin', 'middleware' => 'auth:api'], function () {
        Route::get('/recent-tasks', [AdminController::class, 'recentTaskDetails']);
        Route::post('/change-role', [AdminController::class, 'changeRole']);
        Route::post('/delete', [AdminController::class, 'deleteUser']);
        Route::post('/restore', [AdminController::class, 'restoreUser']);
    });

    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/password/change', [UserController::class, 'changePassword']);
});



Route::group(['prefix' => 'task', 'middleware' => 'auth:api', 'throttle:60,1'], function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::get('/show-complete', [TaskController::class, 'showCompletedTask']);
    Route::get('task-counter', [TaskController::class, 'countStatus']);
});


Route::group(['prefix' => 'department', 'middleware' => 'auth:api', 'throttle:60,1'], function () {
    Route::get('/', [DepartmentController::class, 'index']);

    Route::post('/assignee', [UserController::class, 'showAssignee']);
    Route::post('/assigner', [UserController::class, 'showAssigner']);
});

Route::group(['prefix' => 'position', 'throttle:60,1'], function () {
    Route::get('/show', [PositionController::class, 'showPosition']);
});
