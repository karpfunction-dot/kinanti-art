<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // LOGIKA MENU DINAMIS
        // Kita bagikan variabel $sidebar_menu ke file 'layouts.sidebar_native'
        View::composer('layouts.sidebar_native', function ($view) {
            
            $sidebar_menu = [];
            
            if (Auth::check()) {
                $role_id = Auth::user()->id_role;

                // 1. Ambil Menu Mentah dari Database (Sesuai Logic Native)
                $raw_menus = DB::table('menu_registry as m')
                    ->join('menu_role_access as a', 'm.id_menu', '=', 'a.id_menu')
                    ->where('a.id_role', $role_id)
                    ->where('m.aktif', 1)
                    ->orderBy('m.id_parent', 'ASC') // Parent dulu
                    ->orderBy('m.order_index', 'ASC') // Urutan
                    ->select('m.*')
                    ->get();

                // 2. Susun Hierarchy (Parent -> Child)
                $menu_tree = [];
                
                // Masukkan Parent dulu
                foreach ($raw_menus as $m) {
                    if (empty($m->id_parent)) {
                        $menu_tree[$m->id_menu] = (array) $m;
                        $menu_tree[$m->id_menu]['submenu'] = [];
                    }
                }

                // Masukkan Anak (Submenu) ke Parentnya
                foreach ($raw_menus as $m) {
                    if (!empty($m->id_parent) && isset($menu_tree[$m->id_parent])) {
                        $menu_tree[$m->id_parent]['submenu'][] = (array) $m;
                    }
                }

                $sidebar_menu = $menu_tree;
            }

            // Kirim variabel ke View
            $view->with('sidebar_menu', $sidebar_menu);
        });
    }
}