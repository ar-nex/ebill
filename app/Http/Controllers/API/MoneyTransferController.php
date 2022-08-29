<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MoneyTransferController extends Controller
{
    // userid, amount
    public function directTransfer(Request $request, $user_id)
    {
        $loggedUser = Auth::user();
        if($loggedUser->usertype == 'admin')
        {
            // validate amount
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',   
            ]);
            if($validator->fails())
            {
                return response()->json($validator->errors());
            }
            
            $amount = $request->amount;
            
            $taker = User::find($user_id);
            if($taker == null)
            {
                return response()->json(['status' => false, 'message' => 'Invalid User Id'], 404);
            }

            $lastTransId = 0;
            $lastTrans = Transaction::orderBy('id', 'desc')->first();
            if($lastTrans != null)
            {
                $lastTransId = $lastTrans->id;
            }
            $transactionService = new TransactionService();
            $transactionId = $transactionService->getTransactionId($lastTransId);
            
            if(function_exists('date_default_timezone_set')) {
                date_default_timezone_set("Asia/Kolkata");
            }
            $datetime = date('mdhi');
            $transTag = $transactionService->getTransactionTag($loggedUser->id, $user_id, $datetime);
            DB::transaction(function() use ($user_id, $amount, $transTag, $transactionId, $transactionService, $taker, $loggedUser){
                $takerTrans = Transaction::create([
                    'user_id' => $user_id,
                    'in_amount' => $amount,
                    'tag' => 'TRANSFER',
                    'pairtag' => $transTag,
                    'transaction_id' => $transactionId,
                    'usertype' => $taker->usertype,
                    'code' => 'ADM-TRANSFER',
                    'type' => 'in',
                    'log' => 'Direct transfer by admin to '.$taker->id.'|'.$taker->mobile,
                ]);
                $giverTrans =Transaction::create([
                    'user_id' => $loggedUser->id,
                    'out_amount' => $amount,
                    'tag' => 'TRANSFER',
                    'pairtag' => $transTag,
                    'transaction_id' => $transactionService->getTransactionId($transactionId),
                    'usertype' => $loggedUser->usertype,
                    'code' => 'ADM-TRANSFER',
                    'type' => 'out',
                    'log' => 'Direct transfer by admin to '.$taker->id.'|'.$taker->mobile,
                ]);
            });
            return response()->json(['status' => true, 'message' => 'Successfully transferred']);
        }
        else
        {
            return response()->json(['status' => false, 'message' => 'You are not allowed'], 401);
        }
    }
}
