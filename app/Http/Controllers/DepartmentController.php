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

    /**
     * Returns a list of departments
     *
     * @return array
     */
    public function index()
    {
        $department = Department::get()->all();

        return response()->json([
            'department' => $department
        ]);
    }
}
