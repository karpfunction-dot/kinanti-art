# 07️⃣ MODULE: Site Settings Management

**Status:** 🔴 Not Started  
**Priority:** 🔴 MEDIUM (Global config)  
**Duration:** ~0.5 day  
**Assignee:** @[dev-3]  
**Dependency:** ✅ [#00-SETUP](./00-SETUP-AND-PREPARATION.md)  
**Pattern:** Follow [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) structure

---

## 📋 Deskripsi

CRUD untuk Site Settings / Pengaturan Global yang digunakan di footer & public pages. Includes: kontak, sosial media, logo, alamat, email, WhatsApp, YouTube channel.

---

## ✅ Acceptance Criteria

- [ ] CRUD untuk site_settings table
- [ ] Form dinamis berdasarkan key-value pairs
- [ ] Support multiple types: text, image, url, number, json
- [ ] Logo & favicon upload via Cloudinary
- [ ] Social media links (Instagram, YouTube, TikTok, Facebook, Twitter)
- [ ] Contact info (email, WhatsApp, phone, address)
- [ ] Cache invalidation on update
- [ ] Only admin can access
- [ ] Test: 80%

---

## ✅ Acceptance Criteria

- [ ] CRUD untuk site_settings table
- [ ] Form dinamis berdasarkan key-value pairs
- [ ] Support multiple types: text, image, url, number, json
- [ ] Logo & favicon upload via Cloudinary
- [ ] Social media links (Instagram, YouTube, TikTok, Facebook, Twitter)
- [ ] Contact info (email, WhatsApp, phone, address)
- [ ] Cache invalidation on update
- [ ] Only admin can access
- [ ] Test: 80%

---

## 📝 Quick Implementation

### 1. Controller
- index/edit: single form (bukan list) dengan semua settings fields
- update: batch update multiple keys + cache flush
- No delete action (settings tetap exist tapi bisa di-reset)

### 2. Default Settings (Seeder)

```php
// database/seeders/SiteSettingsSeeder.php
$settings = [
    ['key' => 'site_name', 'value' => 'Kinanti Art Productions', 'type' => 'text'],
    ['key' => 'logo', 'value' => '', 'type' => 'image'],
    ['key' => 'favicon', 'value' => '', 'type' => 'image'],
    ['key' => 'email', 'value' => 'info@kinanti.local', 'type' => 'text'],
    ['key' => 'wa_number', 'value' => '62812345678', 'type' => 'text'],
    ['key' => 'phone', 'value' => '', 'type' => 'text'],
    ['key' => 'address', 'value' => '', 'type' => 'text'],
    ['key' => 'youtube_url', 'value' => '', 'type' => 'url'],
    ['key' => 'instagram_url', 'value' => '', 'type' => 'url'],
    ['key' => 'tiktok_url', 'value' => '', 'type' => 'url'],
    ['key' => 'facebook_url', 'value' => '', 'type' => 'url'],
    ['key' => 'twitter_url', 'value' => '', 'type' => 'url'],
];

foreach ($settings as $setting) {
    SiteSetting::firstOrCreate(['key' => $setting['key']], $setting);
}
```

### 3. Form Request
```php
class UpdateSiteSettingRequest extends FormRequest
{
    public function rules()
    {
        return [
            'site_name' => 'required|string',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'email' => 'required|email',
            'wa_number' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'required|string',
            'youtube_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            // ... dst
        ];
    }
}
```

### 4. Service Helper
```php
// app/Support/SiteSettings.php
class SiteSettings
{
    /**
     * Get setting value by key dengan cache
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.$key", 3600, function () use ($key, $default) {
            return SiteSetting::where('key', $key)->first()?->value ?? $default;
        });
    }
    
    /**
     * Set setting value
     */
    public static function set(string $key, $value)
    {
        $setting = SiteSetting::firstOrCreate(['key' => $key]);
        $setting->update(['value' => $value]);
        Cache::forget("setting.$key");
    }
}

// Usage di Blade:
{{ SiteSettings::get('site_name') }}
{{ SiteSettings::get('wa_number') }}
```

### 5. Views
- Single page form (tidak list) dengan tabbed sections:
  - Tab 1: Site Info (name, logo, favicon)
  - Tab 2: Contact (email, phone, wa_number, address)
  - Tab 3: Social Media (youtube, instagram, tiktok, facebook, twitter)
- Preview images uploaded

### 6. Tests
- Update setting value
- Cache invalidation on update
- Get setting dengan cache hit
- Image upload (logo, favicon)

---

## 📌 Kolom Tabel

```sql
id, key (unique), value, type (text/image/url/number/json), 
created_at, updated_at
```

---

## 📌 Usage di Public Pages

Footer example (Blade):
```blade
<footer>
    <p>{{ SiteSettings::get('site_name') }}</p>
    <p>{{ SiteSettings::get('address') }}</p>
    <p>Email: {{ SiteSettings::get('email') }}</p>
    <p>WhatsApp: {{ SiteSettings::get('wa_number') }}</p>
    
    <ul class="social">
        @if ($instagram = SiteSettings::get('instagram_url'))
            <li><a href="{{ $instagram }}">Instagram</a></li>
        @endif
        <!-- ... dst -->
    </ul>
</footer>
```

---

## 🎯 Definition of Done

- [ ] Settings form functional
- [ ] All default settings seeded
- [ ] Image uploads (logo, favicon) working
- [ ] Cache hit/miss working correctly
- [ ] SiteSettings helper accessible from views
- [ ] Only admin can access
- [ ] Tests passed
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
