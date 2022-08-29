<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinController extends Controller
{
    public function get($pin)
    {
       // DB::enableQueryLog();
        $pins = \App\Models\Pin::where('pincode', $pin)->get();
        if($pins)
        {
            $district = $pins[0]->district;
            $state = $pins[0]->state;
            $offices = [];
            foreach ($pins as $key => $value) {
                array_push($offices, $value->office);
            }
            return response()->json(['offices' => $offices, 'district' => $district, 'state' => $state]);
        }
        else
        {
            return response()->json(['message' => 'Pin not found'], 404);
        }
    }
}
