# 09️⃣ PHASE 4: Validation, Security & Testing

**Status:** 🔴 Not Started (Blocked until Phase 3 done)  
**Priority:** 🔴 CRITICAL  
**Duration:** ~1 day  
**Assignee:** @[qa-team]  
**Dependency:** ✅ Frontend (#08) must be merged  
**Related Master:** [#00-MASTER-ISSUE](./00-MASTER-ISSUE.md)

---

## 📋 Deskripsi

Comprehensive testing & validation untuk semua modul sebelum deployment:
- Security review (OWASP Top 10)
- Performance testing
- Database integrity
- Authorization & access control
- Browser compatibility
- Mobile responsiveness
- Bug fixes
- Final deployment prep

---

## ✅ Acceptance Criteria

- [ ] 0 critical security issues (OWASP Top 10 scan)
- [ ] 0 SQL injection vulnerability
- [ ] 0 XSS vulnerability
- [ ] 0 CSRF bypass
- [ ] FCP < 1.5s, LCP < 2.5s (Google Lighthouse)
- [ ] Lighthouse score: >= 85 (Performance, Accessibility, SEO)
- [ ] All routes tested (admin & public)
- [ ] Authorization matrix 100% tested
- [ ] Database queries < 5 per page (N+1 query fix)
- [ ] No hardcoded secrets (.env checked)
- [ ] All images optimize (Cloudinary transforms applied)
- [ ] Mobile responsiveness: tested on iOS & Android
- [ ] Browser compatibility: Chrome, Firefox, Safari, Edge (latest 2 versions)
- [ ] Staging deployment successful
- [ ] No errors in Laravel logs
- [ ] Admin user acceptance tested
- [ ] Ready for production launch

---

## 📝 Testing Checklist

### 1. Security Testing (60 min)

#### 1.1 OWASP Top 10 Checklist

| Vulnerability | Test | Status | Notes |
|---|---|---|---|
| **Injection (SQL)** | Try `' OR '1'='1` di search fields | ⏳ | Validate: should return 0 results or error |
| **Broken Auth** | Login as content_manager, try access admin settings | ⏳ | Should 403 forbidden |
| **Sensitive Data Exposure** | Check .env in repo | ⏳ | Should not be committed |
| **XML External Entities** | Upload invalid file types | ⏳ | Should reject |
| **Broken Access Control** | Try delete article as content_manager | ⏳ | Should fail |
| **Security Misconfiguration** | Check security headers (HSTS, CSP, X-Frame) | ⏳ | Use Observatory.mozilla.org |
| **XSS** | Try `<script>alert('XSS')</script>` di content field | ⏳ | Should escape/sanitize |
| **CSRF** | Try POST without CSRF token | ⏳ | Should 419 token mismatch |
| **Using Components with Known Vulns** | Run `composer audit` | ⏳ | Should 0 vulnerabilities |
| **Insufficient Logging** | Check audit logs for sensitive actions | ⏳ | Log create/update/delete |

**Action Items:**
- [ ] Run Laravel security scan: `php artisan tinker` → `Security::scan()`
- [ ] Run `composer audit`
- [ ] Check `.env.example` – no secrets
- [ ] Verify CSRF middleware enabled
- [ ] Verify input sanitization on WYSIWYG editor (Quill)
- [ ] Test XSS via Quill: try inject `<img src=x onerror=alert('XSS')>`
- [ ] Enable security headers di `app/Http/Middleware/SecurityHeaders.php`

**Security Headers Implementation:**

```php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

class SecurityHeaders
{
    public function handle($request, $next)
    {
        $response = $next($request);
        
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.quilljs.com; style-src 'self' 'unsafe-inline'");
        
        return $response;
    }
}
```

Register di `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ... existing
    \App\Http\Middleware\SecurityHeaders::class,
];
```

#### 1.2 File Upload Security

**Test Cases:**
- [ ] Upload JPG 2MB – should succeed
- [ ] Upload JPG 3MB – should reject
- [ ] Upload `.exe` – should reject
- [ ] Upload `.svg` with script – should reject
- [ ] Verify filename sanitized (no path traversal)

#### 1.3 Authentication & Authorization

**Test Matrix:**

| User Role | Aksi | Ekspektasi | Status |
|---|---|---|---|
| Public (not logged in) | Visit `/admin/articles` | 401 redirect to login | ⏳ |
| Student | Visit `/admin/articles` | 403 forbidden | ⏳ |
| Content Manager | Create article | 201 created | ⏳ |
| Content Manager | Delete article (milik orang lain) | 403 forbidden | ⏳ |
| Content Manager | Access `/admin/settings` | 403 forbidden | ⏳ |
| Admin | All CRUD actions | 200/201/204 OK | ⏳ |
| Admin | View site settings | 200 OK | ⏳ |

---

### 2. Performance Testing (45 min)

#### 2.1 Google Lighthouse Audit

```bash
# Via Chrome DevTools
1. Open each page in Chrome
2. Right-click → Inspect
3. Lighthouse tab → Generate report
4. Target: Score >= 85 (Performance, Accessibility, SEO)
```

**Pages to test:**
- [ ] Homepage `/` – FCP < 1.5s, LCP < 2.5s
- [ ] Artikel list `/artikel` – FCP < 1.5s
- [ ] Artikel detail `/artikel/{slug}` – FCP < 1.5s
- [ ] Event list `/event` – FCP < 1.5s
- [ ] Admin dashboard `/admin/articles` – FCP < 2s
- [ ] Admin create form `/admin/articles/create` – LCP < 2.5s

#### 2.2 Database Query Monitoring

```php
// In test or local env
DB::enableQueryLog();

// Make request
$this->get(route('landing.articles'));

// Check
$queries = DB::getQueryLog();
$count = count($queries);
echo "Total queries: $count"; // Should be <= 5

// Print queries
foreach ($queries as $q) {
    echo $q['query'];
}
```

**Expected counts:**
- Homepage: < 5 queries (hero slides, achievements, events, articles, trainers)
- Artikel list: < 3 queries (articles + pagination)
- Article detail: < 2 queries (article + author)

**Action if > target:**
- [ ] Add `with()` eager loading
- [ ] Add pagination/limit
- [ ] Add database indexing

#### 2.3 Load Testing (Optional)

```bash
# Menggunakan Apache Bench
ab -n 100 -c 10 http://localhost/

# Expected: requests/sec > 50
```

---

### 3. Database Integrity (30 min)

#### 3.1 Migration Rollback & Rerun

```bash
# Test rollback safety
php artisan migrate:rollback
php artisan migrate

# Verify tables exist & correct columns
php artisan tinker
> Schema::hasTable('articles') // true
> Schema::hasColumn('articles', 'slug') // true
```

**Checklist:**
- [ ] Forward migration OK
- [ ] Backward migration OK
- [ ] No data loss
- [ ] Timestamps correct

#### 3.2 Soft Delete Verification

```php
// Test soft delete
$article = Article::first();
$article->delete();

// Should still exist in DB
Article::withTrashed()->find($article->id); // exists
Article::find($article->id); // null (soft deleted)

// Restore
$article->restore();
Article::find($article->id); // exists again
```

- [ ] Soft delete working (Article model)
- [ ] Other models follow appropriate delete strategy

#### 3.3 Foreign Key Constraints

```php
// Test cascade on delete (if configured)
$user = User::first();
$articles = Article::where('created_by', $user->id)->count();

$user->delete(); // Jika ada cascade, articles juga di-delete

// Verify behavior matches design
```

- [ ] FK relationships defined
- [ ] Cascade rules clear (documented in comments)
- [ ] No orphaned records

---

### 4. Functional Testing (45 min)

#### 4.1 CRUD Operations

**For each module (Article, Event, Achievement, Trainer, Activity, HeroSlide, Settings):**

| Operation | Test Steps | Expected | Status |
|---|---|---|---|
| **Create** | 1. Fill form 2. Submit | Redirect to show/index with success message | ⏳ |
| **Read** | 1. Open list 2. Click item | Display correct data | ⏳ |
| **Update** | 1. Edit form 2. Change field 3. Submit | Data saved & redirected | ⏳ |
| **Delete** | 1. Click delete 2. Confirm | Item removed, success message | ⏳ |
| **Validation** | 1. Submit empty form 2. Submit invalid data | Show error messages | ⏳ |
| **Image Upload** | 1. Upload image 2. Check Cloudinary | Image stored & URL saved | ⏳ |

**Quick test script:**

```bash
# Login as admin
php artisan tinker

$response = $http->post(route('articles.store'), [
    'title' => 'Test',
    'slug' => 'test',
    'content' => 'Content...',
    'excerpt' => 'Exc...',
    'category' => 'berita',
    'status' => 'published',
]);

$response->status(); // 201 or redirect
```

#### 4.2 Search & Filter

| Feature | Test | Expected | Status |
|---|---|---|---|
| **Article search** | Search "seniman" | Show only articles with "seniman" | ⏳ |
| **Event filter by date** | Filter upcoming | Show only start_date >= today | ⏳ |
| **Achievement by year** | Filter 2025 | Show only 2025 | ⏳ |
| **Trainer active** | Toggle is_active | Only show is_active=true di homepage | ⏳ |

#### 4.3 Pagination

- [ ] Artikel page: pagination 9 items/page
- [ ] Event page: pagination 6 items/page
- [ ] Click "next" → load next page
- [ ] Total items / page size = expected page count

#### 4.4 Caching

```php
// Test cache hit
$articles = Article::published()->limit(3)->get();
$time1 = now();

$articles = Article::published()->limit(3)->get(); // from cache
$time2 = now();

// $time2 - $time1 should be much faster

// Test cache invalidation
$article = Article::factory()->create(['status' => 'published']);
Cache::flush(); // should clear articles_latest_3

// Verify cache re-populated
$articles = Article::published()->limit(3)->get();
```

- [ ] First query hits DB
- [ ] Second query hits cache (faster)
- [ ] After create/update/delete, cache invalidates
- [ ] Next query repopulates cache

---

### 5. Responsive Design Testing (30 min)

#### 5.1 Browser DevTools Emulation

| Device | Width | Test | Expected |
|---|---|---|---|
| Mobile | 375px | Homepage loads | Text readable, buttons clickable |
| Tablet | 768px | Article list | 2-column layout |
| Desktop | 1920px | All pages | 3+ column layouts work |

**Test pages:**
- [ ] Homepage
- [ ] Artikel list & detail
- [ ] Event list
- [ ] Prestasi
- [ ] Pelatih
- [ ] Kegiatan
- [ ] Admin forms

#### 5.2 Touch Interactions

On mobile device:
- [ ] Carousel swipe works
- [ ] Menu toggle works
- [ ] Form inputs accessible
- [ ] Buttons easily clickable (min 44x44px)

---

### 6. Browser Compatibility (30 min)

Test each page on:

| Browser | Version | Status | Notes |
|---|---|---|---|
| Chrome | Latest | ⏳ | |
| Firefox | Latest | ⏳ | |
| Safari | Latest | ⏳ | |
| Edge | Latest | ⏳ | |
| iOS Safari | Latest | ⏳ | |
| Android Chrome | Latest | ⏳ | |

**Quick test:**
- [ ] Page loads
- [ ] Images display
- [ ] Forms work
- [ ] Carousel works
- [ ] No console errors

---

### 7. Content Manager User Acceptance Testing (30 min)

**Scenario 1: Create Article**
- [ ] Login as content_manager
- [ ] Navigate to `/admin/articles`
- [ ] Click "Artikel Baru"
- [ ] Fill form (title, content, excerpt, category, image)
- [ ] Publish artikel
- [ ] Verify article appears di `/artikel`
- [ ] Verify author is content_manager
- [ ] Edit own article
- [ ] Try to delete → should fail (403)

**Scenario 2: Create Event**
- [ ] Login as content_manager
- [ ] Create event with start/end date
- [ ] Upload poster
- [ ] Mark as featured
- [ ] Verify on homepage

**Scenario 3: View-only Prestasi**
- [ ] Login as content_manager
- [ ] Try access `/admin/achievements` → should see list (view-only)
- [ ] Try click "Edit" → should be disabled or 403
- [ ] Try click "Delete" → should be disabled or 403

---

### 8. Admin User Acceptance Testing (20 min)

**Scenario: Full Admin Access**
- [ ] Login as admin
- [ ] Access `/admin/landing` → sidebar shows all CMS menus
- [ ] Can CRUD all modules
- [ ] Can access `/admin/landing/settings`
- [ ] Can manage site settings (name, logo, email, etc.)
- [ ] Can delete any article/event/etc.

---

### 9. Bug Fixes & Documentation (30 min)

#### 9.1 Known Issues Resolution

If any issues found during testing:
- [ ] Create issue in GitHub Issues
- [ ] Assign priority (critical/high/medium/low)
- [ ] Fix & test
- [ ] Document in CHANGELOG.md

#### 9.2 Deployment Documentation

Create `DEPLOYMENT.md`:
```markdown
# Deployment Checklist

## Pre-Deployment (Staging)
- [ ] All tests passed
- [ ] Security audit passed
- [ ] Performance targets met
- [ ] DB backup taken

## Deployment Steps
1. SSH to server
2. `git pull origin main`
3. `composer install --no-dev`
4. `php artisan migrate`
5. `php artisan config:cache`
6. `npm run build` (if using Vite)
7. `php artisan cache:clear`

## Post-Deployment
- [ ] Test homepage loads
- [ ] Verify admin can login
- [ ] Check logs for errors
```

---

## 🎯 Definition of Done (Phase 4)

- [ ] Security scan: 0 critical issues
- [ ] `composer audit`: 0 vulnerabilities
- [ ] Authorization matrix: 100% tested
- [ ] Performance: Lighthouse >= 85
- [ ] Query count: < 5 per page
- [ ] CRUD: All operations tested
- [ ] Search/Filter: All working
- [ ] Pagination: Correct behavior
- [ ] Caching: Hit/miss/invalidation working
- [ ] Mobile responsiveness: Tested
- [ ] Browser compatibility: 6 browsers OK
- [ ] Admin UAT: Content manager & admin approve
- [ ] Staging deployment: Successful
- [ ] CHANGELOG.md updated
- [ ] Deployment guide written
- [ ] Ready for production

---

**Created:** May 4, 2026
