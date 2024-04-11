<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Returns a list of all positions
     *
     * @return array
     */

    public function index(Request $request)
    {

        $position = Position::get()->all();

        return response()->json([
            'position' => $position
        ]);
    }
}
