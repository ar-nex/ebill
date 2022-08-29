<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\UserMap;
use App\Models\Commission;
use App\Models\Rate;
use App\Models\Transaction;
use App\Models\DefaultRate;
use App\Models\DefaultCommission;
use App\Models\Config;
use Illuminate\Support\Facades\Hash;
use App\Repositories\TransactionRepository;
use App\Services\TransactionService;


use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $parent = Auth::user();
        $commonValidatorArray = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|unique:users|min:10|max:10',
            'shopname' => 'nullable',
            'state' => 'required',
            'district' => 'required',
            'postoffice' => 'nullable',
            'pin' => 'required|min:6|max:6',
            'usertype' => ['required', Rule::in(['sub-admin', 'super', 'distributor', 'retailer'])],
        ];
        $onlyAdminValidatorArray = [
            'commissions' => 'required',
            'commissions.*.service_type' => ['required', Rule::in(['fast', 'slow'])],
            'commissions.*.min_range' => 'required|integer|min:0',
            'commissions.*.max_range' => 'required|integer|min:0',
            'commissions.*.percentage' => 'required|numeric|min:0',
            'default_commissions.*.service_type' => ['required', Rule::in(['fast', 'slow'])],
            'default_commissions.*.usertype' => ['required', Rule::in(['super', 'distributor', 'retailer'])],
            'default_commissions.*.min_range' => 'required|integer|min:0',
            'default_commissions.*.max_range' => 'required|integer|min:0',
            'default_commissions.*.percentage' => 'required|numeric|min:0',
            'rates' => 'required',
            'rates.*.usertype' => ['required', Rule::in(['sub-admin', 'super', 'distributor', 'retailer'])],
            'rates.*.amount' => 'required|numeric|min:0',
            'default_rates.*.usertype' => ['required', Rule::in(['super', 'distributor', 'retailer'])],
            'default_rates.*.amount' => 'required|numeric|min:0',
        ];
        $validatorArray = $commonValidatorArray;
        if($parent->usertype == 'admin')
        {
            $validatorArray = array_merge($validatorArray, $onlyAdminValidatorArray);
            
        }

        $validator = Validator::make($request->all(), $validatorArray);
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $rate = new Rate();
        // if not admin get rate and balance
        if($parent->usertype != "admin")
        {
            $rate = Rate::where('user_id', $parent->id)->where('usertype', $request->usertype)->first();
            if($rate == null)
            {
                return response()->json(['status' => false, 'message' => 'rate has not been set'], 403);
            }
            $transactionRepository = new TransactionRepository();
            $balance = $transactionRepository->getBalance($parent->id);
            if($balance < $rate->amount)
            {
                return response()->json(['status' => false, 'message' => 'Insufficient balance'], 403);
            }
        }


        // save user
        // save mapper
        // save commissions
        // save default commission if admin
        DB::transaction(function() use($request, $parent, $rate){
            $password = Config::where('key', 'DEFAULT_PASSWORD')->first()->value;
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'shopname' => $request->shopname,
                'state' => $request->state,
                'district' => $request->district,
                'postoffice' => $request->postoffice,
                'pin' => $request->pin,
                'usertype' => $request->usertype,
                'password' => Hash::make($password),
                'pp' => $password,
                'is_active' => true,
            ]);
            $parent = Auth::user();
            $usermap = UserMap::create([
                'user_id' => $user->id,
                'parent_id' => $parent->id,
                'usertype' => $request->usertype,
                'parenttype' => $parent->usertype,
            ]);
            if($parent->usertype == 'admin')
            {
                $commissions = $request->commissions;
                foreach($commissions as $commission)
                {
                    Commission::create([
                        'user_id' => $user->id,
                        'service_type' => $commission['service_type'],
                        'min_range' => $commission['min_range'],
                        'max_range' => $commission['max_range'],
                        'percentage' => $commission['percentage'],
                    ]);
                }
                $default_commissions = $request->default_commissions;
                foreach($default_commissions as $default_commission)
                {
                    DefaultCommission::create([
                        'parent_id' => $user->id,
                        'service_type' => $default_commission['service_type'],
                        'usertype' => $default_commission['usertype'],
                        'min_range' => $default_commission['min_range'],
                        'max_range' => $default_commission['max_range'],
                        'percentage' => $default_commission['percentage'],
                    ]);
                }

                // rate two types. 1. Own rate 2. default child rate
                $rates = $request->rates;
                foreach($rates as $rate)
                {
                    Rate::create([
                        'user_id' => $user->id,
                        'usertype' => $rate['usertype'],
                        'amount' => $rate['amount'],
                    ]);
                }
                $default_rates = $request->default_rates;
                foreach($default_rates as $default_rate)
                {
                    DefaultRate::create([
                        'parent_id' => $user->id,
                        'usertype' => $default_rate['usertype'],
                        'amount' => $default_rate['amount'],
                    ]);
                }
            }
            else
            {
                // get default commission for new user type
                $default_commission = DefaultCommission::where('usertype', $request->usertype)
                ->where('parent_id', $parent->id)->get();
                foreach ($default_commission as $commission) {
                    Commission::create([
                        'user_id' => $user->id,
                        'service_type' => $commission['service_type'],
                        'min_range' => $commission['min_range'],
                        'max_range' => $commission['max_range'],
                        'percentage' => $commission['percentage'],
                    ]);
                }

                // rate
                // own rate from table
                $default_rate = DefaultRate::where('usertype', $request->usertype)->where('parent_id', $parent->id)->get();
                foreach ($default_rate as $rate) {
                    Rate::create([
                        'user_id' => $user->id,
                        'usertype' => $rate['usertype'],
                        'amount' => $rate['amount'],
                    ]);
                }

                // last transaction
                $lastTrans = Transaction::orderBy('id', 'desc')->first();
                $lastTransId = $lastTransaction != null ? $lastTrans->transaction_id : 0;
                
                $transactionService = new TransactionService();
                $transactionId = $transactionService->getTransactionId($lastTransId);
                // deduct balance amounting rate
                $transaction = Transaction::create([
                    'transaction_id'=> $transactionId,
                    'usertype' => $parent->usertype,
                    'tag' => 'UCREATE-'.$user->id,
                    'code' => 'UCREATE',
                    'type' => 'out',
                    'out_amount' => $rate->amount,
                    'log' => $request->usertype." creation by ".$parent->mobile." User Id: ".$user->mobile,
                    'user_id' => $parent->id
                ]);
            }
        });
        return response()->json(['success' => true]);
    }


}
