# GSP Simple ERP

A simple monolithic ERP built on **Laravel 13** backed by **PostgreSQL 17** — inventory,
purchasing, sales and an audited stock ledger. Server-rendered Blade + Tailwind CDN, so
there is **no npm/Vite build step** required.

## ⚠️ Read this first: PHP version

This app requires **PHP >= 8.3** (Laravel 13). Your default `php` on PATH is **8.2.28**,
which will fail with Composer's platform check. Use the PHP 8.5 binary already installed:

```bash
cd PROJECTS/AI/LARAVEL
/opt/homebrew/bin/php artisan serve     # http://127.0.0.1:8000
```

Alternatively make 8.5 the default once:
`brew unlink php@8.2 && brew link --overwrite --force php`

## Database

Configured for PostgreSQL on this machine (credentials live in `.env`, which is git-ignored):

| Setting | Value |
|---|---|
| Host / Port | `127.0.0.1` / `5430` |
| Database | `gsp_erp` |
| User | `panduwiguna` |

`.env.example` still points at SQLite on purpose so credentials are never committed.

## Setup

```bash
/opt/homebrew/bin/php composer install
cp .env.example .env          # then set DB_CONNECTION=pgsql and the DB_* values
/opt/homebrew/bin/php artisan key:generate
/opt/homebrew/bin/php artisan migrate:fresh --seed
/opt/homebrew/bin/php artisan serve    # http://127.0.0.1:8000
```

## Users

Seeders create **12 records for every model** (users, suppliers, customers, products,
purchase orders, sales orders — plus ~30 order lines and 50+ stock movements).

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | System Administrator |
| `rina`, `budi`, `siti`, `agus`, `dewi`, `eko`, `fitri`, `gunawan`, `hesti`, `irfan`, `joko` | `password` | Staff |

Sign in with **either the username or the e-mail address** (e.g. `admin` or `admin@gsp.co.id`).

## Modules

| Area | What it does |
|---|---|
| Dashboard | Stock value at cost, low-stock alerts, open orders, recent activity |
| Products | Catalogue with SKU, cost/sale price, reorder level; soft-delete archive |
| Stock Movements | Append-only ledger: every change records before/after balance |
| Sales Orders | Multi-line order → *ship* decrements stock |
| Purchase Orders | Multi-line order → *receive* increments stock |
| Customers / Suppliers | Contact master data |

## Business rules

- Stock is **never** edited silently. All changes flow through `Product::adjustStock()`,
  which writes a `stock_movements` row with `stock_before` / `stock_after`, reference and user.
- Shipping a sales order is blocked when any line would go negative.
- Editing a product does not expose the stock field; use adjustments, purchases or sales.
- Purchase/sales totals are derived from their line subtotals, never typed in.
- Order documents are immutable once received or shipped (`isReceivable()` / `isShippable()`).

## Layout

```
app/Models            Product, Supplier, Customer, PurchaseOrder(+Item),
                      SalesOrder(+Item), StockMovement
app/Http/Controllers  Resource controllers + AuthController, DashboardController
database/migrations   2026_11_09_00000*_*.php  (4 new files)
resources/views       layouts/app, dashboard, products, sales, purchases,
                      customers, suppliers, movements, auth/login
routes/web.php        guest: login | auth: everything else
```

## Notes & limitations

- **Auth** is a single hand-rolled login with no roles/permissions; any signed-in user can do anything.
- **No payments, invoicing, tax or deliveries** — this is deliberately a simple ERP core.
- Order lines cannot be edited after creation; delete and recreate the order instead.
- Tailwind is loaded from a CDN, so styling needs internet access on first paint.
- Currency is displayed as unlabelled thousands separators (Indonesian formatting).
- Test with `/opt/homebrew/bin/php artisan tinker` or the seeded demo data before entering real records.
