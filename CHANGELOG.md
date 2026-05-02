# Changelog

All notable changes to this project are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

## [1.7.0] — 2026-05-02

### Added
- **CinetPay provider** (`providers/cinetpay.php`) — hosted checkout covering 15+ Francophone African countries (Côte d'Ivoire, Senegal, Cameroon, Mali, Burkina Faso, Togo, Guinea, DRC, Republic of Congo, Madagascar, Comoros, CAR, Chad, Gabon, Equatorial Guinea); currencies XOF/XAF/CDF/GNF/KMF/MGA; callback with transaction verification in `callback_cinetpay.php`
- **Paymob provider** (`providers/paymob.php`) — 3-step hosted checkout (auth → order → payment key → iframe) covering Egypt, Morocco, Pakistan, UAE, Saudi Arabia, Oman, Kuwait; HMAC-SHA512 webhook verification in `callback_paymob.php`
- **Ecocash provider** (`providers/ecocash.php`) — STK-style merchant payment push for Zimbabwe (USD/ZWL), Eswatini (SZL), Lesotho (LSL); callback in `callback_ecocash.php`
- **Orange Money provider** (`providers/orangemoney.php`) — OAuth2 hosted checkout covering 13+ countries (Côte d'Ivoire, Senegal, Mali, Burkina Faso, Guinea, Guinea-Bissau, Cameroon, Madagascar, Sierra Leone, Liberia, Morocco, Tunisia, Jordan); callback in `callback_orangemoney.php`
- **Cellulant Tingg provider** (`providers/cellulant.php`) — OAuth2 hosted checkout covering 18+ African countries (KE, UG, TZ, RW, NG, GH, ZM, ZW, MW, MZ, ET, CI, CM, SN, ZA, CD, MG, BW, AO); mobile money + cards + bank transfer in one checkout; callback in `callback_cellulant.php`
- SVG logos for all 5 new providers in `logos/`
- `index.php` `$pCfg` entries for all 5 new providers with correct currencies, chips, and brand gradients
- `health.php` configuration checks for all 5 new providers
- `admin.php` PHP and JS provider name/logo maps updated to include all 14 providers
- `config.example.php` updated with all 5 new provider sections including full documentation of required/optional constants
- README.md provider table, quick-config block, and setup guide sections updated for all 5 new providers

### Coverage after this release (14 providers)
- **East Africa**: TinyPesa, Daraja, PesaPal, Flutterwave, Paystack, MTN MoMo, Airtel Money, Cellulant Tingg
- **West Africa**: Flutterwave, Paystack, MTN MoMo, Airtel Money, CinetPay, Orange Money, Cellulant Tingg
- **Central Africa**: CinetPay, MTN MoMo, Airtel Money, Cellulant Tingg
- **North Africa**: Paymob (Egypt, Morocco), Orange Money (Morocco, Tunisia), Flutterwave
- **Southern Africa**: Ozow (ZA), DPO Pay (20+ countries), Ecocash (ZW/SZ/LS), Airtel Money, Cellulant Tingg

## [1.6.0] — 2026-05-02

### Added
- **Paystack provider** (`providers/paystack.php`) — hosted checkout; supports Nigeria, Ghana, Kenya, South Africa, Egypt + more; HMAC-SHA512 webhook verification in `callback_paystack.php`
- **MTN MoMo provider** (`providers/mtnmomo.php`) — STK-style Collection API; OAuth token via Basic auth; UUID reference ID; supports Ghana, Uganda, Côte d'Ivoire, Cameroon, Zambia, Rwanda + more; callback in `callback_mtnmomo.php`
- **Airtel Money provider** (`providers/airtelmoney.php`) — STK-style Merchant Payments API; OAuth2 client credentials; supports Kenya, Uganda, Tanzania, Rwanda, Zambia, DRC + more; callback in `callback_airtelmoney.php`
- **DPO Pay provider** (`providers/dpopay.php`) — XML-based v6 API; createToken then redirect; verifyToken on callback; 20+ African countries; callback/verify in `callback_dpopay.php`
- **Ozow provider** (`providers/ozow.php`) — South Africa instant EFT; SHA512 hash signing; `callback_ozow.php` verifies notification hash before logging
- **Five dedicated callback endpoints**: `callback_paystack.php`, `callback_mtnmomo.php`, `callback_airtelmoney.php`, `callback_dpopay.php`, `callback_ozow.php`
- **Provider logos** in `logos/` for all 9 providers: `tinypesa.png`, `daraja.svg`, `pesapal.png`, `flutterwave_32.png`, `paystack.png`, `mtnmomo.svg`, `airtelmoney.png`, `dpopay.png`, `ozow.png`
- Admin panel Provider badge now shows official logos for all 9 providers (table + detail drawer)
- `health.php` credential checks for all 5 new providers with environment/currency/country details
- `config.example.php` — 5 new provider sections with all required constants, supported countries, and currency codes

### Improved
- `stk_push.php` — provider loaded before phone validation; strict Kenyan format required only for STK providers; redirect providers accept international phone numbers
- README — providers table updated with country coverage; `config.php` code block lists all 9 providers; Provider Configuration section has step-by-step guides for Paystack, MTN MoMo, Airtel Money, DPO Pay, and Ozow; Security Notes updated with Paystack/Ozow/MTN signing notes

## [1.5.0] — 2026-05-02

### Added
- **Multi-provider payment support** — a single `PAYMENT_PROVIDER` constant in `config.php` switches the entire integration
- **Provider abstraction layer** — `providers/` directory with a shared interface contract (`base.php`) and four isolated provider files; adding a future provider never touches core routing code
- **TinyPesa provider** (`providers/tinypesa.php`) — refactored from `stk_push.php` into the provider contract; behaviour unchanged
- **Safaricom Daraja provider** (`providers/daraja.php`) — direct Daraja STK Push; fetches OAuth token using consumer key + secret; supports `sandbox` and `production` via `DARAJA_ENV`
- **PesaPal V3 provider** (`providers/pesapal.php`) — hosted checkout; registers IPN, submits order, returns `redirect_url`; `callback_pesapal.php` queries `GetTransactionStatus` and logs the result
- **Flutterwave provider** (`providers/flutterwave.php`) — hosted checkout; posts to `/v3/payments`, returns `redirect_url`; `callback_flutterwave.php` verifies `verif-hash` signature and logs result
- **Redirect flow in frontend** — `index.php` detects `flow: 'redirect'` in the API response and navigates to `redirect_url` with a brief message instead of starting the STK polling loop
- **`callback_pesapal.php`** — dedicated IPN endpoint for PesaPal
- **`callback_flutterwave.php`** — dedicated webhook endpoint for Flutterwave with signature verification
- **`provider` field in log entries** — every transaction carries `"provider": "tinypesa"|"daraja"|"pesapal"|"flutterwave"`
- **Provider column in admin panel** — blue Provider badge in the transaction table, sortable, searchable, shown in the detail drawer
- **Provider column in CSV export** — `export.php` includes a Provider column
- **Provider-aware health check** — `health.php` shows active provider name, flow type, and checks required constants per provider

### Improved
- `stk_push.php` is now a thin router — validates input, rate-limits, loads active provider, calls `provider_initiate()`
- `callback.php` loads active provider and calls `provider_parse_callback()` — no provider logic inline
- `config.example.php` — four commented provider sections each with all required constants

---

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
