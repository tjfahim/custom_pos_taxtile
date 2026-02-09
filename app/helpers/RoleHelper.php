<?php

namespace App\Helpers;

use App\Models\User;

class RoleHelper
{
    public static function convertOldRole($oldRoleId)
    {
        $roleMap = [
            1 => 'admin',
            2 => 'manager',
            3 => 'staff',
        ];

        return $roleMap[$oldRoleId] ?? 'staff';
    }
}