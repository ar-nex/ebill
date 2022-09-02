<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Auth;
use Validator;
use App\Models\User;
use App\Models\Config;
use App\Models\UserMap;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|unique:users',
            'shopname' => 'required',
            'state' => 'required',
            'district' => 'required',
           // 'postoffice' => 'required',
            'pin' => 'required',
            'usertype' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }
        DB::transaction(function() use ($request){
            $password = Config::where('key', 'DEFAULT_PASSWORD')->first()->value;
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'shopname' => $request->shopname,
                'state' => $request->state,
                'district' => $request->district,
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
        });
        return response()
            ->json(['status' => 1,'message' => 'User added successfully']);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('mobile', 'password')))
        {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('mobile', $request['mobile'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()
            ->json([
                'success' => true,
                'name' => $user->name,
                'usertype' => $user->usertype,
                'id' => $user->id,
                'message' => 'Hi '.$user->name.', welcome to home',
                'access_token' => $token, 
                'token_type' => 'Bearer', 
            ]);
    }
}
