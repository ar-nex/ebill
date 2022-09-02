<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\MainDefaultCommission;
use App\Models\MainDefaultRate;

class DefaultController extends Controller
{
    public function rate(Request $request)
    {
        $user = Auth::user();
        if($user->usertype != 'admin')
            return response()->json(['error' => 'unauthorized'], 401);

    }

    public function commission(Request $request)
    {
        $user = Auth::user();
        if($user->usertype != 'admin')
            return response()->json(['error' => 'unauthorized'], 401);

    }

    public function password(Request $request)
    {
        $user = Auth::user();
        if($user->usertype != 'admin')
            return response()->json(['error' => 'unauthorized'], 401);
    }
}
