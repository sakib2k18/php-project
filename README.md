# KUET TRY — Humanitarian Organization Website

A complete, production-quality website for **KUET TRY**, a humanitarian organisation founded by
students, alumni and teachers of Khulna University of Engineering & Technology. It raises funds for
disaster relief, food distribution, medical assistance, education support and other social welfare
work across the south-west of Bangladesh.

Built as a university project with **Laravel 12, PHP 8.2, MySQL/MariaDB, Blade, Tailwind CSS 4 and
vanilla JavaScript** — no React, Vue, Livewire, or unnecessary third-party packages.

---

## Table of contents

1. [Project overview](#1-project-overview)
2. [Features](#2-features)
3. [Requirements](#3-requirements)
4. [Installation](#4-installation)
5. [Running the site](#5-running-the-site)
6. [Login credentials](#6-login-credentials)
7. [Important URLs](#7-important-urls)
8. [Database](#8-database)
9. [API integrations](#9-api-integrations)
10. [Architecture](#10-architecture)
11. [Security](#11-security)
12. [Testing](#12-testing)
13. [Artisan command reference](#13-artisan-command-reference)
14. [Assumptions and design decisions](#14-assumptions-and-design-decisions)
15. [Possible improvements](#15-possible-improvements)

---

## 1. Project overview

KUET TRY publishes fundraising **campaigns**, records what those campaigns became as **projects**,
and tells the stories of the families supported. Visitors can browse everything without an account;
registered supporters can record donations, track their verification and download receipts; a single
administrator manages all content and verifies every donation.

The defining design decision is that **this site does not process card payments**. Instead:

```
Supporter transfers money directly (bank / bKash / Nagad / Rocket / cash)
        ↓
Records the donation here with the transaction reference   →  status: PENDING
        ↓
Administrator matches it against the bank statement         →  status: APPROVED
        ↓
Campaign total updates inside a database transaction
        ↓
A printable receipt appears in the supporter's dashboard
```

Only **approved** donations contribute to a campaign's `raised_amount`, so every figure on the site
can be traced back to a real statement line. This is both safer than faking a payment gateway in a
university project and a more honest model for a small organisation.

**Laravel version:** 12.68
**PHP version required:** 8.2 or newer
**Database:** MySQL / MariaDB, database `kuet_try`, port **4306**

> Laravel 13 requires PHP 8.3+. This machine runs PHP 8.2.12 (XAMPP), so the project targets the
> latest framework version that PHP 8.2 supports — Laravel 12 — and uses its current conventions
> throughout (`bootstrap/app.php` middleware registration, `Gate::policy()`, form requests, etc.).

---

## 2. Features

### Public website
- **Homepage** — hero, live impact statistics, emergency appeal spotlight, announcements, featured
  campaigns, our work, impact stories, upcoming events, latest news, weather, map and gallery strip.
- **Campaigns** — searchable and filterable index (category, status, four sort orders) with a detail
  page showing progress, field updates, recent supporters, related campaigns and a location map.
- **Projects** — completed and ongoing work, filterable, with detail pages.
- **Events** — upcoming and past, with times, locations and maps.
- **Impact stories** — featured story, search, detail pages linked back to the funding campaign.
- **News / blog** — featured lead article, categories, search, sidebar, article pages.
- **Gallery** — category-filtered grid with a keyboard-accessible lightbox.
- **About / Team / Get Involved / Contact** pages.
- **Site-wide search** across campaigns, projects, stories and news.
- `sitemap.xml` and `robots.txt`.

### Supporter area
- Registration, login, logout, remember-me, password reset.
- Dashboard with donation statistics, campaigns, events, announcements and notifications.
- Donation form, donation history (own records only), donation detail, printable receipt.
- Volunteer application with status tracking and editing while pending.
- Profile editing, avatar upload, password change, account closure.
- Database notifications for donation and volunteer decisions.

### Administration panel (`/admin`)
- Dashboard with eight live statistic cards, four Chart.js charts, work queues and an activity feed.
- Full CRUD for campaigns (plus field updates), projects, events, success stories, news articles,
  announcements, gallery images and team members.
- Donation verification (approve / reject / reopen), donation reports with date filtering, and a
  "recalculate campaign totals" repair action.
- Volunteer review, user management, contact message inbox, activity log.
- Organisation settings — name, tagline, about, mission, vision, contact details, social links,
  coordinates, donation instructions, logo and favicon.

---

## 3. Requirements

| Requirement | Version | Notes |
|---|---|---|
| PHP | 8.2+ | XAMPP's bundled PHP is fine |
| Composer | 2.x | |
| MySQL / MariaDB | 5.7+ / 10.4+ | **running on port 4306** |
| Node.js + npm | 18+ / 9+ | build-time only, not needed to run the site |
| Apache | XAMPP | optional — `php artisan serve` also works |

Required PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`, `curl`.

> **Optional:** enabling `extension=gd` in `php.ini` lets one image-dimension test run instead of
> skipping. The application itself does **not** need GD — image validation uses `getimagesize()` and
> `finfo`, both of which are in PHP core.

---

## 4. Installation

### 1. Start XAMPP
Start **Apache** and **MySQL** from the XAMPP control panel, and make sure MySQL is listening on
port **4306** (not the default 3306).

### 2. Put the project in place
Either keep it where it is and use `php artisan serve`, or copy it into
`C:\xampp\htdocs\kuet-try` to serve it through Apache.

### 3. Install PHP dependencies
```bash
composer install
```

### 4. Create the environment file
```bash
copy .env.example .env
```

Confirm the database block reads:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=4306
DB_DATABASE=kuet_try
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate the application key
```bash
php artisan key:generate
```

### 6. Create the database
In phpMyAdmin (or the MySQL client), create a database named `kuet_try` with collation
`utf8mb4_unicode_ci`. From the command line:
```bash
mysql -h 127.0.0.1 -P 4306 -u root -e "CREATE DATABASE kuet_try CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 7. Run the migrations and seeders
```bash
php artisan migrate:fresh --seed
```
This creates all 24 tables (16 application tables plus Laravel's own) and fills them with realistic data: 1 administrator, 15 supporters,
9 campaigns with ~140 donation records, 8 projects, 8 events, 6 stories, 7 articles, 5 announcements,
8 team members, 16 gallery entries, 9 volunteer applications and 6 contact messages.

### 8. Create the storage symlink
```bash
php artisan storage:link
```
> On Windows this needs either Developer Mode enabled or an elevated terminal. If it fails, run the
> command prompt as Administrator, or create the junction manually:
> ```
> mklink /J "C:\path\to\project\public\storage" "C:\path\to\project\storage\app\public"
> ```

### 9. Build the frontend assets
```bash
npm install
npm run build
```
The compiled CSS/JS is written to `public/build`, so **Node is not required to run the site** once
built. Use `npm run dev` instead while editing the front end.

---

## 5. Running the site

### Option A — Laravel's development server (simplest)
```bash
php artisan serve
```
Then open **http://localhost:8000**.

### Option B — XAMPP / Apache
Copy the project to `C:\xampp\htdocs\kuet-try`, set in `.env`:
```
APP_URL=http://localhost/kuet-try/public
```
then run `php artisan config:clear` and open
**http://localhost/kuet-try/public**.

### Option C — Apache VirtualHost (cleanest URLs)
Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName kuet-try.test
    DocumentRoot "C:/xampp/htdocs/kuet-try/public"
    <Directory "C:/xampp/htdocs/kuet-try/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Add `127.0.0.1  kuet-try.test` to `C:\Windows\System32\drivers\etc\hosts`, set
`APP_URL=http://kuet-try.test`, restart Apache, and open **http://kuet-try.test**.

---

## 6. Login credentials

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@kuettry.org` | `Admin@12345` |
| Supporter (demo) | `supporter@kuettry.org` | `Password@123` |
| Other seeded supporters | `firstname.lastname@example.com` | `Password@123` |

The administrator is created by `database/seeders/AdminSeeder.php`. **There is no admin registration
page anywhere in the application** — the seeder is the only way an account with `role = 'admin'`
comes into existence, and the seeder demotes any other admin it finds so exactly one always exists.

> Change the administrator password after first login: **Admin → Settings → Change password**.

---

## 7. Important URLs

| Area | URL |
|---|---|
| Homepage | `/` |
| About / Team | `/about`, `/team` |
| Campaigns | `/campaigns`, `/campaigns/{slug}` |
| Projects | `/projects`, `/projects/{slug}` |
| Events | `/events`, `/events/{slug}` |
| Impact stories | `/stories`, `/stories/{slug}` |
| News | `/news`, `/news/{slug}` |
| Gallery | `/gallery` |
| Get involved | `/get-involved` |
| Contact | `/contact` |
| Search | `/search?q=…` |
| Register / Login | `/register`, `/login` |
| Supporter dashboard | `/dashboard` |
| Record a donation | `/donate` |
| My donations | `/dashboard/donations` |
| Donation receipt | `/dashboard/donations/{id}/receipt` |
| Volunteer | `/volunteer` |
| Profile | `/profile` |
| **Admin dashboard** | `/admin` |
| Admin — campaigns | `/admin/campaigns` |
| Admin — donations | `/admin/donations` |
| Admin — reports | `/admin/donations/reports` |
| Admin — users | `/admin/users` |
| Admin — settings | `/admin/settings` |
| Weather endpoint | `/api/weather` |
| Sitemap | `/sitemap.xml` |

---

## 8. Database

**Name:** `kuet_try`  **Host:** `127.0.0.1`  **Port:** `4306`  **User:** `root`  **Password:** *(empty)*

### Tables

| Table | Purpose |
|---|---|
| `users` | Supporters and the single administrator (`role` enum) |
| `campaigns` | Fundraising appeals, soft deletes |
| `campaign_updates` | Field updates posted against a campaign |
| `donations` | Donation records with verification status |
| `projects` | Delivered and ongoing work, soft deletes |
| `events` | Orientations, camps, fundraisers, soft deletes |
| `volunteers` | Volunteer applications (one per user) |
| `success_stories` | Beneficiary stories, soft deletes |
| `posts` | News / blog articles, soft deletes |
| `announcements` | Homepage notices with priority and expiry |
| `gallery_items` | Gallery photographs |
| `team_members` | Public team page |
| `contact_messages` | Contact form submissions |
| `organization_settings` | Key/value site identity settings |
| `activity_logs` | Audit trail of administrative actions |
| `notifications` | Laravel database notifications |
| `sessions`, `cache`, `jobs`, … | Framework tables |

### Key relationships
```
User      hasMany   Donation          Donation  belongsTo User, Campaign, reviewer(User)
User      hasOne    Volunteer         Campaign  hasMany   Donation, CampaignUpdate, SuccessStory
User      hasMany   Post, ActivityLog SuccessStory belongsTo Campaign
```

Foreign keys use `nullOnDelete()` for donations (so financial history survives account deletion) and
`cascadeOnDelete()` for campaign updates. Indexes cover every column used in filters and sorts.

---

## 9. API integrations

Both integrations are **free, keyless and open**, and both fail gracefully.

### Open-Meteo (weather)
- Server-side only, via Laravel's HTTP client (`App\Services\WeatherService`).
- Responses are **cached for 30 minutes**, so page views never hammer the provider.
- 6-second timeout with one retry; failures are logged and return `null`.
- The browser never calls Open-Meteo — the widget's refresh button calls our own rate-limited
  `/api/weather` endpoint, which returns a small transformed payload.
- If the provider is unreachable the page shows *"Weather data temporarily unavailable"* — never a
  cURL error.

### OpenStreetMap + Leaflet (maps)
- Tiles from OpenStreetMap, rendered with Leaflet (loaded as a lazy chunk, only on pages with a map).
- Attribution is always displayed, as the ODbL requires.
- **Coordinates are stored in the database**, so no geocoding request is made on page load. This
  respects the Nominatim usage policy: no autocomplete, no bulk lookups, no per-request geocoding.
  A Nominatim client is configured in `config/apis.php` but disabled by default.
- If tiles or the library fail to load, the map area degrades to an address card.

Both providers are configurable from `.env` (`WEATHER_API_URL`, `MAP_TILE_URL`, `MAP_PROVIDER`, …)
so they can be swapped without touching application code.

---

## 10. Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            18 admin controllers
│   │   ├── Auth/             5 authentication controllers
│   │   └── …                 17 public + supporter controllers
│   ├── Middleware/           EnsureUserIsAdmin, EnsureAccountIsActive, RememberVisitedCampaign
│   └── Requests/             25 form request classes
├── Models/                   15 Eloquent models (+ HasSlug / HasCoverImage concerns)
├── Notifications/            DonationReviewed, VolunteerReviewed, AnnouncementPublished
├── Policies/                 ContentPolicy, DonationPolicy, UserPolicy, VolunteerPolicy
├── Services/                 SiteSettings, DonationService, StatisticsService,
│                             WeatherService, ImageUploadService, ActivityLogger
└── Support/helpers.php       settings(), money(), compact_number(), active_class()

resources/
├── css/app.css               Design tokens + component layer (Tailwind 4)
├── js/
│   ├── app.js                Entry point
│   └── modules/              17 focused modules — no logic in Blade
└── views/
    ├── layouts/              app, admin, dashboard, auth
    ├── components/           ui.*, form.*, layout.*, admin.*, cards, pagination
    ├── pages/ campaigns/ projects/ events/ stories/ news/ gallery/
    ├── auth/ dashboard/ admin/ errors/
```

### Laravel features demonstrated
Eloquent ORM with relationships, scopes, accessors, casts and soft deletes · migrations with foreign
keys and indexes · seeders and factories · form requests · policies and gates · custom middleware ·
route model binding · named routes and route groups · resource controllers · Blade layouts,
components, slots and stacks · session flash messages · cookies · database transactions · database
notifications · queues-ready jobs table · HTTP client · cache · rate limiting · custom exception
rendering · pagination · validation with custom messages · service container bindings and view
composers · Vite asset compilation.

---

## 11. Security

| Control | Where |
|---|---|
| CSRF protection | `@csrf` on every state-changing form; verified by test |
| XSS protection | `{{ }}` escaping everywhere; flash messages written with `textContent` |
| SQL injection | Eloquent bindings only; filter values validated against allow-lists |
| Password hashing | bcrypt via the `hashed` cast — never stored in plain text |
| Mass assignment | `role`, `is_active`, `status`, `raised_amount`, `counted_in_campaign` are all non-fillable |
| Admin protection | `EnsureUserIsAdmin` middleware on the whole `/admin` group, enforced server-side |
| Authorization | Four policies; `$this->authorize()` and form request `authorize()` |
| Donation integrity | Only `DonationService` changes status or campaign totals, inside DB transactions with row locks |
| Login throttling | 5 failed attempts per email+IP, plus a 20/min per-IP flood guard |
| Rate limiting | Contact form (5/hour), donations (15/hour), weather endpoint (12/min) |
| File uploads | MIME type + extension + size + dimensions validated; stored under a random 40-char name |
| Session security | Regenerated on login, invalidated on logout, HttpOnly/SameSite cookies |
| Single admin | `UserPolicy` refuses to deactivate, demote or delete the administrator |

A disguised PHP file uploaded as `invoice.jpg` is rejected — the rules check the *detected* MIME
type, never the client-supplied filename.

---

## 12. Testing

```bash
php artisan test
```

The suite runs against a **separate MySQL database** (`kuet_try_test`) because the reporting
aggregates use MySQL-specific SQL. Create it once:

```bash
mysql -h 127.0.0.1 -P 4306 -u root -e "CREATE DATABASE kuet_try_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### What is covered

| Test file | Coverage |
|---|---|
| `SmokeTest` | Renders **every** public, supporter and admin page against seeded data |
| `Auth/AuthenticationTest` | Login, logout, wrong password, deactivated accounts, throttling, session |
| `Auth/RegistrationTest` | Registration, hashing, **privilege-escalation guards**, validation |
| `AdminAuthorizationTest` | Guests and users blocked from all 17 admin areas; single-admin protection |
| `DonationTest` | Recording, validation, approval, rejection, double-count guard, totals, privacy, receipts |
| `CampaignManagementTest` | Full CRUD, soft delete/restore, slugs, filters, upload validation |
| `ContentManagementTest` | CRUD for projects, events, posts, stories, announcements, gallery, team |
| `VolunteerTest` | Applications, one-per-user, editing rules, admin review, notifications |
| `ContactAndApiTest` | Contact form, honeypot, message privacy, weather transform/cache/failure |
| `ProfileTest` | Profile updates, self-promotion guard, avatar, password change, account closure |
| `SecurityTest` | CSRF, XSS escaping, SQL injection attempts, `.env` exposure, session handling |

Run a single file with `php artisan test --filter=DonationTest`.

---

## 13. Artisan command reference

```bash
composer install                 # PHP dependencies
php artisan key:generate         # Application encryption key
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Drop, rebuild and seed everything
php artisan db:seed              # Seed only
php artisan storage:link         # public/storage → storage/app/public
npm install                      # Frontend dependencies
npm run build                    # Compile CSS/JS for production
npm run dev                      # Vite dev server with hot reload
php artisan serve                # http://localhost:8000
php artisan test                 # Run the test suite
php artisan route:list           # Inspect all 131 routes
php artisan config:clear         # Clear cached configuration
php artisan optimize:clear       # Clear config, route, view and event caches
```

---

## 14. Assumptions and design decisions

1. **Laravel 12, not 13.** Laravel 13 requires PHP 8.3+; this environment runs PHP 8.2.12. The
   project uses Laravel 12 with its current conventions rather than producing code that cannot run.
2. **No payment gateway.** None was specified and pretending to process card payments in a
   university project would be dishonest. The verified donation-record model is documented above and
   is what a small organisation actually does.
3. **Photographs are optional.** Rather than shipping stock images of vulnerable people, records
   without an uploaded image render a designed placeholder whose gradient is derived from a hash of
   the title. Uploading a real image through the admin panel replaces it everywhere.
4. **Currency is Bangladeshi Taka**, formatted as `Tk 1,250` via the `money()` helper and
   configurable in `config/site.php`.
5. **Content is fictional but realistic** — written to match how a Khulna-based student humanitarian
   organisation would actually report its work.
6. **Email uses the `log` driver.** Password-reset links are written to
   `storage/logs/laravel.log` rather than sent, which is the sensible local default.
7. **Email verification is not enforced.** The column and timestamp exist and are cleared when a user
   changes their email, but access is not gated on it since mail is not deliverable locally.
8. **`serve` is disabled on the private `local` disk** so Laravel does not register an unused
   signed-upload `PUT /storage/{path}` route that would also shadow the public disk's URL prefix.

---

## 15. Possible improvements

- A real payment gateway (SSLCommerz or bKash Checkout) behind the existing donation flow.
- Recurring/monthly donations with scheduled reminders.
- PDF receipts generated server-side rather than via the browser's print dialog.
- CSV/Excel export of donation reports.
- Two-factor authentication for the administrator.
- Bengali translation using Laravel's localisation files.
- Full-text search (MySQL `FULLTEXT` or Meilisearch) instead of `LIKE`.
- Queued email notifications once a real mail driver is configured.

---

## Licence

Built as a university project for **KUET TRY**. The Laravel framework is MIT licensed.
Weather data by [Open-Meteo](https://open-meteo.com/). Map data © OpenStreetMap contributors.
