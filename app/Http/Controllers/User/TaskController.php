<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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

        $task = Task::where('status', 'completed')->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $task

        ], 200);
    }

    public function countStatus()
    {

        Auth::user();

        $pending = Task::where('status', 'pending')->count();
        $inprogress = Task::where('status', 'in progress')->count();
        $completed = Task::where('status', 'completed')->count();

        return response()->json([
            'pending' => $pending,
            'inprogress' => $inprogress,
            'completed' => $completed

        ]);
    }
}
