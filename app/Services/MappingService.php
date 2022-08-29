<?php
namespace App\Services;

use App\Models\UserMap;

class MappingService
{
    
    public function getInBetweenAncestorsByUser($user)
    {
        $ancestors = $this->getInBetweenAncestorByUserId($user->id);
        return $ancestors;
    }

    public function getInBetweenAncestorByUserId($userId)
    {
        $childId = $userId;
        $ancestors = [];
        $ancestorIsAdmin = false;
        while (!$ancestorIsAdmin)
        {
            $userMap = UserMap::where('user_id', $childId)->first();
            if($userMap->parenttype == 'admin')
            {
                $ancestorIsAdmin = true;
            }
            else
            {
                $ancestorIsAdmin = false;
                $childId = $userMap->parent_id;
                $ancestor = [
                    'id' => $userMap->parent_id,
                    'usertype' => $userMap->parenttype
                ];
                array_push($ancestors, $ancestor);
            }
        }
        return $ancestors;
    }
}