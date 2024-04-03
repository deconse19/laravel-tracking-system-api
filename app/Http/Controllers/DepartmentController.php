<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentListRequest;
use App\Http\Requests\ShowUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $department = Department::get()->all();

        return response()->json([
            'department' => $department
        ]);
    }



    public function showAssignee(ShowUserRequest $request)
    {
        $assignee = User::where('department_id', $request->department_id)
            ->where('role', 'assignee')->get();


        if ($assignee->isEmpty()) {
            return response()->json([
                'message' => 'No user'
            ]);
        }


        return response()->json([
            'list' => $assignee
        ]);
    }

    public function showAssigner(ShowUserRequest $request)
    {
        $assigner = User::where('department_id', $request->department_id)
            ->where('role', 'assigner')->get();


        if ($assigner->isEmpty()) {
            return response()->json([
                'message' => 'No user'
            ]);
        }


        return response()->json([
            'list' => $assigner
        ]);
    }
}
