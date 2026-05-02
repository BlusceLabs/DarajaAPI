# Changelog

All notable changes to this project are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

## [1.4.0] — 2026-05-02

### Added
- **Column sorting** on `admin.php` — click any column header (#, Date/Time, Phone, Amount, Receipt, Status) to sort ascending or descending; active sort column shows a ↑/↓ arrow; works in combination with all filters and pagination
- **Copy-to-clipboard** receipt button on the payment success screen — a small inline button next to the M-Pesa receipt number; shows a "Copied!" confirmation for 1.5 seconds
- **Pay Again button** on the payment success screen — resets the form, clears all field state, and removes the success message so another payment can be made without a page reload

### Improved
- `admin.php` — `data-sort*` attributes added to each table row for fast client-side sorting without re-parsing JSON
- `index.php` — success state is now a complete, self-contained flow; no reload needed between payments

---

## [1.3.0] — 2026-05-02

### Added
- **7-day revenue chart** on `admin.php` — pure SVG bar chart computed in PHP; shows confirmed payment counts per day for the last 7 days; no JS charting library required
- **Transaction detail drawer** on `admin.php` — click any table row to slide in a detail panel showing phone, amount, receipt, date, description, reference, result code, merchant request ID, and checkout request ID; closes with ✕, overlay click, or Escape key
- **Pagination** on `admin.php` — 20 rows per page with smart page-number rendering (ellipsis for long ranges); works in combination with status filter, search, and date range; scrolls to top on page change
- **Export CSV with date range** — `export.php` now accepts `?from=YYYY-MM-DD&to=YYYY-MM-DD` query params; filename reflects the range; Export CSV button on `admin.php` dynamically updates its href with the active date filter
- **Result Code** column added to the CSV export

---

## [1.2.0] — 2026-05-02

### Added
- **`.htaccess`** — blocks direct browser access to `config.php` and `mpesa_log.json`; disables directory listings; adds security headers; 404 routed to `404.php`
- **`webhook_test.php`** — developer simulator for sending test M-Pesa callbacks with preset scenarios (success, cancelled, insufficient funds, wrong PIN); shows live request/response log
- **`health.php`** — system status page checking PHP version, cURL/JSON extensions, config, API key, log writability, temp dir, and HTTPS
- **Date range filter** on `admin.php` — filter transactions by from/to date using native date pickers
- **Health** and **Simulator** quick-links added to `admin.php` header

---

## [1.1.0] — 2026-05-02

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

## [1.0.1] — 2026-05-02

### Added
- `admin.php` — transaction log viewer with summary stats and status filtering
- Quick-select amount chips on the payment page (KES 50, 100, 500, 1,000, 2,500, 5,000)
- Optional **Account Reference** field on the payment form
- Receipt number, amount, and timestamp shown on payment confirmation
- Dark mode — 🌙/☀️ toggle on every page; persists via `localStorage`; respects `prefers-color-scheme` on first visit
- `CHANGELOG.md`, `CONTRIBUTING.md`, `config.example.php`, `favicon.svg`

### Fixed
- `stk_push.php` — was reading from `$_GET` instead of the POST JSON body sent by the frontend
- `config.php` — removed incorrect `header()` call that broke files requiring this config
- Phone number validation now rejects non-Safaricom numbers before calling the API

### Improved
- `callback.php` — correctly parses the full `stkCallback` envelope; stores structured JSON lines
- `check_status.php` — proper `ResultCode === 0` check; searches newest entries first
- `.gitignore` — excludes `config.php`, `mpesa_log.json`, OS/editor files, and Replit internals
- Payment UI — loading spinner, status messages, and animated progress bar

---

## [1.0.0] — 2026-05-02

### Added
- Initial release: STK Push via TinyPesa API
- `stk_push.php` — backend payment trigger
- `callback.php` — M-Pesa result receiver
- `check_status.php` — payment status polling
- `index.php` — payment UI
- `config.php` — credential configuration
- MIT License
- `README.md`
