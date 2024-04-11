<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddTaskRequest;
use App\Http\Requests\StartTaskRequest;
use App\Http\Requests\SubmitTaskRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\VerifyTaskRequest;
use App\Mail\ApprovalTaskMail;
use App\Mail\TaskVerificationMail;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        Auth::user();

        $task = Task::paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $task

        ], 200);
    }

    public function showCompletedTask(Request $request)
    {

        $task = Task::where('status', 'verified')->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $task

        ], 200);
    }

   

    

        


}
