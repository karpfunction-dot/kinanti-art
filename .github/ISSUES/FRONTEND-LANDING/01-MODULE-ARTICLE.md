# 01️⃣ MODULE: Article/Berita Management

**Status:** 🔴 Not Started  
**Priority:** 🔴 HIGH (Core feature)  
**Duration:** ~0.5 day  
**Assignee:** @[dev-1]  
**Dependency:** ✅ [#00-SETUP-AND-PREPARATION](./00-SETUP-AND-PREPARATION.md) MUST be merged first  
**Related Master:** [#00-MASTER-ISSUE](./00-MASTER-ISSUE.md)

---

## 📋 Deskripsi

Implementasi CRUD untuk modul Article (Berita/Artikel) dengan fitur:
- Create/Edit artikel dengan WYSIWYG editor (Quill)
- Upload featured image via Cloudinary
- Auto-generate slug dari title dengan collision handling
- Publish/Draft management
- Category filter (berita, pengumuman, artikel)
- Cache invalidation otomatis

---

## ✅ Acceptance Criteria

- [ ] CRUD lengkap: Create, Read (list + detail), Update, Delete
- [ ] Featured image upload via Cloudinary (max 2MB, validasi format)
- [ ] Slug auto-generate unique (collision handling)
- [ ] WYSIWYG editor Quill terintegrasi di form
- [ ] Publish/Draft workflow dengan published_at timestamp
- [ ] Filter by category & status di list page
- [ ] Search by title & excerpt
- [ ] Pagination 9 items per page
- [ ] Cache invalidation saat create/update/delete
- [ ] Authorization: Admin full access, Content Manager create own only
- [ ] Test coverage: 80% CRUD paths
- [ ] No hardcoded data (semua dari database)
- [ ] Responsive design (mobile/tablet/desktop)

---

## 📝 Implementation Checklist

### 1. Create ArticleController (30 min)

**File:** `app/Http/Controllers/Landing/ArticleController.php`

```php
namespace App\Http\Controllers\Landing;

use App\Models\Landing\Article;
use App\Http\Requests\Landing\StoreArticleRequest;
use App\Http\Requests\Landing\UpdateArticleRequest;
use App\Services\Landing\ArticleService;
use Illuminate\Support\Facades\Gate;

class ArticleController extends BaseLandingController
{
    public function __construct(protected ArticleService $service)
    {
        parent::__construct();
    }
    
    /**
     * Display list of articles
     */
    public function index()
    {
        $query = Article::query();
        
        // Filter by status
        if ($status = request('status')) {
            $query->where('status', $status);
        }
        
        // Filter by category
        if ($category = request('category')) {
            $query->where('category', $category);
        }
        
        // Search
        if ($search = request('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
        }
        
        // Pagination
        $articles = $query->orderBy('created_at', 'desc')
                          ->paginate(9);
        
        return view('landing.articles.index', [
            'articles' => $articles,
            'statuses' => ['draft' => 'Draft', 'published' => 'Published'],
            'categories' => ['berita' => 'Berita', 'pengumuman' => 'Pengumuman', 'artikel' => 'Artikel'],
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        return view('landing.articles.create', [
            'categories' => ['berita' => 'Berita', 'pengumuman' => 'Pengumuman', 'artikel' => 'Artikel'],
            'statuses' => ['draft' => 'Draft', 'published' => 'Published'],
        ]);
    }
    
    /**
     * Store article
     */
    public function store(StoreArticleRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Upload featured image jika ada
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = app(CloudinaryService::class)
                    ->uploadLandingImage(
                        $request->file('featured_image')->getRealPath(),
                        'articles'
                    );
            }
            
            $article = $this->service->createArticle($data);
            
            return redirect()->route('articles.show', $article)
                           ->with('success', 'Artikel berhasil dibuat');
        } catch (\Exception $e) {
            \Log::error('Article creation failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal membuat artikel: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show article detail
     */
    public function show(Article $article)
    {
        $this->authorize('view', $article);
        return view('landing.articles.show', compact('article'));
    }
    
    /**
     * Show edit form
     */
    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        
        return view('landing.articles.edit', [
            'article' => $article,
            'categories' => ['berita' => 'Berita', 'pengumuman' => 'Pengumuman', 'artikel' => 'Artikel'],
            'statuses' => ['draft' => 'Draft', 'published' => 'Published'],
        ]);
    }
    
    /**
     * Update article
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        try {
            $this->authorize('update', $article);
            
            $data = $request->validated();
            
            // Upload featured image jika ada file baru
            if ($request->hasFile('featured_image')) {
                // Delete old image dari Cloudinary (opsional)
                // app(CloudinaryService::class)->deleteImage($article->featured_image);
                
                $data['featured_image'] = app(CloudinaryService::class)
                    ->uploadLandingImage(
                        $request->file('featured_image')->getRealPath(),
                        'articles'
                    );
            }
            
            $this->service->updateArticle($article, $data);
            
            return redirect()->route('articles.show', $article)
                           ->with('success', 'Artikel berhasil diperbarui');
        } catch (\Exception $e) {
            \Log::error('Article update failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal memperbarui artikel: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete article (soft delete)
     */
    public function destroy(Article $article)
    {
        try {
            $this->authorize('delete', $article);
            
            $article->delete();
            \Cache::forget('articles_latest_3');
            
            return redirect()->route('articles.index')
                           ->with('success', 'Artikel berhasil dihapus');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus artikel']);
        }
    }
}
```

**Validation:**
- [ ] Controller ada di `app/Http/Controllers/Landing/ArticleController.php`
- [ ] Semua method ada (index, create, store, show, edit, update, destroy)
- [ ] Error handling di setiap method (try-catch)

### 2. Create Form Requests (10 min)

**File:** `app/Http/Requests/Landing/StoreArticleRequest.php`
```php
namespace App\Http\Requests\Landing;

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
            'title.unique' => 'Judul artikel sudah ada',
            'slug.unique' => 'Slug sudah dipakai',
            'content.required' => 'Konten artikel wajib diisi',
            'featured_image.max' => 'Gambar maksimal 2MB',
            'featured_image.mimetypes' => 'Format gambar harus JPEG, PNG, atau WebP',
        ];
    }
}
```

**File:** `app/Http/Requests/Landing/UpdateArticleRequest.php`
```php
// Similar dengan Store tapi slug unique:articles,slug,id bukan hanya title
class UpdateArticleRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|min:5|max:100|unique:articles,title,' . $this->article->id,
            'slug' => 'required|string|unique:articles,slug,' . $this->article->id,
            // ... rest same as Store
        ];
    }
}
```

**Validation:**
- [ ] Form Request rules cover semua required fields
- [ ] Unique validation berjalan dengan baik

### 3. Create Blade Views (45 min)

**File:** `resources/views/landing/articles/index.blade.php`
```blade
@extends('layouts.app')

@section('title', 'Manajemen Artikel')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📄 Artikel</h1>
        <a href="{{ route('articles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Artikel Baru
        </a>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari judul..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- List -->
    @if ($articles->count())
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Penulis</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($articles as $article)
                        <tr>
                            <td>
                                <strong>{{ $article->title }}</strong><br>
                                <small class="text-muted">{{ $article->slug }}</small>
                            </td>
                            <td>{{ ucfirst($article->category) }}</td>
                            <td>
                                <span class="badge bg-{{ $article->status === 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td>{{ $article->author?->name ?? 'N/A' }}</td>
                            <td>{{ $article->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('articles.show', $article) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('articles.edit', $article) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('articles.destroy', $article) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        {{ $articles->links() }}
    @else
        <div class="alert alert-info">Tidak ada artikel</div>
    @endif
</div>
@endsection
```

**File:** `resources/views/landing/articles/create.blade.php` & `edit.blade.php`
```blade
@extends('layouts.app')

@section('title', isset($article) ? 'Edit Artikel' : 'Buat Artikel Baru')

@section('content')
<div class="container mt-4">
    <h1>{{ isset($article) ? 'Edit Artikel' : 'Artikel Baru' }}</h1>
    
    <form action="{{ isset($article) ? route('articles.update', $article) : route('articles.store') }}" 
          method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        @if (isset($article))
            @method('PUT')
        @endif
        
        <div class="row">
            <div class="col-md-8">
                <!-- Judul -->
                <div class="mb-3">
                    <label class="form-label">Judul *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $article->title ?? '') }}" 
                           @change="generateSlug">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Slug -->
                <div class="mb-3">
                    <label class="form-label">Slug *</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $article->slug ?? '') }}">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Content with Quill Editor -->
                <div class="mb-3">
                    <label class="form-label">Konten *</label>
                    <div id="editor" class="@error('content') is-invalid @enderror"></div>
                    <input type="hidden" name="content" id="content" 
                           value="{{ old('content', $article->content ?? '') }}">
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Excerpt -->
                <div class="mb-3">
                    <label class="form-label">Ringkasan *</label>
                    <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" 
                              rows="2" maxlength="200">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                    <small class="text-muted">Max 200 karakter</small>
                    @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Kategori -->
                <div class="mb-3">
                    <label class="form-label">Kategori *</label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" 
                                {{ old('category', $article->category ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" 
                                {{ old('status', $article->status ?? 'draft') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Published Date -->
                <div class="mb-3">
                    <label class="form-label">Tanggal Publikasi</label>
                    <input type="date" name="published_at" class="form-control @error('published_at') is-invalid @enderror"
                           value="{{ old('published_at', $article?->published_at?->format('Y-m-d')) }}">
                    @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Featured Image -->
                <div class="mb-3">
                    <label class="form-label">Gambar Unggulan</label>
                    <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror"
                           accept="image/*">
                    <small class="text-muted">Max 2MB (JPEG, PNG, WebP)</small>
                    
                    @if (isset($article) && $article->featured_image)
                        <div class="mt-2">
                            <img src="{{ $article->featured_image }}" alt="Featured" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    @endif
                    @error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <!-- Submit -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        {{ isset($article) ? 'Perbarui' : 'Buat' }} Artikel
                    </button>
                    <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Quill Editor Script -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slugify@1.6.5/slugify.min.js"></script>

<script>
    var quill = new Quill('#editor', { theme: 'snow' });
    
    // Load existing content
    @if (isset($article))
        quill.root.innerHTML = @json($article->content);
    @endif
    
    // Auto-generate slug
    document.querySelector('input[name="title"]').addEventListener('change', function() {
        const slug = slugify(this.value, { lower: true, locale: 'id' });
        document.querySelector('input[name="slug"]').value = slug;
    });
    
    // Submit: copy Quill content to hidden input
    document.querySelector('form').addEventListener('submit', function() {
        document.querySelector('#content').value = quill.root.innerHTML;
    });
</script>
@endsection
```

**Validation:**
- [ ] Create form punya all fields + Quill editor
- [ ] Edit form load existing data
- [ ] Featured image preview
- [ ] Error messages tampil

### 4. Create Policy (Authorization) (10 min)

**File:** `app/Policies/Landing/ArticlePolicy.php`

```php
namespace App\Policies\Landing;

use App\Models\User;
use App\Models\Landing\Article;

class ArticlePolicy
{
    /**
     * Content Manager hanya bisa edit artikel miliknya
     */
    public function update(User $user, Article $article): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return $user->hasRole('content_manager') && $article->created_by === $user->id;
    }
    
    /**
     * Hanya admin yang bisa delete
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->hasRole('admin');
    }
    
    public function view(User $user, Article $article): bool
    {
        return true; // semua auth user bisa view
    }
}
```

Register di `app/Providers/AuthServiceProvider.php`:
```php
use App\Models\Landing\Article;
use App\Policies\Landing\ArticlePolicy;

protected $policies = [
    // ... existing
    Article::class => ArticlePolicy::class,
];
```

### 5. Create Tests (20 min)

**File:** `tests/Feature/Landing/ArticleTest.php`

```php
namespace Tests\Feature\Landing;

use Tests\TestCase;
use App\Models\User;
use App\Models\Landing\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;
    
    private $admin;
    private $contentManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->contentManager = User::factory()->create(['role' => 'content_manager']);
    }
    
    /** @test */
    public function admin_can_create_article()
    {
        $this->actingAs($this->admin)
            ->post(route('articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'This is test content for article',
                'excerpt' => 'Test excerpt',
                'category' => 'berita',
                'status' => 'published',
            ])
            ->assertRedirect();
        
        $this->assertDatabaseHas('articles', ['title' => 'Test Article']);
    }
    
    /** @test */
    public function content_manager_can_view_articles()
    {
        $this->actingAs($this->contentManager)
            ->get(route('articles.index'))
            ->assertOk();
    }
    
    /** @test */
    public function content_manager_can_edit_own_article()
    {
        $article = Article::factory()->create(['created_by' => $this->contentManager->id]);
        
        $this->actingAs($this->contentManager)
            ->put(route('articles.update', $article), ['title' => 'Updated'])
            ->assertRedirect();
    }
    
    /** @test */
    public function content_manager_cannot_delete_article()
    {
        $article = Article::factory()->create(['created_by' => $this->contentManager->id]);
        
        $this->actingAs($this->contentManager)
            ->delete(route('articles.destroy', $article))
            ->assertForbidden();
    }
}
```

**Run tests:**
```bash
php artisan test tests/Feature/Landing/ArticleTest.php
```

---

## 🎯 Definition of Done (Module)

- [ ] ArticleController berjalan tanpa error
- [ ] Semua CRUD action tested (5+ test cases passed)
- [ ] Create/Edit form dengan Quill editor working
- [ ] Featured image upload via Cloudinary working
- [ ] Slug auto-generate unique (tested collision)
- [ ] Cache invalidation saat create/update/delete
- [ ] Authorization policy working (admin full, content manager partial)
- [ ] List page searchable & filterable
- [ ] Pagination working (9 items per page)
- [ ] Responsive design tested di mobile/tablet/desktop
- [ ] Code reviewed oleh senior dev
- [ ] Merged ke `develop` branch

---

## 📞 Notes

- Use Quill.js untuk simplicity (bukan TinyMCE - terlalu heavy untuk v1)
- Image harus melalui Cloudinary (jangan local storage)
- Slug collision handling: append "-2", "-3" dst
- Cache key: `articles_latest_3` (invalidate saat ada perubahan)

---

**Created:** May 4, 2026
