# African Payments — PHP Multi-Provider Integration

## Project Overview
Open-source PHP integration for pan-African mobile money and payment gateway providers.
Plain PHP + vanilla JS, no frameworks. Originally an M-Pesa STK Push integration,
now expanded to **18 providers across 50+ African countries**.

GitHub: https://github.com/BlusceLabs/DarajaAPI

## Architecture

### Server
- PHP built-in dev server on port **5000** (workflow: `PHP Server`)
- No framework — plain PHP + vanilla JS

### File Structure
```
index.php            Payment UI — provider-driven config, chips, inline validation, STK polling
stk_push.php         POST endpoint — validates, rate-limits, dispatches to active provider
callback.php         Generic M-Pesa callback fallback
callback_*.php       Per-provider webhook receivers (16 files, one per provider)
check_status.php     GET polling endpoint — confirms STK payments from log
admin.php            Transaction log — stats, filter, search, date range, CSV export
export.php           Streams mpesa_log.json as downloadable CSV
health.php           System status — PHP, cURL, config, permissions, provider checks
webhook_test.php     Dev tool — simulate callbacks with preset scenarios
404.php              Custom 404 error page
.htaccess            Apache: blocks config/log access, security headers, 404 routing
config.php           Credentials (gitignored — never commit)
config.example.php   Safe template — copy to config.php and fill in credentials
providers/           18 PHP provider files (one per payment network)
logos/               SVG/PNG brand logos for all 18 providers
favicon.svg          SVG browser tab icon
mpesa_log.json       NDJSON log — one JSON line per callback (gitignored)
README.md            Full setup and API documentation
CHANGELOG.md         Version history (v1.0 → v1.9)
```

### Provider Registry (18 providers)

| Key | Provider | Type | Coverage |
|---|---|---|---|
| `tinypesa` | TinyPesa | STK | Kenya (M-Pesa) |
| `daraja` | Safaricom Daraja | STK | Kenya (M-Pesa direct) |
| `pesapal` | PesaPal | Redirect | KE/UG/TZ/RW/ZM/ZW/ET/GH |
| `flutterwave` | Flutterwave | Redirect | Pan-African 30+ |
| `paystack` | Paystack | Redirect | NG/GH/ZA/KE/EG |
| `mtnmomo` | MTN Mobile Money | STK | 21 African countries |
| `airtelmoney` | Airtel Money | STK | KE/UG/TZ/RW/NG/ZM/MG/MW |
| `dpopay` | DPO Pay | Redirect | 20+ African countries |
| `ozow` | Ozow | Redirect | South Africa (EFT) |
| `cinetpay` | CinetPay | Redirect | 15 Francophone countries |
| `paymob` | Paymob | Redirect | EG/MA/PK/AE/SA/OM/KW |
| `ecocash` | Ecocash | STK | ZW/SZ/LS |
| `orangemoney` | Orange Money | Redirect | 13+ countries |
| `evcplus` | EVC Plus | STK | Somalia (Hormuud + Telesom Zaad) |
| `wave` | Wave | Redirect | SN/CI/ML/BF/CM/UG/ZM |
| `telebirr` | Telebirr | Redirect | Ethiopia (40M+ users) |
| `moovafrica` | Moov Africa/Flooz | STK | TG/BJ/NE/BF/CI/TD/GA/CD/MG |
| `cellulant` | Cellulant Tingg | Redirect | 18+ countries |

### Provider Contract
Each `providers/{name}.php` exports 4 functions:
- `provider_flow()` → `'stk'` or `'redirect'`
- `provider_initiate($amount, $phone, $ref)` → `['success'=>bool, ...]`
- `provider_parse_callback($payload)` → normalised log entry
- `provider_check_status($ref)` → status lookup (not all providers support this)

### Data Flow
**STK (push) providers:**
1. User submits phone + amount → `stk_push.php` → provider STK API
2. Customer approves on phone → provider sends callback to `callback_{provider}.php`
3. Callback logs to `mpesa_log.json`
4. Frontend polls `check_status.php` every 5s (up to 60s) for confirmation

**Redirect (hosted checkout) providers:**
1. User submits amount → `stk_push.php` → provider returns `redirect_url`
2. Frontend redirects customer to provider checkout page
3. Customer pays → provider POSTs notification to `callback_{provider}.php`
4. Callback logs to `mpesa_log.json`

### Security Model
All callbacks use a defence-in-depth approach:
- Providers with HMAC/signature support (Paymob, Flutterwave, Paystack, Wave, Cellulant):
  fail-closed when secret is configured — missing or invalid signature → HTTP 401
- Providers with API re-verification (CinetPay, Orange Money):
  re-queries the provider API to confirm payment status before logging
- Providers without signing (Ecocash, Evcplus, Telebirr):
  documented with IP allowlisting recommendation; POST-only

### Payment Log Format (mpesa_log.json — NDJSON)
```json
{
  "provider": "tinypesa",
  "transaction_id": "QHX2Y3Z4AB",
  "reference": "Order-001",
  "amount": "500",
  "currency": "KES",
  "phone": "254712345678",
  "status": "success",
  "raw": { ... },
  "logged_at": "2026-05-02T10:30:45+00:00"
}
```

## Key Configuration
- Active provider: set `PAYMENT_PROVIDER` in `config.php` (defaults to `tinypesa`)
- Rate limit: 5 requests per IP per minute (file-based via `sys_get_temp_dir()`)
- Reference field max length: 20 characters
- STK polling: 5s interval × 12 attempts = 60s total; cancel button available
- Phone validation: strips prefix, requires `(7|1)\d{8}` pattern (Safaricom only)
- Amount range: KES 1 – 150,000

## User Preferences
- Open source — code must remain clean, documented, and framework-free
- Keep UI polished but lightweight (no external CSS/JS libraries)
- Every provider added should have: logo, $pCfg entry, health.php checks, config.example.php section, README docs, CHANGELOG entry
- Provider callbacks must be fail-closed when signing secrets are configured
