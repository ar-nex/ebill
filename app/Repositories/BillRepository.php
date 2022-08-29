<?php
namespace App\Repositories;

use App\Models\BillRequest;
use App\Models\User;
use App\Models\UserMap;
use App\Models\Transaction;
use App\Services\TransactionService;
use Auth;
use Illuminate\Support\Facades\DB;

class BillRepository
{
    function updateStatus($id, $status)
    {
        $bill = BillRequest::find($id);
        if ($bill) {
            if ($status == 1) {
                if($bill->status == 1){
                    return false;
                }
                // pay commission
                $resp = $this->payCommission($bill);
                return $resp;
            } 
            else if($status == 2)
            {
                // cancel commission
            }
            else if($status == 0)
            {
                return false;
            }
            else {
                return false;
            }
            $bill->save();
            return true;
        }
        else
        {
            return false;
        }
    }

    function payCommission($bill)
    {
        // get applier
        $applier = User::find($bill->user_id);
        $admin = Auth::user();
        $transactionService = new TransactionService();

        $lastTransaction = Transaction::orderBy('id', 'desc')->first();
        $commission_rate = $applier->commission;
        $commission = $bill->amount * $commission_rate / 100;

        switch ($applier->usertype) {
            case 'super':
                $transactionTag = $transactionService->getTransactionTag($admin->id, $applier->id, $bill->id);
                DB::transaction(function () use ($admin, $applier, $bill, $commission, $lastTransaction, $transactionService, $transactionTag) {
                    
                    $applierTrans = new Transaction();
                    $applierTrans->user_id = $applier->id;
                    $applierTrans->usertype = $applier->usertype;
                    $applierTrans->code = "COMMIN1";
                    $applierTrans->type = 'in';
                    $applierTrans->in_amount = $commission;
                    $applierTrans->log = 'Commission from bill request : id - ' . $bill->id;
                    $applierTrans->transaction_id = $transactionService->getTransactionId($lastTransaction->transaction_id);
                    $applierTrans->tag = $transactionTag;
                    $applierTrans->save();

                    $adminTrans = new Transaction();
                    $adminTrans->user_id = $admin->id;
                    $adminTrans->usertype = $admin->usertype;
                    $adminTrans->code = "COMMOUT";
                    $adminTrans->type = 'out';
                    $adminTrans->out_amount = $commission;
                    $adminTrans->log = 'Commission to super : id - ' . $bill->id;
                    $adminTrans->transaction_id = $transactionService->getTransactionId($applierTrans->transaction_id);
                    $adminTrans->tag = $transactionTag;
                    $adminTrans->save();

                    $bill->status = 1;
                    $bill->save();
                });
                return 1;
                break;
            case 'distributor':

                // $parent is mis taken.
                $parentMap = UserMap::where('user_id', $applier->id)->orderBy('id', 'desc')->first();
                $parent = User::find($parentMap->parent_id);
                if ($parent->usertype == 'admin') {
                    $transactionTag = $transactionService->getTransactionTag($parent->id, $applier->id, $bill->id);
                    DB::transaction(function () use ($parent, $applier, $bill, $commission, $lastTransaction, $transactionService, $transactionTag) {
                    
                        $applierTrans = new Transaction();
                        $applierTrans->user_id = $applier->id;
                        $applierTrans->usertype = $applier->usertype;
                        $applierTrans->code = "COMMIN1";
                        $applierTrans->type = 'in';
                        $applierTrans->in_amount = $commission;
                        $applierTrans->log = 'Commission from bill request : id - ' . $bill->id;
                        $applierTrans->transaction_id = $transactionService->getTransactionId($lastTransaction->transaction_id);
                        $applierTrans->tag = $transactionTag;
                        $applierTrans->save();
    
                        $adminTrans = new Transaction();
                        $adminTrans->user_id = $parent->id;
                        $adminTrans->usertype = $parent->usertype;
                        $adminTrans->code = "COMMOUT";
                        $adminTrans->type = 'out';
                        $adminTrans->out_amount = $commission;
                        $adminTrans->log = 'Commission to distributor : id - ' . $bill->id;
                        $adminTrans->transaction_id = $transactionService->getTransactionId($applierTrans->transaction_id);
                        $adminTrans->tag = $transactionTag;
                        $adminTrans->save();
    
                        $bill->status = 1;
                        $bill->save();
                    });
                    return 1;
                }
                else
                {
                    // parent is super distributor
                    $parentCommissionRate = $parent->commission - $applier->commission;
                    $parentCommission = $bill->amount * $parentCommissionRate / 100;

                    DB::transaction(function () use ($admin, $parent, $applier, $bill, $commission, $parentCommission, $lastTransaction, $transactionService, $transactionTag) {
                    
                        $applierTrans = new Transaction();
                        $applierTrans->user_id = $applier->id;
                        $applierTrans->usertype = $applier->usertype;
                        $applierTrans->code = "COMMIN1";
                        $applierTrans->type = 'in';
                        $applierTrans->in_amount = $commission;
                        $applierTrans->log = 'Commission from bill request : id - ' . $bill->id;
                        $applierTrans->transaction_id = $transactionService->getTransactionId($applierTrans->transaction_id);
                        $applierTrans->tag = $transactionService->getTransactionTag($admin->id, $applier->id, $bill->id);
                        $applierTrans->save();

                        $adminTrans = new Transaction();
                        $adminTrans->user_id = $admin->id;
                        $adminTrans->usertype = $admin->usertype;
                        $adminTrans->code = "COMMOUT";
                        $adminTrans->type = 'out';
                        $adminTrans->out_amount = $commission;
                        $adminTrans->log = 'Commission to distributor : id - ' . $bill->id;
                        $adminTrans->transaction_id = $transactionService->getTransactionId($applierTrans->transaction_id);
                        $adminTrans->tag = $transactionService->getTransactionTag($admin->id, $applier->id, $bill->id);
                        $adminTrans->save();

                        $parentTrans = new Transaction();
                        $parentTrans->user_id = $parent->id;
                        $parentTrans->usertype = $parent->usertype;
                        $parentTrans->code = "COMMIN2";
                        $parentTrans->type = 'in';
                        $parentTrans->in_amount = $parentCommission;
                        $parentTrans->log = 'Commission from bill request : id - ' . $bill->id;
                        $parentTrans->transaction_id = $transactionService->getTransactionId($adminTrans->transaction_id);
                        $parentTrans->tag = $transactionService->getTransactionTag($admin->id, $parent->id, $bill->id);
                        $parentTrans->save();

                        $adminTrans = new Transaction();
                        $adminTrans->user_id = $admin->id;
                        $adminTrans->usertype = $admin->usertype;
                        $adminTrans->code = "COMMOUT2";
                        $adminTrans->type = 'out';
                        $adminTrans->out_amount = $parentCommission;
                        $adminTrans->log = 'Commission to super : id - ' . $bill->id;
                        $adminTrans->transaction_id = $transactionService->getTransactionId($parentTrans->transaction_id);
                        $adminTrans->tag = $transactionService->getTransactionTag($admin->id, $parent->id, $bill->id);
                        $adminTrans->save();

                        $bill->status = 1;
                        $bill->save();
                        
                    });
                    return 1;

                }

                break;
            case 'retailer':
                $parentMap = UserMap::where('user_id', $applier->id)->orderBy('id', 'desc')->first();
                $parent = User::find($parentMap->parent_id);
                // case 1: parent is admin
                switch ($parent->usertype) {
                    case 'admin':
                        DB::transaction(function () use ($parent, $applier, $bill, $commission, $lastTransaction, $transactionService, $transactionTag) {
                    
                            $applierTrans = new Transaction();
                            $applierTrans->user_id = $applier->id;
                            $applierTrans->usertype = $applier->usertype;
                            $applierTrans->code = "COMMIN1";
                            $applierTrans->type = 'in';
                            $applierTrans->in_amount = $commission;
                            $applierTrans->log = 'Commission from bill request : id - ' . $bill->id;
                            $applierTrans->transaction_id = $transactionService->getTransactionId($lastTransaction->transaction_id);
                            $applierTrans->tag = $transactionTag;
                            $applierTrans->save();
        
                            $adminTrans = new Transaction();
                            $adminTrans->user_id = $parent->id;
                            $adminTrans->usertype = $parent->usertype;
                            $adminTrans->code = "COMMOUT";
                            $adminTrans->type = 'out';
                            $adminTrans->out_amount = $commission;
                            $adminTrans->log = 'Commission to retailer : id - ' . $bill->id;
                            $adminTrans->transaction_id = $transactionService->getTransactionId($applierTrans->transaction_id);
                            $adminTrans->tag = $transactionTag;
                            $adminTrans->save();
        
                            $bill->status = 1;
                            $bill->save();
                        });
                        return 1;
                        break;
                    case 'super':
                        // retailer will get
                        // admin will give

                        // super will get
                        // admin will give

                        DB::transaction(function () use ($parent, $applier, $bill, $commission, $lastTransaction, $transactionService, $transactionTag) {
                    
                            $applierTrans = new Transaction();
                            $applierTrans->user_id = $applier->id;
                            $applierTrans->usertype = $applier->usertype;
                            $applierTrans->code = "COMMIN1";
                            $applierTrans->type = 'in';
                            $applierTrans->in_amount = $commission;
                            $applierTrans->log = 'Commission from bill request : id - ' . $bill->id;
                            $applierTrans->transaction_id = $transactionService->getTransactionId($lastTransaction->transaction_id);
                            $applierTrans->tag = $transactionTag;
                            $applierTrans->save();
        
                            $adminTrans = new Transaction();
                            $adminTrans->user_id = $parent->id;
                            $adminTrans->usertype = $parent->usertype;
                            $adminTrans->code = "COMMOUT";
                            $adminTrans->type = 'out';
                            $adminTrans->out_amount = $commission;
                            $adminTrans->log = 'Commission to retailer : id - ' . $bill->id;
                            $adminTrans->transaction_id = $transactionService->getTransactionId($applierTrans->transaction_id);
                            $adminTrans->tag = $transactionTag;
                            $adminTrans->save();
        
                            $bill->status = 1;
                            $bill->save();
                        });
                        break;
                    case 'distributor':
                        $parentOfDistributorMap = UserMap::where('user_id', $parent->id)->orderBy('id', 'desc')->first();
                        $parentOfDistributor = User::find($parentOfDistributorMap->parent_id);
                        // case 1: parent of distributor is admin
                          // retailer will get
                          // admin will give

                          // distributor will get
                          // admin will give

                        // case 2: parent of distributor is super
                           // retailer will get
                           // admin will give

                           // distributor will give
                           // admin will give

                           // super will get
                           // admin will give
                        break;
                    default:
                        # code...
                        break;
                }
                break;
        }
        
    }

    private function transactCommission($taker, $giver, $bill_id, $amount, $transactionId, $transactionTag)
    {

    }
}