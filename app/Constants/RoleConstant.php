<?php

namespace App\Constants;

/**
 * Role Constants
 * Centralized role ID management
 */
class RoleConstant
{
    const ADMIN = 1;
    const MANAJEMEN = 2;
    const PELATIH = 3;
    const SISWA = 4;

    /**
     * Get all role IDs
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::MANAJEMEN,
            self::PELATIH,
            self::SISWA,
        ];
    }

    /**
     * Check if role is admin or management
     */
    public static function isAdminOrManagement($roleId): bool
    {
        return in_array($roleId, [self::ADMIN, self::MANAJEMEN]);
    }

    /**
     * Get role name by ID
     */
    public static function getRoleName($roleId): ?string
    {
        $roles = [
            self::ADMIN => 'admin',
            self::MANAJEMEN => 'manajemen',
            self::PELATIH => 'pelatih',
            self::SISWA => 'siswa',
        ];

        return $roles[$roleId] ?? null;
    }
}
