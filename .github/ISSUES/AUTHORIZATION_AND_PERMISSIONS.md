# AUTHORIZATION & PERMISSION SPECIFICATION

**Status:** 📖 Reference Document  
**Audience:** Junior Dev, QA Team  
**Reference:** Addressed gap from BRD analysis

---

## 📋 Deskripsi

Dokumen ini menjelaskan **explicit authorization matrix** untuk landing page CMS. Menjelaskan siapa boleh akses apa, kapan, dan bagaimana tech implementasinya.

---

## 🎯 Permission Matrix

### Role Definition

| Role | Tujuan | Permissions |
|------|--------|------------|
| **Super Admin** | Full control semua fitur | CRUD all, manage settings, manage users |
| **Content Manager** | Manage content saja | CREATE/READ/UPDATE own items, READ all items |
| **Teacher** | Operasional (existing) | Absensi, nilai, kelas (tidak kena landing page) |
| **Student** | Operasional (existing) | View nilai, absensi (tidak kena landing page) |
| **Public User** | Baca konten publik | READ-ONLY homepage & public pages |

---

## 🔐 Detailed Permission Matrix

### Articles Module

| Action | Super Admin | Content Manager | Public User |
|--------|-------------|-----------------|-------------|
| **View list** | ✅ | ✅ | ❌ (admin page) |
| **Create** | ✅ | ✅ | ❌ |
| **Read own** | ✅ | ✅ | ❌ |
| **Read others'** | ✅ | ✅ | ❌ |
| **Update own** | ✅ | ✅ | ❌ |
| **Update others'** | ✅ | ❌ | ❌ |
| **Delete own** | ✅ | ❌ | ❌ |
| **Delete others'** | ✅ | ❌ | ❌ |
| **Publish** | ✅ | ✅ (own) | ❌ |
| **View published articles (public)** | ✅ | ✅ | ✅ |

**Logic:**
- Content Manager create article → created_by = content_manager_id
- Content Manager hanya bisa edit/update if: `article.created_by === auth()->id()`
- Delete hanya admin
- Published artikel visible ke public (no auth needed)

### Events Module

| Action | Super Admin | Content Manager | Public User |
|--------|-------------|-----------------|-------------|
| **CRUD** | ✅ | ✅ (full) | ❌ |
| **View published events** | ✅ | ✅ | ✅ |

**Logic:**
- Content Manager punya full CRUD untuk event (tidak perlu created_by logic)
- Atau alternative: `events.created_by` check seperti articles

### Achievements Module

| Action | Super Admin | Content Manager | Public User |
|--------|-------------|-----------------|-------------|
| **View list (admin)** | ✅ | ✅ | ❌ |
| **Create** | ✅ | ❌ | ❌ |
| **Update** | ✅ | ❌ | ❌ |
| **Delete** | ✅ | ❌ | ❌ |
| **View (public)** | ✅ | ✅ | ✅ |

**Logic:**
- Content Manager view-only (tidak bisa edit)
- Hanya admin yang create/edit/delete prestasi

### Trainers, Activities, Hero Slides

| Module | Super Admin | Content Manager | Public |
|--------|-------------|-----------------|--------|
| **Trainers** | CRUD | VIEW-ONLY | VIEW |
| **Activities** | CRUD | CRUD | VIEW |
| **Hero Slides** | CRUD | ❌ | VIEW |

### Site Settings

| Action | Super Admin | Content Manager | Public User |
|--------|-------------|-----------------|-------------|
| **View** | ✅ | ❌ | ❌ |
| **Update** | ✅ | ❌ | ❌ |

---

## 🛠️ Technical Implementation

### 1. Middleware Check

**File:** `app/Http/Middleware/CheckRole.php`

```php
namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    public function handle($request, $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('login');
        }
        
        $userRole = auth()->user()->role;
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized');
        }
        
        return $next($request);
    }
}
```

**Usage di routes:**

```php
Route::middleware(['auth', 'role:admin,content_manager'])->group(function () {
    Route::resource('articles', ArticleController::class);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('settings', SiteSettingController::class);
});
```

### 2. Policy Check

**File:** `app/Policies/Landing/ArticlePolicy.php`

```php
namespace App\Policies\Landing;

use App\Models\User;
use App\Models\Landing\Article;

class ArticlePolicy
{
    /**
     * Content Manager hanya bisa update artikel miliknya
     * Admin bisa update semua
     */
    public function update(User $user, Article $article): bool
    {
        if ($user->hasRole('admin')) {
            return true; // Admin boleh update semua
        }
        
        if ($user->hasRole('content_manager')) {
            return $article->created_by === $user->id; // CM hanya own articles
        }
        
        return false;
    }
    
    /**
     * Hanya admin yang bisa delete
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->hasRole('admin');
    }
    
    /**
     * Semua auth user bisa view
     */
    public function view(User $user, Article $article): bool
    {
        return true;
    }
}
```

**Usage di Controller:**

```php
public function update(UpdateArticleRequest $request, Article $article)
{
    // Method 1: Manual check
    if ($article->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
        abort(403);
    }
    
    // Method 2: Using Gate (cleaner)
    $this->authorize('update', $article);
    
    // ... update logic
}
```

### 3. HasRole Helper

**File:** `app/Models/User.php` (add method)

```php
public function hasRole($roles): bool
{
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($this->role, $roles);
}
```

**Usage:**
```php
if (auth()->user()->hasRole('admin')) { ... }
if (auth()->user()->hasRole(['admin', 'content_manager'])) { ... }
```

### 4. Blade Template Authorization

```blade
{{-- Show edit button only if authorized --}}
@can('update', $article)
    <a href="{{ route('articles.edit', $article) }}" class="btn btn-warning">Edit</a>
@endcan

{{-- Alternative: Using helper --}}
@if (auth()->user()->hasRole('admin'))
    <a href="{{ route('articles.delete', $article) }}" class="btn btn-danger">Delete</a>
@endif
```

---

## 🚨 Authorization Tests

### Test 1: Content Manager Can Create

```php
public function test_content_manager_can_create_article()
{
    $cm = User::factory()->create(['role' => 'content_manager']);
    
    $this->actingAs($cm)
        ->post(route('articles.store'), [
            'title' => 'Test',
            'slug' => 'test',
            'content' => 'Content',
            'excerpt' => 'Excerpt',
            'category' => 'berita',
            'status' => 'published',
        ])
        ->assertRedirect();
    
    $article = Article::where('title', 'Test')->first();
    $this->assertEquals($cm->id, $article->created_by);
}
```

### Test 2: Content Manager Cannot Edit Others' Articles

```php
public function test_content_manager_cannot_edit_others_article()
{
    $cm1 = User::factory()->create(['role' => 'content_manager']);
    $cm2 = User::factory()->create(['role' => 'content_manager']);
    $article = Article::factory()->create(['created_by' => $cm1->id]);
    
    $this->actingAs($cm2)
        ->put(route('articles.update', $article), ['title' => 'Updated'])
        ->assertForbidden();
}
```

### Test 3: Content Manager Cannot Delete

```php
public function test_content_manager_cannot_delete_article()
{
    $cm = User::factory()->create(['role' => 'content_manager']);
    $article = Article::factory()->create(['created_by' => $cm->id]);
    
    $this->actingAs($cm)
        ->delete(route('articles.destroy', $article))
        ->assertForbidden();
}
```

### Test 4: Admin Can Do Everything

```php
public function test_admin_can_do_everything()
{
    $admin = User::factory()->create(['role' => 'admin']);
    $article = Article::factory()->create();
    
    // Can edit anyone's article
    $this->actingAs($admin)
        ->put(route('articles.update', $article), ['title' => 'Updated'])
        ->assertRedirect();
    
    // Can delete
    $this->actingAs($admin)
        ->delete(route('articles.destroy', $article))
        ->assertRedirect();
}
```

### Test 5: Unauthenticated Cannot Access

```php
public function test_unauthenticated_cannot_access_admin_pages()
{
    $this->get(route('articles.index'))
        ->assertRedirect('/login');
}
```

---

## 🎯 Enforcement Checklist (for QA)

| Test | Expected | Status |
|------|----------|--------|
| Public user visits `/admin/articles` | 401 redirect to login | ⏳ |
| Student visits `/admin/articles` | 403 forbidden | ⏳ |
| Content Manager tries edit article dari CM lain | 403 forbidden | ⏳ |
| Content Manager tries delete article | 403 forbidden | ⏳ |
| Content Manager tries access settings | 403 forbidden | ⏳ |
| Admin visits any `/admin` page | 200 OK | ⏳ |
| Admin can delete anyone's article | 204/redirect OK | ⏳ |
| Published article visible di homepage (public) | 200 OK | ⏳ |
| Draft article NOT visible di homepage (public) | Not shown | ⏳ |

---

## 📌 Defaults & Recommendations

### User Creation (Admin Setup)

```php
// Seeder untuk initial users
DB::table('users')->insert([
    [
        'name' => 'Admin',
        'email' => 'admin@kinanti-art.local',
        'password' => bcrypt('password123'),
        'role' => 'admin',
    ],
    [
        'name' => 'Content Manager 1',
        'email' => 'content@kinanti-art.local',
        'password' => bcrypt('password123'),
        'role' => 'content_manager',
    ],
]);
```

### Role Assignment

```php
// Assign role ke existing user
$user = User::find(1);
$user->update(['role' => 'admin']);
```

---

## ⚠️ Common Mistakes (to avoid)

❌ **WRONG:** Check role di every controller method
```php
public function update(Request $request, Article $article)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }
    // ...
}
```

✅ **RIGHT:** Use middleware + policy
```php
Route::middleware('role:admin')->put('/articles/{article}', ...);
// Or use Policy in controller:
$this->authorize('update', $article);
```

❌ **WRONG:** Hardcode permission logic
```php
if ($user->role == 'admin' || $user->role == 'content_manager')
```

✅ **RIGHT:** Use helper
```php
if ($user->hasRole(['admin', 'content_manager']))
```

---

## 🔗 Related Files

- `app/Policies/Landing/ArticlePolicy.php` – Authorization logic
- `app/Policies/Landing/EventPolicy.php` – Similar pattern
- `app/Http/Middleware/CheckRole.php` – Middleware
- `app/Models/User.php` – hasRole() helper
- `tests/Feature/Landing/AuthorizationTest.php` – All auth tests
- `.env` – Role definitions

---

**Created:** May 4, 2026  
**Version:** 1.0  
**Last Updated:** May 4, 2026
