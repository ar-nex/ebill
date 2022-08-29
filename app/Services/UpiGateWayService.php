<?php
namespace App\Services;

use App\Models\UpiGateWay;

class UpiGateWayService
{
    public function makeTransId()
    {
        if(function_exists('date_default_timezone_set')) {
            date_default_timezone_set("Asia/Kolkata");
        }
        $date = date('ymdHis');
        $count = UpiGateWay::count();
        if ($count == 0) {
            return $date .'0001';
        }
        else
        {
            $last = UpiGateWay::orderBy('id', 'desc')->first();
            $last_transid = $last->transaction;
            $last_id = $last->id;
            $last_id++;
            $last_id = str_pad($last_id, 4, '0', STR_PAD_LEFT);
            return $date . $last_id;
        }
        return $date;

    }
}