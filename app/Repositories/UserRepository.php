<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\UserMap;

class UserRepository
{
    public function getAll()
    {
        return User::all();
    }

    public function getById($id)
    {
        return User::find($id);
    }

    public function getByUserType($type)
    {
        return User::where('usertype', $type)->get();
    }

    public function getCountByUserType($type)
    {
        return User::where('usertype', $type)->count();
    }

    public function getCountByUserTypeForUser($type, $parentId)
    {
        return UserMap::where('usertype', $type)->where('parent_id', $parentId)->count();
    }

}