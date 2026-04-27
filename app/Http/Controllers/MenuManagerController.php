<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MenuManagerController extends Controller
{
    private function normalizeMenuPage(?string $page): ?string
    {
        if ($page === null) {
            return null;
        }

        $normalized = trim($page);
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

    public function index()
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $roles = DB::table('roles')->where('aktif', 1)->orderBy('id_role')->get();
        
        $menus = DB::table('menu_registry as m')
            ->leftJoin('menu_role_access as ra', 'm.id_menu', '=', 'ra.id_menu')
            ->leftJoin('roles as r', 'ra.id_role', '=', 'r.id_role')
            ->select(
                'm.id_menu',
                'm.id_parent',
                'm.label',
                'm.icon',
                'm.page',
                'm.order_index',
                'm.aktif',
                DB::raw('GROUP_CONCAT(DISTINCT r.nama_role SEPARATOR ", ") as role_names'),
                DB::raw('GROUP_CONCAT(DISTINCT r.id_role SEPARATOR ",") as role_ids')
            )
            ->groupBy('m.id_menu', 'm.id_parent', 'm.label', 'm.icon', 'm.page', 'm.order_index', 'm.aktif')
            ->orderByRaw('COALESCE(m.id_parent, 0)')
            ->orderBy('m.order_index')
            ->orderBy('m.id_menu')
            ->get();
        
        $parentMenus = DB::table('menu_registry')
            ->whereNull('id_parent')
            ->orderBy('order_index')
            ->orderBy('label')
            ->get();
        
        return view('settings.menu.index', compact('roles', 'menus', 'parentMenus'));
    }
    
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'label' => 'required|string|max:100',
        'icon' => 'nullable|string|max:50',
        'page' => 'nullable|string|max:200',
        'id_parent' => 'nullable|exists:menu_registry,id_menu',
        'order_index' => 'nullable|integer',
        'aktif' => 'boolean',
        'roles' => 'nullable|array',
        'roles.*' => 'exists:roles,id_role',
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ]);
    }
    
    try {
        DB::beginTransaction();
        
        $normalizedPage = $this->normalizeMenuPage($request->page);

        // Cek apakah page sudah ada (untuk menghindari duplicate)
        $existing = DB::table('menu_registry')
            ->where('page', $normalizedPage)
            ->where('id_parent', $request->id_parent ?: null)
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Menu dengan page "' . $request->page . '" sudah ada!'
            ]);
        }
        
        $id_menu = DB::table('menu_registry')->insertGetId([
                'id_parent' => $request->id_parent ?: null,
                'label' => $request->label,
                'icon' => $request->icon,
                'page' => $normalizedPage,
                'order_index' => $request->order_index ?? 0,
                'aktif' => $request->aktif ?? 1,
                'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($request->has('roles') && is_array($request->roles)) {
            foreach ($request->roles as $roleId) {
                DB::table('menu_role_access')->insert([
                    'id_menu' => $id_menu,
                    'id_role' => $roleId,
                    'created_at' => now(),
                ]);
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => '✅ Menu berhasil ditambahkan'
        ]);
        
    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
    
   public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'label' => 'required|string|max:100',
        'icon' => 'nullable|string|max:50',
        'page' => 'nullable|string|max:200',
        'id_parent' => 'nullable|exists:menu_registry,id_menu',
        'order_index' => 'nullable|integer',
        'aktif' => 'boolean',
        'roles' => 'nullable|array',
        'roles.*' => 'exists:roles,id_role',
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ]);
    }
    
    try {
        DB::beginTransaction();

        $normalizedPage = $this->normalizeMenuPage($request->page);
        
        // ✅ UPDATE menu_registry - TANPA id_role
        DB::table('menu_registry')->where('id_menu', $id)->update([
            'id_parent' => $request->id_parent ?: null,
            'label' => $request->label,
            'icon' => $request->icon,
            'page' => $normalizedPage,
            'order_index' => $request->order_index ?? 0,
            'aktif' => $request->aktif ?? 1,
            'updated_at' => now(),
        ]);
        
        // ✅ UPDATE menu_role_access
        // Hapus semua role access yang lama
        DB::table('menu_role_access')->where('id_menu', $id)->delete();
        
        // Insert role access yang baru
        if ($request->has('roles') && is_array($request->roles) && count($request->roles) > 0) {
            foreach ($request->roles as $roleId) {
                DB::table('menu_role_access')->insert([
                    'id_menu' => $id,
                    'id_role' => $roleId,
                    'created_at' => now(),
                ]);
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => '✏️ Menu berhasil diperbarui'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui: ' . $e->getMessage()
        ]);
    }
}
    public function getMenu($id)
    {
        $menu = DB::table('menu_registry')->where('id_menu', $id)->first();
        
        if ($menu) {
            $roleIds = DB::table('menu_role_access')
                ->where('id_menu', $id)
                ->pluck('id_role')
                ->toArray();
            
            $menu->role_ids = $roleIds;
            
            return response()->json([
                'success' => true,
                'data' => $menu
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Menu tidak ditemukan'
        ]);
    }

    
}
