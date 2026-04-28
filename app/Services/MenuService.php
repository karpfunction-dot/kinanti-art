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
    public static function getMenuTree(?int $userRoleId): array
    {
        // Get all active menus
        $allMenus = DB::table('menu_registry')
            ->where('aktif', 1)
            ->orderByRaw('COALESCE(id_parent, 0)')
            ->orderBy('order_index')
            ->orderBy('id_menu')
            ->get()
            ->keyBy('id_menu');

        // Get all role restrictions for this specific role
        $accessibleMenuIds = DB::table('menu_role_access')
            ->where('id_role', $userRoleId)
            ->pluck('id_menu')
            ->toArray();

        // Get menus that have ANY role restrictions (these are restricted menus)
        $restrictedMenuIds = DB::table('menu_role_access')
            ->distinct('id_menu')
            ->pluck('id_menu')
            ->toArray();

        // Filter menus based on role access logic:
        // - If menu is NOT in restricted list, show it (unrestricted menu)
        // - If menu IS in restricted list, only show if user has access
        $visibleMenus = [];
        foreach ($allMenus as $menu) {
            if (!in_array($menu->id_menu, $restrictedMenuIds)) {
                // Menu has no role restrictions - show to everyone
                $visibleMenus[$menu->id_menu] = $menu;
            } elseif (in_array($menu->id_menu, $accessibleMenuIds)) {
                // Menu has role restrictions but user has access
                $visibleMenus[$menu->id_menu] = $menu;
            }
        }

        // Build menu tree from visible menus
        $menuTree = [];
        
        // Build parent menus
        foreach ($visibleMenus as $menu) {
            if (is_null($menu->id_parent)) {
                $menuTree[$menu->id_menu] = [
                    'menu' => $menu,
                    'children' => []
                ];
            }
        }
        
        // Attach child menus
        foreach ($visibleMenus as $menu) {
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
