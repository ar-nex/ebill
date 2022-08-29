<?php
namespace App\Services;
use App\Models\Commission;

class CommissionService
{
    public function getCommissions($ancestors, $amount, $service_type)
    {
        $data = [];
        foreach ($ancestors as $ancestor) {
            $commission = 0;
            $commissionRow = Commission::where('user_id', $ancestor["id"])
            ->where('min_range', '<=', $amount)
            ->where('max_range', '>=', $amount)
            ->where('service_type', $service_type)->first();
            if ($commissionRow != null) {
                $commission = $commissionRow->percentage;
            }
            $commInfo = [
                'commission' => $commission,
                'user_id' => $ancestor["id"],
                'user_type' => $ancestor["usertype"],
            ];
            array_push($data, $commInfo);
        }
        return $data;
    }
}
