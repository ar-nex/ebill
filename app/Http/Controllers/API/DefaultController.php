<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Illuminate\Validation\Rule;
use App\Models\MainDefaultCommission;
use App\Models\MainDefaultRate;
use Illuminate\Support\Facades\DB;

class DefaultController extends Controller
{
    public function rate(Request $request)
    {
        $user = Auth::user();
        if($user->usertype != 'admin')
            return response()->json(['error' => 'unauthorized'], 401);
        $validator = Validator::make($request->all(), [
            'rates' => 'required',
            'rates.*.byUsertype' => ['required', Rule::in(['sub-admin', 'super', 'distributor', 'retailer'])],
            'rates.*.forUsertype' => ['required', Rule::in(['sub-admin', 'super', 'distributor', 'retailer'])],
            'rates.*.amount' => 'required|numeric|min:0'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }
        // update main default rate
        // if present then update or insert
        $rates = $request->rates;
        foreach ($rates as $rate) {
            $defRate = MainDefaultRate::where('byUsertype', $rate['byUsertype'])->where('forUsertype', $rate['forUsertype'])->first();
            if ($defRate == null) {
                MainDefaultRate::create([
                    'byUsertype' => $rate['byUsertype'],
                    'forUsertype' => $rate['forUsertype'],
                    'amount' => $rate['amount']
                ]);
            } else {
                $defRate->amount = $rate['amount'];
                $defRate->save();
            }
        }
        return response()->json(['success' => true, 'message' => "Rate updated successfully"]);

    }

    public function commission(Request $request)
    {
        $user = Auth::user();
        if($user->usertype != 'admin')
            return response()->json(['error' => 'unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'default_commissions' => 'required',
            'default_commissions.*.service_type' => ['required', Rule::in(['fast', 'slow'])],
            'default_commissions.*.usertype' => ['required', Rule::in(['sub-admin', 'super', 'distributor', 'retailer'])],
            'default_commissions.*.min_range' => 'required|numeric|min:0',
            'default_commissions.*.max_range' => 'required|numeric|min:0',
            'default_commissions.*.percentage' => 'required|numeric|min:0'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }

        $commissions =$request->default_commissions;
        foreach ($commissions as $commission) {
            $whereCon = [
                'service_type' => $commission['service_type'],
                'usertype' => $commission['usertype'],
                'min_range' => $commission['min_range'],
                'max_range' => $commission['max_range'],
            ];
            $defComm = MainDefaultCommission::where($whereCon)->first();
            if ($defComm == null) {
                $defComm = MainDefaultCommission::create([
                    'service_type' => $commission['service_type'],
                    'usertype' => $commission['usertype'],
                    'min_range' => $commission['min_range'],
                    'max_range' => $commission['max_range'],
                    'percentage' => $commission['percentage']
                ]);
            } else {
                 $defComm->percentage = $commission['percentage'];
               $defComm->save();
            }
        }
        return response()->json(['success' => true, 'message' => "Rate updated successfully"]);
    }

    public function password(Request $request)
    {
        $user = Auth::user();
        if($user->usertype != 'admin')
            return response()->json(['error' => 'unauthorized'], 401);
    }
}
