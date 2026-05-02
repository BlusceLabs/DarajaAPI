# Changelog

All notable changes to this project are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

## [1.2.0] — 2024-12-10

### Added
- **Real-time inline validation** on payment form — phone and amount fields show green/red feedback as you type, before submission
- **Rate limiting** in `stk_push.php` — max 5 requests per IP per minute; returns `429 Too Many Requests` with a `Retry-After` header
- **CSV export** (`export.php`) — downloads all transactions as a UTF-8 CSV (Excel-compatible with BOM)
- **Auto-refresh** on `admin.php` — 30-second countdown with a live indicator; toggleable on/off
- **Search** on `admin.php` — filter by phone number or receipt in real time
- **Pending** filter tab on `admin.php`
- **Success rate** and **pending count** sub-labels on stat cards
- **Custom 404 page** (`404.php`) with links back to the payment page and admin

### Improved
- `stk_push.php` — account reference is now sanitised (alphanumeric + hyphens only)
- `admin.php` — search and filter work together; no-results message shown when nothing matches
- `config.php` / `config.example.php` — aligned to TinyPesa setup only; removed leftover Daraja raw-API fields

---

## [1.1.0] — 2024-12-10

### Added
- `admin.php` — transaction log viewer with summary stats and status filtering
- Quick-select amount chips on the payment page (KES 50, 100, 500, 1,000, 2,500, 5,000)
- Optional **Account Reference** field on the payment form
- Receipt number, amount, and timestamp shown on payment confirmation
- `CHANGELOG.md` — this file
- `CONTRIBUTING.md` — contribution guide
- `config.example.php` — safe credential template for contributors
- `favicon.svg` — browser tab icon

### Fixed
- `stk_push.php` — was reading from `$_GET` instead of the POST JSON body sent by the frontend
- `config.php` — removed incorrect `header()` call that broke files requiring this config
- `stk_push.php` — now properly imports `config.php` for credentials
- Phone number validation now rejects non-Safaricom numbers before calling the API

### Improved
- `callback.php` — correctly parses the full `stkCallback` envelope; stores structured JSON lines
- `check_status.php` — proper `ResultCode === 0` check; searches newest entries first
- `.gitignore` — excludes `config.php`, `mpesa_log.json`, OS/editor files, and Replit internals
- Payment UI — loading spinner, status messages, and animated progress bar

---

## [1.0.0] — 2024-12-10

### Added
- Initial release: STK Push via TinyPesa API
- `stk_push.php` — backend payment trigger
- `callback.php` — M-Pesa result receiver
- `check_status.php` — payment status polling
- `index.php` — payment UI
- `config.php` — credential configuration
- MIT License
- `README.md`
