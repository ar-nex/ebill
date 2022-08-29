<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;

class UpiGateWayController extends Controller
{
    public function initiate(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'amount' => 'required|numeric',
            'purpose' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());       
        }
        
        // service for initiating payment
        $service = new \App\Services\UpiGateWayService();
        $transaction_id = $service->makeTransId();

        $key = config('constants.upi_gateway_key');
        $user = Auth::user();
        $content = json_encode(array(
            "key"=> $key,
            "client_txn_id"=> $transaction_id, // order id or your own transaction id
            "amount"=> $request->amount,
            "p_info"=> $request->purpose,
            "customer_name"=> $user->name, // customer name
            "customer_email"=> $user->email, // customer email
            "customer_mobile"=> $user->mobile, // customer mobile number
            "redirect_url"=>route("payment.redirect").'?client_txn_id='.$transaction_id.'txn_id=', // redirect url after payment, with ?client_txn_id=&txn_id=
          // "redirect_url"=>"https://google.com", // redirect url after payment, with ?client_txn_id=&txn_id=
            "udf1"=> $user->id, // udf1, udf2 and udf3 are used to save other order related data, like customer id etc.
            "udf2"=> "user defined field 2",
            "udf3"=> "user defined field 3",
        ));

        $curl = curl_init(config('constants.upi_gateway_url'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 200 ) {
            // You can handle Error yourself.
            die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }
        curl_close($curl);
        $response = json_decode($json_response, true);
        if($response["status"] == true){
            $upi_gateway = new \App\Models\UpiGateWay();
            $upi_gateway->client_txn_id = $transaction_id;
            $upi_gateway->amount = $request->amount;
            $upi_gateway->product_info = $request->purpose;
            $upi_gateway->user_id = $user->id;
            $upi_gateway->udf1 = $user->id;
            $upi_gateway->save();
        }
        return response()->json($response);

        // if($response["status"] == true){
        //     return response()->json($response);
        //     // Method 1
        //     // redirect to payment page of UPI
        //     // header("Location: ".$response["data"]["payment_url"]);
        //     // die();
        //     // Method 2
        //     // echo "<script>window.location.href='".$response["data"]["payment_url"]."'</script>";
        //     // die();
        // }else{
        //     echo $response['msg'];
        // }
    }

    public function redirected()
    {
        
    }
}
