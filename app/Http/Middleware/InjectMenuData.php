<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MenuService;

class InjectMenuData
{
    /**
     * Handle an incoming request.
     * Automatically inject menu data into all views for authenticated users
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $roleId = auth()->user()->id_role ?? null;
            $menuData = MenuService::getMenuTree($roleId);
            
            // Share menu data with all views
            view()->share('menuData', $menuData);
        }

        return $next($request);
    }
}
