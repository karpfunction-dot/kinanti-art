<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Menu Service
 * Handle menu generation and filtering
 */
class MenuService
{
    /**
     * Get menu tree for authenticated user
     * @param int $userRoleId
     * @return array
     */
    public static function getMenuTree(int $userRoleId): array
    {
        $dbMenus = DB::table('menu_registry as m')
            ->leftJoin('menu_role_access as ra', 'm.id_menu', '=', 'ra.id_menu')
            ->where('m.aktif', 1)
            ->where(function($q) use ($userRoleId) {
                $q->where('ra.id_role', $userRoleId)
                  ->orWhereNull('ra.id_menu');
            })
            ->select('m.*')
            ->orderByRaw('COALESCE(m.id_parent, 0)')
            ->orderBy('m.order_index')
            ->orderBy('m.id_menu')
            ->get();
        
        $menuTree = [];
        
        // Build parent menus
        foreach ($dbMenus as $menu) {
            if (is_null($menu->id_parent)) {
                $menuTree[$menu->id_menu] = [
                    'menu' => $menu,
                    'children' => []
                ];
            }
        }
        
        // Attach child menus
        foreach ($dbMenus as $menu) {
            if (!is_null($menu->id_parent) && isset($menuTree[$menu->id_parent])) {
                $menuTree[$menu->id_parent]['children'][] = $menu;
            }
        }
        
        return $menuTree;
    }

    /**
     * Normalize menu page URL
     * @param string|null $page
     * @return string|null
     */
    public static function normalizeMenuPage(?string $page): ?string
    {
        $normalized = trim((string) $page);
        $normalized = trim($normalized, '/');

        if ($normalized === '') {
            return null;
        }

        $aliases = [
            'jadwal_info' => 'jadwal',
            'jadwal-info' => 'jadwal',
            'idcard_info' => 'idcard',
            'idcard-info' => 'idcard',
        ];

        return $aliases[$normalized] ?? $normalized;
    }
}
