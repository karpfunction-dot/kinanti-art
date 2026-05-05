<?php

namespace App\Enums;

enum RoleType: int
{
    case ADMIN = 1;
    case MANAJEMEN = 2;
    case PELATIH = 3;
    case SISWA = 4;

    /**
     * Get label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::MANAJEMEN => 'Manajemen',
            self::PELATIH => 'Pelatih',
            self::SISWA => 'Siswa',
        };
    }

    /**
     * Check if role is administrative (Admin or Manajemen)
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::ADMIN, self::MANAJEMEN]);
    }
}
