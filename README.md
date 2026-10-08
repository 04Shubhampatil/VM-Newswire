# VM Newswire

Lead-generation website for **VM Newswire**, a press release distribution service. Visitors compare packages, see the media each package reaches, download sample reports and send an enquiry. There is **no online checkout**: enquiries are saved to the database, the owner is notified by email, and the customer receives an acknowledgement.

The owner manages everything from `/admin`: packages, prices, media outlets (including bulk CSV import), sample report PDFs, enquiries, website content, FAQs and company settings.

## Requirements

| | Version |
|---|---|
| PHP | 8.3+ (developed on 8.4) with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `intl`, `zip`, `gd` |
| Laravel | 13 |
| Composer | 2.x |
| MySQL | 8.0+ (SQLite also works for local development and is used by the tests) |

No Node.js or npm is needed: the stylesheet, fonts and JavaScript ship pre-compiled.

## Stack

Laravel 13 (Blade, Eloquent, Form Requests, Mail, queues, Storage) · Tailwind CSS 4 (pre-compiled) · Alpine.js. Fonts (Inter, IBM Plex Mono) are self-hosted from `public/fonts`.

Front-end assets are plain files served by Laravel — no build step:

| File | Purpose |
|---|---|
| `public/css/app.css` | Compiled Tailwind stylesheet (source: `resources/css/app.css`, `resources/css/motion.css`) |
| `public/css/fonts.css` | `@font-face` rules for the self-hosted fonts |
| `public/js/app.js` | Compiled Alpine.js bundle (source: `resources/js/app.js`, `motion.js`, `analytics.js`) |
| `resources/views/partials/head-assets.blade.php` | The `<link>`/`<script>` tags every layout includes |

The `resources/css` and `resources/js` files are the readable sources. Edits to them do not apply by themselves: either edit the compiled files under `public/` directly (plain CSS/JS), or re-add the Tailwind/Vite toolchain that was retired to `storage/app/retired-frontend-toolchain/` and rebuild.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Database (MySQL)

Create a database and a dedicated user (run in the MySQL client as an administrator, choosing your own password):

```sql
CREATE DATABASE vm_newswire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vm_newswire'@'localhost' IDENTIFIED BY 'choose-a-strong-password';
GRANT ALL PRIVILEGES ON vm_newswire.* TO 'vm_newswire'@'localhost';
FLUSH PRIVILEGES;
```

Then set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`.

For a quick local start without MySQL, set `DB_CONNECTION=sqlite` and remove the other `DB_*` lines; Laravel uses `database/database.sqlite`.

### Migrate and seed

Set `ADMIN_EMAIL` (and optionally `ADMIN_PASSWORD`) in `.env`, then:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

The seeders create:

- **Site content** — company details, about text, footer text, general FAQs and *draft* privacy/terms pages (marked "legal review required").
- **Admin user** — from `ADMIN_EMAIL`. If `ADMIN_PASSWORD` is empty, a one-time password is printed and must be changed at first login. No password is hardcoded.
- **Demo catalogue** (not in production) — the six packages from the brief, the ten named outlets and 60 "Demo Outlet" rows. **Prices and package-to-outlet mappings are placeholders.** Replace them in `/admin` before launch. Set `SEED_DEMO_DATA=true` to seed them in production anyway.

Create or reset an admin at any time:

```bash
php artisan vmn:create-admin owner@example.com --name="Owner"
php artisan vmn:create-admin owner@example.com --generate
```

## Development

```bash
php artisan dev
```

`php artisan dev` runs the web server, the queue listener (needed for enquiry emails) and log tailing together. To run pieces separately:

```bash
php artisan serve
php artisan queue:listen --tries=1
```

Locally `MAIL_MAILER=log` writes every email to `storage/logs/laravel.log`.

## Testing

```bash
php artisan test
```

Tests use in-memory SQLite and cover public pages, the inactive-package rules, slug redirects, enquiry validation, the price snapshot, spam protection and rate limiting, email sending and failure logging, admin authentication, and package, media, CSV import, PDF upload, enquiry filtering, CSV export and content management.

Code style (Laravel preset, PSR-12 based):

```bash
vendor/bin/pint
```

## How enquiries work

1. The form posts to `POST /enquiries` (CSRF, honeypot field, per-IP rate limit, optional Cloudflare Turnstile).
2. `StoreEnquiryRequest` validates server-side. The package is required when the form is on a package page and optional on `/contact`.
3. `EnquiryService` stores the enquiry **with a snapshot of the package name and price** plus two `email_logs` rows (`pending`) in one transaction.
4. After the transaction commits, the `SendEnquiryEmails` job sends `AdminEnquiryMail` (to Admin → Settings → notification email, else `ADMIN_NOTIFICATION_EMAIL`, else the public email) and `CustomerAcknowledgementMail`. Each log becomes `sent` or `failed` with the error message.
5. A failed email never removes the enquiry. The admin sees the failure on the enquiry page and can **Retry**.

## Admin

| Area | What it does |
|---|---|
| Dashboard | Totals, new enquiries, active packages, outlets, recent enquiries, failed-email and missing-report alerts |
| Packages | Create, edit, activate or deactivate, reorder, archive (soft delete) and restore. Price, currency, features, Markdown content, SEO fields, "Most requested" (one package). Featured media (max 7) and network outlets. Sample report upload |
| Media Network | Add, edit, enable/disable, reorder and delete outlets. **Poster image** per outlet (JPG/PNG/WebP up to 5 MB, min 300×200; re-encoded server-side to WebP at 800px and stored under `storage/app/public/media-network/` with a generated name — the old file is deleted only after the replacement is stored). Instant preview before saving. Short description and description feed the outlet popup. "Show in hero network" picks the outlets in the home page distribution visual (first 7 by order; tablet shows 5, mobile 4). Outlets without a poster get a designed fallback. Outlets used by a package can't be deleted |
| Media import | CSV `name, website_url, logo_url, category, is_active`. Every row is validated, with results shown as imported / updated / skipped / failed plus a reason per row. Can update existing outlets and attach them to a package |
| Sample Reports | Upload, replace or remove PDFs. Extension, MIME type and `%PDF` signature are checked, filenames are random UUIDs, files live on a private disk and are streamed to visitors |
| Enquiries | Search (name/email/company), filter by package, status and date range, sort, change status, detail view with price snapshot and email delivery log, streamed CSV export (spreadsheet formula injection neutralised) |
| Website Content | About, footer, privacy and terms (Markdown with raw HTML stripped), FAQs |
| Settings | Company name, email, phone, WhatsApp, address, notification email, network size label ("200+"), social links |

## SEO

Every page has a unique title, meta description, canonical URL, Open Graph and Twitter tags (`public/images/og-default.png`). Package pages generate their title and description from the admin fields. JSON-LD: Organization + WebSite (home), Service (package), BreadcrumbList (inner pages), FAQPage (`/faq`). `/sitemap.xml` lists the static pages and active packages. `/robots.txt` blocks everything outside production.

## Analytics

Set `GA4_MEASUREMENT_ID` to enable Google Analytics 4. Events: `package_view`, `sample_report_click`, `enquiry_form_started`, `enquiry_submitted`, `cta_click`. Only labels and package names are sent, never personal data.

## Production deployment

1. `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://vmnewswire.com`, `SESSION_SECURE_COOKIE=true`.
2. Configure MySQL and a transactional email provider (`MAIL_*`, with a verified `MAIL_FROM_ADDRESS` domain).
3. Deploy, then:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan optimize
   ```

4. Run a queue worker under a process manager (Supervisor or systemd): `php artisan queue:work --tries=1`.
5. Serve over HTTPS (for example with Let's Encrypt). Security headers are added by `App\Http\Middleware\SecurityHeaders`.
6. Back up the database regularly: enquiries are business-critical.

### S3-compatible storage (optional)

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

Set `SAMPLE_REPORTS_DISK=s3` and the `AWS_*` values. Reports are streamed through the app, so the bucket can stay private.

### Cloudflare Turnstile (optional)

Set `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY`. The widget and server-side verification switch on automatically.

## Project layout

```
app/Http/Controllers/Public   Website pages, enquiry submission, reports, sitemap
app/Http/Controllers/Admin    Admin panel
app/Http/Requests             Form Requests (enquiry, package, media outlet)
app/Services                  SiteSettings (cached), EnquiryService, MediaCsvImporter
app/Jobs/SendEnquiryEmails    Sends and logs enquiry emails
app/Mail                      AdminEnquiryMail, CustomerAcknowledgementMail
resources/views/components    Blade components (layouts, header, footer, package card, comparison table, enquiry form, …)
routes/web.php, routes/admin.php
```

 php artisan serve
  $env:Path = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe;$env:Path"