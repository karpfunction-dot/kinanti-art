<!-- OVERLAY GELAP SAAT MOBILE -->
<div id="overlay" class="overlay"></div>

<aside id="sidebar" class="sidebar">
    
    <!-- BAGIAN PROFIL USER -->
    <div class="sidebar-profile">
        @php
            $nama = Auth::user()->profil->nama_lengkap ?? Auth::user()->kode_barcode ?? 'User';
            $role = ucfirst(Auth::user()->role->nama_role ?? 'Member');
            $foto_url = \App\Support\PhotoUrl::resolve(Auth::user()->profil->foto_profil ?? null);
        @endphp

        <div class="sidebar-avatar">
            <img src="{{ $foto_url }}" 
     class="sidebar-user-photo" 
     alt="Foto Profil">
            <div class="avatar-status"></div>
        </div>
        
        <h3 class="sidebar-user-name">{{ $nama }}</h3>
        <p class="sidebar-user-role">
            <i class="fa fa-shield-alt me-1"></i> {{ $role }}
        </p>
    </div>

    <div class="sidebar-divider"></div>

    <!-- MENU DINAMIS DARI DATABASE -->
    <ul class="sidebar-menu">
        @php
            $userRoleId = Auth::user()->id_role;
            $normalizeMenuPage = function ($page) {
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
            };
            
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
            foreach ($dbMenus as $menu) {
                if (is_null($menu->id_parent)) {
                    $menuTree[$menu->id_menu] = [
                        'menu' => $menu,
                        'children' => []
                    ];
                }
            }
            foreach ($dbMenus as $menu) {
                if (!is_null($menu->id_parent) && isset($menuTree[$menu->id_parent])) {
                    $menuTree[$menu->id_parent]['children'][] = $menu;
                }
            }
        @endphp

        @forelse($menuTree as $parentMenu)
            @php $m = $parentMenu['menu']; @endphp
            @php $parentPage = $normalizeMenuPage($m->page); @endphp
            
            @if(empty($parentMenu['children']))
                <li>
                    <a href="{{ $parentPage ? url('/' . $parentPage) : '#' }}" class="{{ $parentPage && (Request::is($parentPage) || Request::is($parentPage . '/*')) ? 'active' : '' }}">
                        <div class="menu-icon"><i class="fa {{ $m->icon ?? 'fa-circle' }}"></i></div>
                        <span class="menu-label">{{ $m->label }}</span>
                    </a>
                </li>
            @else
                <li class="has-submenu">
                    <a href="#" onclick="toggleSubmenu(event, this)">
                        <div class="menu-icon"><i class="fa {{ $m->icon ?? 'fa-circle' }}"></i></div>
                        <span class="menu-label">{{ $m->label }}</span>
                        <i class="fa fa-chevron-down menu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @foreach($parentMenu['children'] as $child)
                            @php $childPage = $normalizeMenuPage($child->page); @endphp
                            <li>
                                <a href="{{ $childPage ? url('/' . $childPage) : '#' }}" class="{{ $childPage && (Request::is($childPage) || Request::is($childPage . '/*')) ? 'active' : '' }}">
                                    <i class="fa {{ $child->icon ?? 'fa-circle' }}"></i>
                                    <span>{{ $child->label }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endif
        @empty
            <!-- FALLBACK jika database kosong -->
            <li><a href="/dashboard"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/kelas"><i class="fa fa-chalkboard-user"></i> Data Kelas</a></li>
            <li><a href="/absensi"><i class="fa fa-clipboard-list"></i> Manajemen Absensi</a></li>
            <li><a href="/absensi_report"><i class="fa fa-chart-line"></i> Laporan Absensi</a></li>
            <li><a href="/idcard"><i class="fa fa-id-card"></i> ID Card Anggota</a></li>
            <li class="has-submenu">
                <a href="#" onclick="toggleSubmenu(event, this)">
                    <i class="fa fa-cog"></i> Pengaturan
                    <i class="fa fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li><a href="/settings/users">Manajemen Pengguna</a></li>
                    <li><a href="/settings/profil">Manajemen Profil</a></li>
                    <li><a href="/settings/roles">Manajemen Role</a></li>
                    <li><a href="/settings/menu">Menu Manager</a></li>
                    <li><a href="/settings/company_profile">Profil Sanggar</a></li>
                </ul>
            </li>
        @endforelse
        
        <li class="menu-divider"></li>
        <li class="menu-logout">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <div class="menu-icon"><i class="fa fa-right-from-bracket"></i></div>
                    <span class="menu-label">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</aside>

<style>
    /* SIDEBAR MODERN */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #0f3b2c 0%, #0a2d21 100%);
        color: #e2e8f0;
        position: fixed;
        top: 70px;
        left: 0;
        bottom: 0;
        overflow-y: auto;
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1200;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    }
    
    .sidebar.open { transform: translateX(0); }
    
    @media (min-width: 768px) {
        .sidebar { transform: translateX(0); }
        .overlay { display: none; }
    }
    
    @media (max-width: 767px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
    }
    
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        z-index: 1100;
    }
    
    .overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    
    .sidebar-profile {
        text-align: center;
        padding: 28px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-avatar {
        position: relative;
        display: inline-block;
        margin-bottom: 16px;
    }
    
    .sidebar-user-photo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, 0.2);
    }
    
    .avatar-status {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 14px;
        height: 14px;
        background: #22c55e;
        border-radius: 50%;
        border: 2px solid #0f3b2c;
    }
    
    .sidebar-user-name {
        margin: 12px 0 6px;
        font-size: 1rem;
        font-weight: 600;
        color: white;
    }
    
    .sidebar-user-role {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0;
    }
    
    .sidebar-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        margin: 12px 0;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 12px 0;
        margin: 0;
    }
    
    .sidebar-menu li {
        margin: 4px 12px;
    }
    
    .sidebar-menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        color: #cbd5e1;
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .sidebar-menu li a:hover {
        background: rgba(255, 255, 255, 0.08);
        color: white;
    }
    
    .sidebar-menu li a.active {
        background: linear-gradient(135deg, #1a5d45 0%, #0f3b2c 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .menu-icon {
        width: 28px;
        text-align: center;
    }
    
    .menu-label {
        flex: 1;
    }
    
    .has-submenu > a { cursor: pointer; }
    .menu-arrow { margin-left: auto; transition: transform 0.2s ease; }
    .submenu {
        list-style: none;
        padding-left: 42px;
        margin: 4px 0 8px 0;
        display: none;
    }
    .submenu li a {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
    .submenu li a i { font-size: 0.75rem; width: 24px; }
    .menu-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        margin: 12px 0 !important;
    }
    .menu-logout a {
        width: 100%;
        border: none;
        background: transparent;
        cursor: pointer;
        text-align: left;
    }
    .menu-logout .logout-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 10px 14px;
        border-radius: 12px;
        border: none;
        background: transparent;
        color: #f87171;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .menu-logout .logout-btn:hover {
        background: rgba(248, 113, 113, 0.1);
        color: #fecaca;
    }
    .menu-logout a {
        color: #f87171 !important;
    }
    .menu-logout a:hover {
        background: rgba(248, 113, 113, 0.1) !important;
        color: #fecaca !important;
    }
    
    .sidebar::-webkit-scrollbar { width: 4px; }
    .sidebar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
</style>

<script>
    function toggleSubmenu(event, element) {
        event.preventDefault();
        event.stopPropagation();
        
        let parentLi = element.closest('li');
        let submenu = parentLi.querySelector('.submenu');
        let arrow = element.querySelector('.menu-arrow');
        
        if (submenu) {
            if (submenu.style.display === "none" || !submenu.style.display) {
                submenu.style.display = "block";
                if (arrow) arrow.style.transform = "rotate(180deg)";
            } else {
                submenu.style.display = "none";
                if (arrow) arrow.style.transform = "rotate(0deg)";
            }
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Buka submenu jika ada anak aktif
        document.querySelectorAll('.has-submenu').forEach(function(menu) {
            let hasActiveChild = menu.querySelector('.submenu li a.active');
            if (hasActiveChild) {
                let submenu = menu.querySelector('.submenu');
                let arrow = menu.querySelector('.menu-arrow');
                if (submenu) submenu.style.display = "block";
                if (arrow) arrow.style.transform = "rotate(180deg)";
            }
        });
    });
</script>
