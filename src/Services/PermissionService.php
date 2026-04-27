<?php

namespace App\Services;

/**
 * Permission utility
 */
class PermissionService
{
    /**
     * Check if user has permission
     */
    public static function can(string $permission): bool
    {
        if (!isset($_SESSION['permissions'])) {
            return false;
        }

        return in_array($permission, $_SESSION['permissions']);
    }
}
