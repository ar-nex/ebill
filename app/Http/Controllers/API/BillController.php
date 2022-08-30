<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MappingService;
use App\Services\CommissionService;
use App\Services\TransactionService;
use App\Models\BillRequest;
use App\Models\Transaction;
use App\Models\User;
use Auth;

class BillController extends Controller
{
    
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'service_type' => ['required', Rule::in(['slow', 'fast'])],
            'consumer_mobile' => 'required|numeric',
            'consumer_id' => 'required|string',
            'operator_id' => 'required|numeric',
            'operator' => 'required|string',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors());
        }
        
        $user = Auth::user();
        $mappingService = new MappingService();
        $commissionService = new CommissionService();
        $ancestors = $mappingService->getInBetweenAncestorsByUser($user);
        array_unshift($ancestors, ["id"=>$user->id, "usertype" => $user->usertype]);
        $ancestorsWithCommission = $commissionService->getCommissions($ancestors, $request->amount, 'slow');


        DB::transaction(function() use ($request, $user, $ancestorsWithCommission)
        {
            $admin = User::where('usertype', 'admin')->first();
            $lastTransaction = Transaction::orderBy('id', 'desc')->first();
            $lastTransId = $lastTransaction != null ? $lastTransaction->transaction_id : 0;
            $transactionService = new TransactionService();
            $billRequest = BillRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'consumer_mobile' => $request->consumer_mobile,
                'consumer_id' => $request->consumer_id,
                'operator' => $request->operator,
                'operator_id' => $request->operator_id,
            ]);
            $tag = "COM-EL-".str_pad($billRequest->id, 7, '0', STR_PAD_LEFT);
            $billDeductTrans = Transaction::create([
                'user_id' => $user->id,
                'usertype' => $user->usertype,
                'code' => 'ELECT',
                'transaction_id' => $transactionService->getTransactionId($lastTransId),
                'type' => 'out',
                'tag' => $tag,
                'out_amount' => $request->amount,
                'log'=> 'Bill payment request : id - ' . $billRequest->id,
            ]);
            $ltransId = $billDeductTrans->transaction_id;
            $codeCount = 1;
            foreach ($ancestorsWithCommission as $key => $value) {
                //give commission
                $pairtag = $transactionService->getTransactionPairTag($admin->id, $value['user_id'], $billRequest->id);
                $commTransIn = Transaction::create([
                    'user_id' => $value['user_id'],
                    'usertype' => $value['user_type'],
                    'code' => 'COMIN'.$codeCount,
                    'transaction_id' => $transactionService->getTransactionId($ltransId),
                    'type' => 'in',
                    'in_amount' => $request->amount * $value['commission']/100,
                    'log'=> 'Commission : bill id - ' . $billRequest->id,
                    'tag' => $tag,
                    'pairtag' => $pairtag
                ]);
                $ltransId = $commTransIn->transaction_id;
                $commTransOut = Transaction::create([
                    'user_id' => $admin->id,
                    'usertype' => $admin->usertype,
                    'code' => 'COMOUT'.$codeCount,
                    'transaction_id' => $transactionService->getTransactionId($ltransId),
                    'type' => 'out',
                    'out_amount' => $request->amount * $value['commission']/100,
                    'log'=> 'Commission : bill id - ' . $billRequest->id,
                    'tag' => $tag,
                    'pairtag' => $pairtag
                ]);
                $codeCount++;
                $ltransId = $commTransOut->transaction_id;
            } 
        });


        return response()->json(['data' => $ancestorsWithCommission]);
    }
    
    // bill requests list status wise
    public function list(Request $request)
    {
        $this->validate($request, [
            'status' => 'required|numeric',
        ]);
        $billRequests = DB::table('bill_requests')
            ->join('users', 'bill_requests.user_id', '=', 'users.id')
            ->select('bill_requests.*', 'users.name', 'users.mobile', 'users.usertype')
            ->where('bill_requests.status', '=', $request->status)
            ->orderBy('bill_requests.id', 'desc')
            ->get();
        return response()->json(['status' => true, 'message' => 'Bill requests list', 'data' => $billRequests], 200);
    }

    // update bill request status
    public function update(Request $request, $id)
    {
        // only admin can update bill request status
        $user = Auth::user();
        if ($user->usertype != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $this->validate($request, [
            'status' => 'required|numeric',
        ]);
        $billRepository = new \App\Repositories\BillRepository();
        $status = $billRepository->updateStatus($id, $request->status);
        return response()->json(['status' => $status, 'message' => 'Bill request updated successfully'], 200);
    }

    private function processCommission(Request $request, $id)
    {
        $applier = \App\Models\User::find($billRequest->user_id);
        if($applier->commission == null)
        {
            return response()->json(['error' => 'Commission not set'], 401);
        }

        switch ($applier->usertype) {
            case 'super':
                // give commission to super and deduct from admin
                $commission_rate = $applier->commission;

                break;
            case 'distributor':
                $commission = $applier->commission->agent_commission;
                break;
            case 'retailer':
                $commission = $applier->commission->consumer_commission;
                break;
            default:
                $commission = 0;
                break;
        }
    }

    // delete bill request
    public function delete(Request $request, $id)
    {
        $billRequest = \App\Models\BillRequest::find($id);
        $billRequest->delete();
        return response()->json(['status' => true, 'message' => 'Bill request deleted successfully'], 200);
    }

}
