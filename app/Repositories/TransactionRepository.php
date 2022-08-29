<?php
namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\UserMap;

class TransactionRepository
{
    public function getAll()
    {
        return Transaction::all();
    }

    public function getById($id)
    {
        return Transaction::find($id);
    }

    public function getByUserId($userId)
    {
        return Transaction::where('user_id', $userId)->get();
    }

    public function getByUserType($type)
    {
        return Transaction::where('type', $type)->get();
    }

    public function getBalance($userId)
    {
        $total_in = Transaction::where('user_id', $userId)->where('type', 'in')->sum('in_amount');
        $total_out = Transaction::where('user_id', $userId)->where('type', 'out')->sum('out_amount');
        return $total_in - $total_out;
    }

    public function getBalanceByUserType($type)
    {
        $total_in = Transaction::where('type', 'in')->where('usertype', $type)->sum('in_amount');
        $total_out = Transaction::where('type', 'out')->where('usertype', $type)->sum('out_amount');
        return $total_in - $total_out;
    }

    public function giveCommission($taker_id, $giver_id, $amount)
    {
        
    }
}    