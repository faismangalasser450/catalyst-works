# Catalyst Works

A simpler replacement for the Catalyst ERP: Sales (customers, enquiries, quotations, orders, invoices, payments) and Service (complaints, technician view, AMC contracts, PPM visits), with a KPI dashboard and letterhead-style quotation / tax-invoice PDFs.

**Status:** working first version with sample data only. Not yet connected to the old ERP database.

## Stack
Laravel 13 · PHP 8.3 · Livewire 4 · Tailwind CSS 4 · DomPDF · SQLite for development (MySQL for production).

## Run locally
```sh
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```
Open http://127.0.0.1:8000 and log in with one of the sample users (password: `password`):

| Role | Email |
|---|---|
| Admin | sanjeev@catalystgroup.ae |
| Sales | abin@catalystgroup.ae |
| Service coordinator | priya@catalystgroup.ae |
| Accounts | fathima@catalystgroup.ae |
| Technician (phone view) | aqil@catalystgroup.ae |

Change these in **Settings → Users** before any real use.

## Design rules used throughout
- Every menu item opens a **list**, never a blank form. `+ New` is one button away.
- Registering a complaint is **7 fields**; visits, materials and closing are separate one-click actions.
- Technician and status change **inline in the row** and save instantly.
- Company details, terms, bank details and VAT come from **Settings** and are printed automatically.
- Each document has a **next step** button: Enquiry → Quote → Approve → Order → Invoice → Payment.
- Technicians get a phone-sized **My jobs** page: Call, Map, Visited / Working / Close.

## Tests
```sh
php artisan test
```

## Going live (later)
1. Point `.env` at MySQL and run `php artisan migrate`.
2. Write a one-off import from the old `Catalyst_erp` tables (customers, quotations, AMC agreements, complaints) into these tables.
3. Replace `public/brand/header.jpg` / `footer.jpg` if the letterhead changes.

## Hosting the test site

The repo ships a `Dockerfile` (Apache + PHP 8.3, Postgres and MySQL drivers) that runs
`php artisan migrate --force --seed` on start; the seeder is a no-op once data exists.
The test site runs on Render (free web service, Docker, region Singapore) with a Supabase
Postgres database. Environment variables it needs:

```
APP_ENV=production  APP_DEBUG=false  APP_KEY=<php artisan key:generate --show>  APP_URL=https://<host>
DB_CONNECTION=pgsql  DB_URL=postgresql://...  DB_SSLMODE=require
SESSION_DRIVER=database  CACHE_STORE=database  QUEUE_CONNECTION=sync  MAIL_MAILER=log  LOG_CHANNEL=stderr
```

Free-tier note: the service sleeps after 15 minutes without traffic; the first request afterwards takes 30–60 s.
