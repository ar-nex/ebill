<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\TransactionRepository;
use Auth;

class DashboardController extends Controller
{
 
    public function get()
    {
        $userRepository = new UserRepository();
        $transactionRepository = new TransactionRepository();

        $user = Auth::user();
        switch ($user->usertype) {
            case 'admin':

                
                $retailer_count = $userRepository->getCountByUserType('retailer');
                $distributor_count = $userRepository->getCountByUserType('distributor');
                $super_count = $userRepository->getCountByUserType('super');
                
                $retailer_balance = $transactionRepository->getBalanceByUserType('retailer');
                $distributor_balance = $transactionRepository->getBalanceByUserType('distributor');
                $super_balance = $transactionRepository->getBalanceByUserType('super');

                $self_balance = $transactionRepository->getBalance($user->id);
                
                return response()->json([
                    'retailer_count' => $retailer_count, 
                    'distributor_count' => $distributor_count, 
                    'super_count' => $super_count, 
                    'retailer_balance' => $retailer_balance, 
                    'distributor_balance' => $distributor_balance, 
                    'super_balance' => $super_balance, 
                    'self_balance' => $self_balance]);

                break;
            case 'super':
                # code...
                break;
            case 'distributor':
                # code...
                break;
            case 'retailer':
                # code...
                break;
            default:
                # code...
                break;
        }
    }
}
