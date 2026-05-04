# 📑 INDEX: Landing Page CMS Issues

**Last Updated:** May 4, 2026  
**Total Issues:** 13 files  
**Status:** 🔴 Ready for Team Assignment

---

## 🗂️ Struktur Issue

### 📌 Master & Setup

| # | File | Durasi | Priority | Status |
|---|------|--------|----------|--------|
| **MASTER** | [00-MASTER-ISSUE.md](./00-MASTER-ISSUE.md) | 2 weeks | 🔴 CRITICAL | 📘 Reference |
| **00** | [00-SETUP-AND-PREPARATION.md](./00-SETUP-AND-PREPARATION.md) | 1 day | 🔴 BLOCKING | 🔴 Not Started |

---

### 🎯 Phase 2: Backend CRUD Modules (Parallel)

**Durasi Total:** ~3.5 days (parallel)  
**Blocking:** Harus tunggu Phase 1 selesai

| # | Module | Durasi | Assignee Saran | Status |
|---|--------|--------|-----------------|--------|
| **01** | [01-MODULE-ARTICLE.md](./01-MODULE-ARTICLE.md) | 0.5d | Dev A | 🔴 Not Started |
| **02** | [02-MODULE-EVENT.md](./02-MODULE-EVENT.md) | 0.5d | Dev A | 🔴 Not Started |
| **03** | [03-MODULE-ACHIEVEMENT.md](./03-MODULE-ACHIEVEMENT.md) | 0.5d | Dev B | 🔴 Not Started |
| **04** | [04-MODULE-TRAINER.md](./04-MODULE-TRAINER.md) | 0.5d | Dev B | 🔴 Not Started |
| **05** | [05-MODULE-ACTIVITY.md](./05-MODULE-ACTIVITY.md) | 0.5d | Dev C | 🔴 Not Started |
| **06** | [06-MODULE-HERO-SLIDER.md](./06-MODULE-HERO-SLIDER.md) | 0.5d | Dev C | 🔴 Not Started |
| **07** | [07-MODULE-SITE-SETTINGS.md](./07-MODULE-SITE-SETTINGS.md) | 0.5d | Dev C | 🔴 Not Started |

---

### 🎨 Phase 3: Frontend Landing Page

| # | File | Durasi | Priority | Blocking |
|---|------|--------|----------|----------|
| **08** | [08-LANDING-PAGE-FRONTEND.md](./08-LANDING-PAGE-FRONTEND.md) | 2 days | 🔴 HIGH | All Phase 2 ✅ |

---

### ✅ Phase 4: Testing & Validation

| # | File | Durasi | Priority | Blocking |
|---|------|--------|----------|----------|
| **09** | [09-VALIDATION-AND-SECURITY-CHECKLIST.md](./09-VALIDATION-AND-SECURITY-CHECKLIST.md) | 1 day | 🔴 CRITICAL | Phase 3 ✅ |

---

### 📖 Phase 5: Documentation (Post-Launch)

| # | File | Durasi | Priority | Optional |
|---|------|--------|----------|----------|
| **10** | [10-DOCUMENTATION-AND-TRAINING.md](./10-DOCUMENTATION-AND-TRAINING.md) | 0.5d | 🟡 MEDIUM | ✅ Yes |

---

### 🔐 Reference Documents

| File | Tujuan | Audience |
|------|--------|----------|
| [AUTHORIZATION_AND_PERMISSIONS.md](./AUTHORIZATION_AND_PERMISSIONS.md) | Permission matrix & implementation | Junior Dev, QA |
| [README.md](#) | High-level overview | Team |

---

## 🚀 Quick Start

### Untuk Team Lead:

1. **Baca dulu:** [00-MASTER-ISSUE.md](./00-MASTER-ISSUE.md)
2. **Assign** developer ke issues sesuai fase
3. **Kelompokkan:**
   - Phase 1 (Setup): 1 senior dev
   - Phase 2 (Backend): 3 dev parallel (2 modul per dev)
   - Phase 3 (Frontend): 1 dev frontend
   - Phase 4 (Testing): 1 QA + 1 senior dev
   - Phase 5 (Docs): 1 doc writer

### Untuk Junior Dev:

1. **Baca:** [AUTHORIZATION_AND_PERMISSIONS.md](./AUTHORIZATION_AND_PERMISSIONS.md) → pahami permission model
2. **Baca:** Issue yang di-assign ke kamu
3. **Ikuti:** Checklist di setiap issue
4. **Test:** Sebelum submit PR
5. **Ask:** Jika ada yang tidak jelas

---

## 📊 Timeline Estimate

```
Setup (Day 1)                ║ 1 dev senior
                             ║
Article (0.5d) ──┐           ║ Phase 2
Event (0.5d) ────┼─ Dev A    ║ 3 dev parallel
Achievement ─────┤ Dev B     ║ ~1.75 days
Trainer (0.5d) ──┤           ║
Activity (0.5d) ─┼─ Dev C    ║
HeroSlider ──────┘           ║
Settings (0.5d) ─┘           ║
                             ║
Frontend (2 days)            ║ 1 dev frontend
Testing (1 day)              ║ 1 QA + 1 senior dev
Documentation (0.5d)         ║ 1 doc writer (optional)

Total: ~2 weeks (critical path)
```

---

## 📌 Dependency Graph

```
00-SETUP (Day 1)
    ↓
    ├─→ 01-ARTICLE (0.5d)
    ├─→ 02-EVENT (0.5d)
    ├─→ 03-ACHIEVEMENT (0.5d)
    ├─→ 04-TRAINER (0.5d)
    ├─→ 05-ACTIVITY (0.5d)
    ├─→ 06-HERO-SLIDER (0.5d)
    └─→ 07-SITE-SETTINGS (0.5d)
            ↓
            All must merge to develop
            ↓
    08-FRONTEND (2 days)
            ↓
    09-VALIDATION (1 day)
            ↓
    ✅ Ready for Production
```

---

## ✅ Definition of Ready (Before Start)

**Prerequisites sebelum team mulai:**

- [ ] Semua dev sudah baca BRD/FSD/NFR
- [ ] Environment setup (composer install, npm install, .env configured)
- [ ] Database connection verified
- [ ] Cloudinary credentials configured
- [ ] Git workflow explained (feature branch → PR → review → merge)
- [ ] Slack channel created (#landing-page-cms)
- [ ] Daily standup scheduled

---

## 🔗 Related Documentation

- **BRD (Business Requirements):** [../BRD.md](#)
- **FSD (Functional Specifications):** [../FSD.md](#)
- **NFR (Non-Functional Requirements):** [../NFR.md](#)
- **Architecture Diagram:** [../ARCHITECTURE.md](#)

---

## 💬 Communication

**Slack:** #landing-page-cms  
**Standup:** Daily 10:00 AM  
**Code Review:** Within 24 hours  
**Issue Tracking:** GitHub Issues  

---

**Created:** May 4, 2026  
**Version:** 1.0
