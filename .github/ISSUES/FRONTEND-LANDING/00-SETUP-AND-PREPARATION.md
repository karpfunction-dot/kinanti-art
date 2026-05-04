# 00️⃣ SETUP AND PREPARATION: Landing Page Infrastructure

**Status:** 🔴 Not Started  
**Priority:** 🔴 CRITICAL (Blocking all Phase 2)  
**Duration:** ~1 day  
**Assignee:** @[assign-1-senior-dev]  
**Related Master Issue:** [#00-MASTER-ISSUE](./00-MASTER-ISSUE.md)

---

## 📋 Deskripsi

Setup semua infrastructure & foundation untuk landing page sebelum modul-modul diimplementasikan. Termasuk: database migrations, models, services, routes, middleware, dashboard menu, dan authorization matrix.

**JANGAN LANJUT ke module issues sampai issue ini 100% selesai dan merged.**

---

## ✅ Acceptance Criteria

- [ ] Semua 7 models sudah dibuat (Article, Event, Achievement, Trainer, Activity, HeroSlide, SiteSetting)
- [ ] Semua migration sudah buat & tested (tidak ada error)
- [ ] CloudinaryService sudah siap untuk dipakai semua module
- [ ] Authorization middleware sudah dibuat (Admin, ContentManager roles)
- [ ] Dashboard menu sudah updated (sidebar baru untuk CMS)
- [ ] Routes setup (`/admin/articles`, `/admin/events`, etc.) sudah siap
- [ ] Form Request classes sudah template-ready
- [ ] Base controller untuk admin CRUD sudah siap
- [ ] Unit test untuk models 80% passed
- [ ] Dokumentasi di `.env.example` updated
- [ ] TIDAK ADA leftover dari development (debug code, console.log, dd(), etc.)

---

## 📝 Checklist & Implementation Guide

### 1. Database Migrations (30 min)

**File location:** `database/migrations/`

#### 1.1 Create Articles Table
```php
// Filename: YYYY_MM_DD_HHMMSS_create_articles_table.php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('title', 100)->unique();
    $table->string('slug')->unique();
    $table->longText('content');
    $table->string('excerpt', 200);
    $table->string('featured_image')->nullable(); // Cloudinary URL/ID
    $table->enum('category', ['berita', 'pengumuman', 'artikel']);
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->unsignedBigInteger('created_by');
    $table->timestamps();
    
    $table->foreign('created_by')->references('id')->on('users');
    $table->index('slug');
    $table->index('status');
    $table->index('published_at');
});
```

**Validation:**
- [ ] Run migration tanpa error: `php artisan migrate`
- [ ] Check table di database: `php artisan tinker` → `DB::table('articles')->getColumns()`
- [ ] Reverse migration works: `php artisan migrate:rollback`

#### 1.2 Create Events Table
```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title', 100);
    $table->string('slug')->unique();
    $table->text('description');
    $table->dateTime('start_date');
    $table->dateTime('end_date');
    $table->string('location', 150);
    $table->string('poster_image')->nullable(); // Cloudinary
    $table->boolean('is_featured')->default(false);
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamps();
    
    $table->index('slug');
    $table->index('start_date');
    $table->index('is_featured');
});
```

#### 1.3 Create Achievements Table
```php
Schema::create('achievements', function (Blueprint $table) {
    $table->id();
    $table->string('title', 150);
    $table->integer('year');
    $table->text('description');
    $table->string('icon_class', 50)->nullable(); // Font Awesome class
    $table->string('achievement_image')->nullable(); // Cloudinary
    $table->integer('order')->default(0);
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
    
    $table->index('year');
    $table->index('is_featured');
    $table->index('order');
});
```

#### 1.4 Create Trainers Table
```php
Schema::create('trainers', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100)->unique();
    $table->string('role', 100); // Koreografer Utama, Trainer, dll
    $table->text('bio')->nullable();
    $table->string('photo_url')->nullable(); // Cloudinary
    $table->string('instagram', 100)->nullable();
    $table->string('youtube', 100)->nullable();
    $table->integer('order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index('order');
    $table->index('is_active');
});
```

#### 1.5 Create Activities Table
```php
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->enum('activity_type', ['pasanggiri', 'ujian']);
    $table->string('title', 150);
    $table->date('date');
    $table->text('description')->nullable();
    $table->string('location', 150)->nullable();
    $table->string('image_url')->nullable(); // Cloudinary
    $table->string('result_link')->nullable(); // URL eksternal (nilai, dokumentasi)
    $table->timestamps();
    
    $table->index('activity_type');
    $table->index('date');
});
```

#### 1.6 Create Hero Slides Table
```php
Schema::create('hero_slides', function (Blueprint $table) {
    $table->id();
    $table->string('title', 100);
    $table->string('subtitle', 150)->nullable();
    $table->string('button_text', 50)->nullable();
    $table->string('button_link')->nullable();
    $table->string('image_desktop')->nullable(); // Cloudinary - 1920x600
    $table->string('image_mobile')->nullable(); // Cloudinary - 480x600
    $table->integer('order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index('order');
    $table->index('is_active');
});
```

#### 1.7 Create Site Settings Table
```php
Schema::create('site_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key', 100)->unique(); // site_name, logo, youtube_url, wa_number, etc
    $table->longText('value')->nullable();
    $table->enum('type', ['text', 'image', 'url', 'number', 'json'])->default('text');
    $table->timestamps();
    
    $table->index('key');
});
```

**❌ DO NOT modify existing tables:** user, kelas, role, absensi, transaksi, etc.

---

### 2. Create Models (40 min)

**File location:** `app/Models/` (create subdirectory `app/Models/Landing/` untuk organization)

#### 2.1 Article Model
```php
// app/Models/Landing/Article.php
namespace App\Models\Landing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
    
    protected $table = 'articles';
    public $timestamps = true;
    
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'featured_image',
        'category', 'status', 'published_at', 'created_by'
    ];
    
    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->orderBy('published_at', 'desc');
    }
    
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
```

#### 2.2 Event Model
```php
// app/Models/Landing/Event.php
class Event extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'start_date', 'end_date',
        'location', 'poster_image', 'is_featured', 'status'
    ];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now())
                     ->orderBy('start_date', 'asc');
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
```

#### 2.3 Achievement Model
```php
// app/Models/Landing/Achievement.php
class Achievement extends Model
{
    protected $fillable = [
        'title', 'year', 'description', 'icon_class',
        'achievement_image', 'order', 'is_featured'
    ];
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                     ->orderBy('order', 'asc');
    }
}
```

#### 2.4-2.7 Trainer, Activity, HeroSlide, SiteSetting Models
Buat similar structure dengan fillable, casts, scopes sesuai kebutuhan.

**Testing:**
- [ ] `php artisan tinker` → `\App\Models\Landing\Article::count()` (should return 0)
- [ ] Model relationships work: `$article->author()->first()`
- [ ] Scopes work: `Article::published()->get()`

---

### 3. Create Authorization Matrix (20 min)

**File location:** `app/Constants/RoleConstant.php` (update existing)

#### 3.1 Define Roles
```php
// app/Constants/RoleConstant.php
class RoleConstant
{
    // Existing roles
    const ROLE_ADMIN = 'admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';
    const ROLE_PARENT = 'parent';
    
    // NEW: Landing page roles
    const ROLE_CONTENT_MANAGER = 'content_manager';
    
    // Permissions matrix untuk landing page
    const LANDING_PERMISSIONS = [
        'admin' => ['create', 'read', 'update', 'delete', 'manage_settings'],
        'content_manager' => ['create', 'read', 'update'], // Own articles only
    ];
}
```

#### 3.2 Database Seeder untuk Roles (jika belum ada)
```php
// database/seeders/LandingRoleSeeder.php
DB::table('roles')->insertOrIgnore([
    ['name' => 'admin', 'created_at' => now()],
    ['name' => 'content_manager', 'created_at' => now()],
]);
```

**Validation:**
- [ ] `php artisan db:seed --class=LandingRoleSeeder`
- [ ] Check roles di database

---

### 4. Create Services Layer (20 min)

**File location:** `app/Services/Landing/`

#### 4.1 ArticleService (Template)
```php
// app/Services/Landing/ArticleService.php
namespace App\Services\Landing;

use App\Models\Landing\Article;
use Illuminate\Support\Str;

class ArticleService
{
    /**
     * Create article dengan slug auto-generate & cache invalidate
     */
    public function createArticle(array $data)
    {
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['created_by'] = auth()->id();
        
        $article = Article::create($data);
        
        // Invalidate cache
        \Cache::forget('articles_latest_3');
        
        return $article;
    }
    
    /**
     * Generate unique slug dengan collision handling
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Article::where('slug', 'like', "$slug%")->count();
        
        return $count ? "$slug-$count" : $slug;
    }
    
    /**
     * Update article & invalidate cache
     */
    public function updateArticle(Article $article, array $data)
    {
        if (isset($data['title']) && $data['title'] !== $article->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }
        
        $article->update($data);
        \Cache::forget('articles_latest_3');
        
        return $article;
    }
}
```

#### 4.2 CloudinaryService Update
**File:** `app/Services/CloudinaryService.php` (jika sudah ada, update)

```php
// Tambahkan method untuk landing page assets
class CloudinaryService
{
    /**
     * Upload gambar untuk landing page dengan transformasi
     */
    public function uploadLandingImage(string $filePath, string $folder): ?string
    {
        try {
            $result = \Cloudinary\Uploader::upload($filePath, [
                'folder' => "kinanti-art/{$folder}",
                'resource_type' => 'auto',
                'transformation' => [
                    ['width' => 'auto', 'quality' => 'auto', 'fetch_format' => 'auto'],
                ]
            ]);
            
            return $result['secure_url'];
        } catch (\Exception $e) {
            \Log::error('Cloudinary upload failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Delete gambar dari Cloudinary
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            \Cloudinary\Uploader::destroy($publicId);
            return true;
        } catch (\Exception $e) {
            \Log::error('Cloudinary delete failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
```

**Validation:**
- [ ] Service methods bisa di-call dari controller: `app(ArticleService::class)->createArticle(...)`

---

### 5. Create Routes & Controllers Base (20 min)

#### 5.1 Routes Setup
```php
// routes/web.php (tambahkan di bawah existing routes)

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Admin Landing Page CMS Routes
    Route::prefix('admin/landing')->group(function () {
        // Hanya admin & content_manager yang bisa akses
        Route::middleware('role:admin,content_manager')->group(function () {
            
            Route::resource('articles', \App\Http\Controllers\Landing\ArticleController::class);
            Route::resource('events', \App\Http\Controllers\Landing\EventController::class);
            Route::resource('achievements', \App\Http\Controllers\Landing\AchievementController::class);
            Route::resource('trainers', \App\Http\Controllers\Landing\TrainerController::class);
            Route::resource('activities', \App\Http\Controllers\Landing\ActivityController::class);
            Route::resource('hero-slides', \App\Http\Controllers\Landing\HeroSlideController::class);
            
            // Hanya admin yang bisa akses settings
            Route::middleware('role:admin')->group(function () {
                Route::resource('settings', \App\Http\Controllers\Landing\SiteSettingController::class);
            });
        });
    });
    
    // Public landing page routes
    Route::get('/', \App\Http\Controllers\Landing\PublicController::class . '@index')->name('landing.home');
    Route::get('/artikel', \App\Http\Controllers\Landing\PublicController::class . '@articles')->name('landing.articles');
    Route::get('/artikel/{slug}', \App\Http\Controllers\Landing\PublicController::class . '@articleDetail')->name('landing.article.detail');
    Route::get('/event', \App\Http\Controllers\Landing\PublicController::class . '@events')->name('landing.events');
    Route::get('/prestasi', \App\Http\Controllers\Landing\PublicController::class . '@achievements')->name('landing.achievements');
    Route::get('/pelatih', \App\Http\Controllers\Landing\PublicController::class . '@trainers')->name('landing.trainers');
    Route::get('/kegiatan', \App\Http\Controllers\Landing\PublicController::class . '@activities')->name('landing.activities');
});
```

**Validation:**
- [ ] `php artisan route:list | grep landing` (semua route tertampil)

#### 5.2 Create Middleware untuk Role Check
```php
// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

class CheckRole
{
    public function handle($request, $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('login');
        }
        
        // Cek role dari user
        $userRole = auth()->user()->role; // asumsi ada column role di users table
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized');
        }
        
        return $next($request);
    }
}
```

Register di `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ... existing
    'role' => \App\Http\Middleware\CheckRole::class,
];
```

---

### 6. Update Dashboard Menu (15 min)

**File location:** `resources/views/layouts/sidebar.blade.php` (or wherever sidebar exists)

```blade
<!-- Tambahkan sebelum </ul> -->
<li class="nav-item">
    <a class="nav-link" href="#landingMenu" data-bs-toggle="collapse" role="button">
        <i class="fas fa-newspaper"></i> CMS Landing
    </a>
    <div id="landingMenu" class="collapse">
        <ul class="nav flex-column ms-3">
            @if (auth()->user()->hasRole(['admin', 'content_manager']))
                <li><a class="nav-link" href="{{ route('articles.index') }}">📄 Artikel</a></li>
                <li><a class="nav-link" href="{{ route('events.index') }}">📅 Event</a></li>
                <li><a class="nav-link" href="{{ route('achievements.index') }}">🏆 Prestasi</a></li>
                <li><a class="nav-link" href="{{ route('trainers.index') }}">👥 Pelatih</a></li>
                <li><a class="nav-link" href="{{ route('activities.index') }}">🎭 Kegiatan</a></li>
                <li><a class="nav-link" href="{{ route('hero-slides.index') }}">🖼️ Hero Slider</a></li>
            @endif
            @if (auth()->user()->hasRole('admin'))
                <li><a class="nav-link" href="{{ route('settings.index') }}">⚙️ Pengaturan</a></li>
            @endif
        </ul>
    </div>
</li>
```

**Validation:**
- [ ] Login as admin → sidebar CMS menu terlihat
- [ ] Login as content_manager → sidebar CMS menu terlihat (tanpa settings)
- [ ] Login as student → sidebar CMS menu tidak terlihat

---

### 7. Create Form Request Templates (15 min)

**File location:** `app/Http/Requests/Landing/`

#### 7.1 StoreArticleRequest
```php
// app/Http/Requests/Landing/StoreArticleRequest.php
namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->hasRole(['admin', 'content_manager']);
    }
    
    public function rules()
    {
        return [
            'title' => 'required|string|min:5|max:100|unique:articles,title',
            'slug' => 'required|string|unique:articles,slug',
            'content' => 'required|string|min:20',
            'excerpt' => 'required|string|min:10|max:200',
            'category' => 'required|in:berita,pengumuman,artikel',
            'featured_image' => 'nullable|image|max:2048|mimetypes:image/jpeg,image/png,image/webp',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date|after_or_equal:today',
        ];
    }
    
    public function messages()
    {
        return [
            'title.required' => 'Judul artikel wajib diisi',
            'title.min' => 'Judul minimal 5 karakter',
            'content.required' => 'Konten artikel wajib diisi',
            'featured_image.max' => 'Gambar maksimal 2MB',
        ];
    }
}
```

Buat similar untuk Event, Achievement, Trainer, Activity, HeroSlide requests.

**Validation:**
- [ ] `php artisan route:list | grep Form` atau check form request bisa instantiate

---

### 8. Create Base Controller (10 min)

```php
// app/Http/Controllers/Landing/BaseLandingController.php
namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;

class BaseLandingController extends Controller
{
    /**
     * Base constructor dengan middleware auth
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }
    
    /**
     * Success response helper
     */
    protected function success($message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    
    /**
     * Error response helper
     */
    protected function error($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
```

---

### 9. Environment Configuration (.env.example) (10 min)

Update `..env.example`:
```
# Landing Page CMS
LANDING_PAGE_ENABLED=true
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

# Cache
CACHE_DRIVER=file
# Or redis untuk production
```

---

### 10. Run Tests (10 min)

```bash
# 1. Run migration
php artisan migrate

# 2. Create test user dengan role
php artisan tinker
> $user = User::first();
> $user->update(['role' => 'admin']);

# 3. Test model relationships
> $article = Article::create([...]);
> $article->author; // should work

# 4. Test routes
php artisan route:list | grep landing

# 5. Test authorization middleware
# Login & visit /admin/landing/articles (should work)
# Visit dengan role yang berbeda (should be denied)
```

---

## 🎯 Definition of Done

- [x] Semua 7 migrations berjalan tanpa error
- [x] Semua 7 models bisa instantiate & relational queries bekerja
- [x] Authorization middleware working (tested manually)
- [x] CloudinaryService updated dengan landing-specific methods
- [x] Routes setup untuk semua CRUD endpoints + public pages
- [x] Dashboard sidebar updated dengan landing menu
- [x] Form Requests template selesai
- [x] Base controller siap dipakai
- [x] `.env.example` updated
- [x] Zero migration rollback errors
- [x] Code reviewed oleh senior dev
- [x] Merged ke `develop` branch

---

## 🔗 Next Steps

Setelah issue ini **100% merged**, assign:
- Developer 1 → [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) + [#02-MODULE-EVENT](./02-MODULE-EVENT.md)
- Developer 2 → [#03-MODULE-ACHIEVEMENT](./03-MODULE-ACHIEVEMENT.md) + [#04-MODULE-TRAINER](./04-MODULE-TRAINER.md)
- Developer 3 → [#05-MODULE-ACTIVITY](./05-MODULE-ACTIVITY.md) + [#06-MODULE-HERO-SLIDER](./06-MODULE-HERO-SLIDER.md)
- Developer 4 → [#07-MODULE-SITE-SETTINGS](./07-MODULE-SITE-SETTINGS.md)

Semua bisa run **parallel** setelah setup selesai.

---

**Created:** May 4, 2026
