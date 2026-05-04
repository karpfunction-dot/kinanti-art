# 08️⃣ PHASE 3: Landing Page Frontend Implementation

**Status:** 🔴 Not Started (Blocked until Phase 2 done)  
**Priority:** 🔴 HIGH  
**Duration:** ~2 days  
**Assignee:** @[dev-frontend]  
**Dependency:** ✅ All Phase 2 modules (#01-#07) must be merged  
**Related Master:** [#00-MASTER-ISSUE](./00-MASTER-ISSUE.md)

---

## 📋 Deskripsi

Implementasi Landing Page Frontend menggunakan Blade template yang menampilkan konten dinamis dari database. Semua data dari CRUD admin sebelumnya. Termasuk: homepage, detail pages (artikel, event, prestasi, pelatih, kegiatan), caching, responsive design.

---

## ✅ Acceptance Criteria

- [ ] Homepage menampilkan: hero slider, 3 featured prestasi, 2 upcoming events, 3 latest artikel, 3 pelatih
- [ ] Artikel page: list 9 items/page, sidebar (kategori filter, latest articles), search
- [ ] Artikel detail page: full content, featured image, author, published date
- [ ] Event page: list upcoming & past events, filter by type
- [ ] Prestasi page: grid view, year filter
- [ ] Pelatih page: grid view, 3 per row responsive
- [ ] Kegiatan page: tab view (Pasanggiri / Ujian), chronological order
- [ ] Caching: homepage queries cached 1 hour, invalidate on content update
- [ ] Performance: FCP < 1.5s, LCP < 2.5s (measured on staging)
- [ ] Responsive: mobile/tablet/desktop tested
- [ ] SEO: meta tags, sitemap, robots.txt
- [ ] Footer: dynamic (dari site settings)
- [ ] Test: functional tests untuk semua pages

---

## 📝 Implementation Checklist

### 1. Create Public Routes & PublicController (30 min)

**File:** `routes/web.php` (add public routes)

```php
// Public landing page routes (NO auth required)
Route::get('/', [\App\Http\Controllers\Landing\PublicController::class, 'index'])
    ->name('landing.home');
Route::get('/artikel', [\App\Http\Controllers\Landing\PublicController::class, 'articles'])
    ->name('landing.articles');
Route::get('/artikel/{slug}', [\App\Http\Controllers\Landing\PublicController::class, 'articleDetail'])
    ->name('landing.article.detail');
Route::get('/event', [\App\Http\Controllers\Landing\PublicController::class, 'events'])
    ->name('landing.events');
Route::get('/event/{slug}', [\App\Http\Controllers\Landing\PublicController::class, 'eventDetail'])
    ->name('landing.event.detail');
Route::get('/prestasi', [\App\Http\Controllers\Landing\PublicController::class, 'achievements'])
    ->name('landing.achievements');
Route::get('/pelatih', [\App\Http\Controllers\Landing\PublicController::class, 'trainers'])
    ->name('landing.trainers');
Route::get('/kegiatan', [\App\Http\Controllers\Landing\PublicController::class, 'activities'])
    ->name('landing.activities');
```

**File:** `app/Http/Controllers/Landing/PublicController.php`

```php
namespace App\Http\Controllers\Landing;

use App\Models\Landing\{Article, Event, Achievement, Trainer, Activity, HeroSlide};
use App\Support\SiteSettings;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Homepage dengan hero slider, featured prestasi, upcoming events, latest articles, pelatih
     */
    public function index(): View
    {
        $data = Cache::remember('landing_homepage', 3600, function () {
            return [
                'slides' => HeroSlide::where('is_active', true)->orderBy('order')->get(),
                'achievements' => Achievement::where('is_featured', true)
                                            ->orderBy('order')
                                            ->limit(3)
                                            ->get(),
                'upcomingEvents' => Event::where('start_date', '>=', now())
                                        ->where('status', 'published')
                                        ->orderBy('start_date')
                                        ->limit(2)
                                        ->get(),
                'latestArticles' => Article::published()
                                          ->limit(3)
                                          ->get(),
                'trainers' => Trainer::where('is_active', true)
                                    ->orderBy('order')
                                    ->limit(3)
                                    ->get(),
            ];
        });
        
        return view('landing.public.home', $data);
    }
    
    /**
     * Artikel list page
     */
    public function articles(): View
    {
        $articles = Article::published()
                           ->paginate(9);
        $categories = ['berita' => 'Berita', 'pengumuman' => 'Pengumuman', 'artikel' => 'Artikel'];
        
        return view('landing.public.articles.index', compact('articles', 'categories'));
    }
    
    /**
     * Artikel detail page
     */
    public function articleDetail(Article $article): View
    {
        if ($article->status !== 'published') {
            abort(404);
        }
        
        $relatedArticles = Article::published()
                                 ->where('id', '!=', $article->id)
                                 ->where('category', $article->category)
                                 ->limit(3)
                                 ->get();
        
        return view('landing.public.articles.show', compact('article', 'relatedArticles'));
    }
    
    /**
     * Event list page
     */
    public function events(): View
    {
        $upcomingEvents = Event::where('start_date', '>=', now())
                              ->where('status', 'published')
                              ->orderBy('start_date')
                              ->paginate(6, ['*'], 'upcoming_page');
        
        $pastEvents = Event::where('start_date', '<', now())
                           ->where('status', 'published')
                           ->orderBy('start_date', 'desc')
                           ->paginate(6, ['*'], 'past_page');
        
        return view('landing.public.events.index', compact('upcomingEvents', 'pastEvents'));
    }
    
    /**
     * Event detail page
     */
    public function eventDetail(Event $event): View
    {
        if ($event->status !== 'published') {
            abort(404);
        }
        
        return view('landing.public.events.show', compact('event'));
    }
    
    /**
     * Prestasi list page
     */
    public function achievements(): View
    {
        $achievements = Achievement::orderBy('is_featured', 'desc')
                                   ->orderBy('year', 'desc')
                                   ->paginate(12);
        
        $years = Achievement::selectRaw('DISTINCT year')
                            ->orderBy('year', 'desc')
                            ->pluck('year');
        
        return view('landing.public.achievements.index', compact('achievements', 'years'));
    }
    
    /**
     * Trainer list page
     */
    public function trainers(): View
    {
        $trainers = Trainer::where('is_active', true)
                          ->orderBy('order')
                          ->get();
        
        return view('landing.public.trainers.index', compact('trainers'));
    }
    
    /**
     * Kegiatan list page dengan tab pasanggiri/ujian
     */
    public function activities(): View
    {
        $pasanggiris = Activity::where('activity_type', 'pasanggiri')
                              ->orderBy('date', 'desc')
                              ->paginate(6, ['*'], 'pasanggiri_page');
        
        $exams = Activity::where('activity_type', 'ujian')
                        ->orderBy('date', 'desc')
                        ->paginate(6, ['*'], 'ujian_page');
        
        return view('landing.public.activities.index', compact('pasanggiris', 'exams'));
    }
}
```

### 2. Create Blade Views (120 min)

**File Structure:**
```
resources/views/
├── landing/
│   └── public/
│       ├── home.blade.php
│       ├── articles/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── events/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── achievements/
│       │   └── index.blade.php
│       ├── trainers/
│       │   └── index.blade.php
│       └── activities/
│           └── index.blade.php
```

**File:** `resources/views/landing/public/home.blade.php`

```blade
@extends('layouts.landing') {{-- atau app, tergantung setup --}}

@section('title', SiteSettings::get('site_name', 'Kinanti Art'))

@section('content')

<!-- Hero Slider -->
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            @forelse ($slides as $slide)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}" 
                     style="min-height: 500px;">
                    <picture>
                        <source media="(max-width: 768px)" srcset="{{ $slide->image_mobile }}">
                        <img src="{{ $slide->image_desktop }}" alt="{{ $slide->title }}" 
                             class="d-block w-100 h-100" style="object-fit: cover;">
                    </picture>
                    <div class="carousel-caption d-none d-md-block">
                        <h1>{{ $slide->title }}</h1>
                        @if ($slide->subtitle)
                            <p>{{ $slide->subtitle }}</p>
                        @endif
                        @if ($slide->button_text && $slide->button_link)
                            <a href="{{ $slide->button_link }}" class="btn btn-primary btn-lg">
                                {{ $slide->button_text }}
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="carousel-item active" style="min-height: 500px; background: #f0f0f0;">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <p>Carousel kosong</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Featured Achievements -->
<section class="achievements-section py-5 bg-light">
    <div class="container">
        <h2 class="mb-4 text-center">Prestasi Unggulan</h2>
        <div class="row">
            @forelse ($achievements as $achievement)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        @if ($achievement->achievement_image)
                            <img src="{{ $achievement->achievement_image }}" 
                                 alt="{{ $achievement->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">
                                @if ($achievement->icon_class)
                                    <i class="{{ $achievement->icon_class }}"></i>
                                @endif
                                {{ $achievement->title }}
                            </h5>
                            <p class="card-text text-muted">{{ $achievement->year }}</p>
                            <p>{{ Str::limit($achievement->description, 100) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">Tidak ada prestasi</div>
            @endforelse
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('landing.achievements') }}" class="btn btn-outline-primary">
                Lihat Semua Prestasi
            </a>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="events-section py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Event Mendatang</h2>
        <div class="row">
            @forelse ($upcomingEvents as $event)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        @if ($event->poster_image)
                            <img src="{{ $event->poster_image }}" alt="{{ $event->title }}" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $event->title }}</h5>
                            <p class="card-text">
                                <i class="fas fa-calendar"></i> 
                                {{ $event->start_date->format('d M Y') }}<br>
                                <i class="fas fa-map-marker-alt"></i> 
                                {{ $event->location }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">Tidak ada event mendatang</div>
            @endforelse
        </div>
    </div>
</section>

<!-- Latest Articles -->
<section class="articles-section py-5 bg-light">
    <div class="container">
        <h2 class="mb-4 text-center">Artikel Terbaru</h2>
        <div class="row">
            @forelse ($latestArticles as $article)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if ($article->featured_image)
                            <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">{{ ucfirst($article->category) }}</span>
                            <h5 class="card-title">{{ $article->title }}</h5>
                            <p class="card-text">{{ $article->excerpt }}</p>
                            <small class="text-muted">
                                {{ $article->published_at->format('d M Y') }}
                            </small>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="{{ route('landing.article.detail', $article) }}" class="btn btn-sm btn-outline-primary">
                                Baca Selengkapnya
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">Tidak ada artikel</div>
            @endforelse
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('landing.articles') }}" class="btn btn-outline-primary">
                Lihat Semua Artikel
            </a>
        </div>
    </div>
</section>

<!-- Trainers -->
<section class="trainers-section py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Tim Pelatih Kami</h2>
        <div class="row">
            @forelse ($trainers as $trainer)
                <div class="col-md-4 mb-4 text-center">
                    <div class="card h-100">
                        @if ($trainer->photo_url)
                            <img src="{{ $trainer->photo_url }}" alt="{{ $trainer->name }}" 
                                 class="card-img-top" style="height: 250px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $trainer->name }}</h5>
                            <p class="text-muted">{{ $trainer->role }}</p>
                            <p class="small">{{ $trainer->bio }}</p>
                            <div class="social-links">
                                @if ($trainer->instagram)
                                    <a href="https://instagram.com/{{ str_replace('@', '', $trainer->instagram) }}" 
                                       target="_blank" class="btn btn-sm btn-link">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                @endif
                                @if ($trainer->youtube)
                                    <a href="{{ $trainer->youtube }}" target="_blank" class="btn btn-sm btn-link">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">Tidak ada pelatih</div>
            @endforelse
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white text-center">
    <div class="container">
        <h2>Bergabunglah dengan Kinanti Art Productions</h2>
        <p>Pelajari seni tari dan koreografi bersama pelatih profesional kami</p>
        <a href="#" class="btn btn-light btn-lg">Daftar Sekarang</a>
    </div>
</section>

@endsection
```

Artikel detail, event list, dan pages lainnya dibuat dengan pattern similar.

### 3. Create Layout Template (20 min)

**File:** `resources/views/layouts/landing.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kinanti Art Productions')</title>
    <meta name="description" content="@yield('description', 'Sanggar Seni Tari Kinanti Art Productions')">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
    
    @yield('extra_css')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing.home') }}">
                <img src="{{ SiteSettings::get('logo') }}" alt="Logo" style="height: 40px;">
                {{ SiteSettings::get('site_name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing.home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing.articles') }}">Artikel</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing.events') }}">Event</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing.achievements') }}">Prestasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing.trainers') }}">Pelatih</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing.activities') }}">Kegiatan</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>{{ SiteSettings::get('site_name') }}</h5>
                    <p>{{ SiteSettings::get('address') }}</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Hubungi Kami</h5>
                    <p>
                        Email: {{ SiteSettings::get('email') }}<br>
                        WhatsApp: {{ SiteSettings::get('wa_number') }}<br>
                        Telepon: {{ SiteSettings::get('phone') }}
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Ikuti Kami</h5>
                    <div class="social-links">
                        @foreach (['instagram' => 'fab fa-instagram', 'youtube' => 'fab fa-youtube', 'tiktok' => 'fab fa-tiktok', 'facebook' => 'fab fa-facebook'] as $key => $icon)
                            @php $url = SiteSettings::get($key . '_url'); @endphp
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-light">
                                    <i class="{{ $icon }}"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; {{ now()->year }} {{ SiteSettings::get('site_name') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('extra_js')
</body>
</html>
```

### 4. Caching Strategy (20 min)

**File:** `app/Listeners/Landing/InvalidateLandingCache.php`

```php
namespace App\Listeners\Landing;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Facades\Cache;

class InvalidateLandingCache
{
    /**
     * Handle cache invalidation ketika article/event/achievement update
     */
    public function handle()
    {
        Cache::forget('landing_homepage');
        Cache::forget('articles_latest_3');
        Cache::forget('achievements_featured_3');
    }
}
```

Register di model observers atau di Service update methods.

### 5. Performance Optimization (20 min)

- Enable query optimization dengan `LIMIT` pada queries
- Use `with()` untuk eager loading relationships
- Implement image lazy loading: `loading="lazy"`
- Minify CSS/JS: `npm run build` (atau setup Vite)
- Use CDN untuk Cloudinary images

### 6. SEO Implementation (15 min)

- Generate sitemap: `php artisan sitemap:generate` (using package)
- Meta tags per page (title, description, og:image)
- Robots.txt: allow public pages, disallow `/admin`

### 7. Tests (30 min)

**File:** `tests/Feature/Landing/PublicPageTest.php`

```php
public function test_homepage_loads(): void
{
    $this->get('/')
        ->assertStatus(200)
        ->assertSee('Prestasi')
        ->assertSee('Event');
}

public function test_article_list_page(): void
{
    Article::factory(10)->published()->create();
    
    $this->get(route('landing.articles'))
        ->assertStatus(200)
        ->assertViewHas('articles');
}

// ... more tests
```

---

## 🎯 Definition of Done

- [ ] All public routes working (no 404)
- [ ] Homepage displays all sections (slider, prestasi, events, artikel, trainers)
- [ ] Artikel list/detail pages working
- [ ] Event list/detail pages working
- [ ] Prestasi page working
- [ ] Trainers page working
- [ ] Activities page working
- [ ] Caching implementation working (verify dengan DB queries)
- [ ] Performance tested: FCP < 1.5s, LCP < 2.5s
- [ ] Responsive design tested (mobile/tablet/desktop)
- [ ] SEO basics implemented
- [ ] Footer displays dynamic settings
- [ ] Tests: 80% coverage for page loading
- [ ] Code reviewed
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
