<?php

namespace App\Services;

class TransactionService
{
    public function getTransactionId($lastTrans)
    {
        $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if(function_exists('date_default_timezone_set')) {
            date_default_timezone_set("Asia/Kolkata");
        }
        $date = date('dmy');
        if($lastTrans == 0){
            return $date .'-AA0001';
        } 
        else
        {
            // last 4 digit of lastTrans
            $lastFour = substr($lastTrans, -4);
            $lastSix = substr($lastTrans, -6);
            $lastFour++;

            $secondChar = substr($lastSix, 1, 1);
            $firstChar = substr($lastSix, 0, 1);

            if ($lastFour == 10000) {
                $secondCharIndex = strpos($alpha, $secondChar);
                if ($secondCharIndex == 25) {
                    $firstCharIndex = strpos($alpha, $firstChar);
                    $firstCharIndex++;
                    $firstChar = substr($alpha, $firstCharIndex, 1);
                    $secondChar = "A";
                } else {
                    $secondChar = substr($alpha, $secondCharIndex + 1, 1);
                }
                $lastFour = '0001';
                return $date .'-'. $firstChar . $secondChar . $lastFour;
            }
            else{
                return $date .'-'. $firstChar . $secondChar . str_pad($lastFour, 4, '0', STR_PAD_LEFT);
            }
        }
    }

    public function getTransactionTag($giver_id, $taker_id, $relevant_id)
    {
        return str_pad($giver_id, 6, '0', STR_PAD_LEFT)
                .'-' 
                . str_pad($taker_id, 6, '0', STR_PAD_LEFT) 
                .'-'
                . str_pad($relevant_id, 8, '0', STR_PAD_LEFT);
    }

    public function getTransactionPairTag($giver_id, $taker_id, $relevant_id)
    {
        return str_pad($giver_id, 6, '0', STR_PAD_LEFT)
                .'-' 
                . str_pad($taker_id, 6, '0', STR_PAD_LEFT) 
                .'-'
                . str_pad($relevant_id, 8, '0', STR_PAD_LEFT);
    }
}