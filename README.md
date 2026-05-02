# M-Pesa STK Push — PHP Integration

A clean, production-ready PHP integration for **Lipa Na M-Pesa Online** (STK Push) using the [TinyPesa](https://tinypesa.com) API. No complex OAuth flows — just an API key and you're ready to accept M-Pesa payments.

No frameworks. No build tools. Plain PHP + vanilla JS.

---

## Screenshots

<table>
  <tr>
    <td align="center"><strong>Payment Form</strong></td>
    <td align="center"><strong>Admin Panel</strong></td>
  </tr>
  <tr>
    <td><img src="screenshots/payment-form.jpg" alt="Payment form — phone input, amount chips, Pay Now button" width="420"/></td>
    <td><img src="screenshots/admin-panel.jpg" alt="Admin panel — stats, 7-day chart, transaction table" width="420"/></td>
  </tr>
  <tr>
    <td align="center"><strong>System Health Check</strong></td>
    <td align="center"><strong>Webhook Simulator</strong></td>
  </tr>
  <tr>
    <td><img src="screenshots/health-check.jpg" alt="Health check page — PHP version, extensions, config status" width="420"/></td>
    <td><img src="screenshots/webhook-simulator.jpg" alt="Webhook simulator — preset scenarios, live response log" width="420"/></td>
  </tr>
</table>

---

## Supported Payment Providers

Switch providers by changing one constant in `config.php` — no other code changes needed.

| Provider | Type | Flow | Required credentials |
|---|---|---|---|
| **TinyPesa** (default) | M-Pesa STK Push | Phone PIN prompt | `TINYPESA_API_KEY` |
| **Safaricom Daraja** | M-Pesa STK Push | Phone PIN prompt | `DARAJA_CONSUMER_KEY`, `DARAJA_CONSUMER_SECRET`, `DARAJA_SHORTCODE`, `DARAJA_PASSKEY`, `DARAJA_CALLBACK_URL` |
| **PesaPal V3** | Hosted checkout | Browser redirect | `PESAPAL_CONSUMER_KEY`, `PESAPAL_CONSUMER_SECRET`, `PESAPAL_IPN_URL`, `PESAPAL_CALLBACK_URL` |
| **Flutterwave** | Hosted checkout | Browser redirect | `FLW_SECRET_KEY`, `FLW_PUBLIC_KEY`, `FLW_REDIRECT_URL` |

**STK Push** providers send a PIN prompt directly to the customer's phone — no redirect needed.  
**Hosted checkout** providers redirect the customer to a payment page in their browser.

```php
// config.php — choose one
define('PAYMENT_PROVIDER', 'tinypesa');    // default
define('PAYMENT_PROVIDER', 'daraja');
define('PAYMENT_PROVIDER', 'pesapal');
define('PAYMENT_PROVIDER', 'flutterwave');
```

---

## Features

**Payment flow**
- STK Push (TinyPesa / Daraja) — sends a PIN prompt directly to the customer's phone
- Hosted checkout (PesaPal / Flutterwave) — redirects to provider's payment page
- Real-time inline phone and amount validation before submission
- Quick-amount chips (50, 100, 500, 1,000, 2,500, 5,000) on the payment form
- Optional account reference field (shown/hidden on demand)
- Payment status polling — auto-detects confirmation and shows a receipt (STK Push providers)
- Rate limiting — max 5 payment requests per IP per minute (file-based, no Redis required)

**Admin panel**
- Summary stats: total requests, confirmed, failed, total KES collected
- 7-day confirmed payments bar chart (pure SVG, computed in PHP — no JS charting library)
- Date range filter — filter the transaction table by from/to date
- Status tabs — All / Confirmed / Failed / Pending
- Full-text search by phone number, receipt, or provider name
- Provider badge column — shows which gateway processed each transaction, sortable
- Click any row to open a slide-out detail drawer with the full transaction record
- Pagination — 20 rows per page, works in combination with all filters
- CSV export — downloads all transactions with Provider column; respects the active date range filter

**Developer tools**
- `/health.php` — shows active provider name + flow type, checks all required credentials for that provider, PHP version, cURL, JSON, log writability, temp dir, and HTTPS
- `/webhook_test.php` — sends simulated M-Pesa callbacks to `callback.php` with four preset scenarios (success, user cancelled, insufficient funds, wrong PIN)

**Production hardening**
- `.htaccess` — blocks direct browser access to `config.php`, `mpesa_log.json`, and all `.json` files; disables directory listings; adds security headers; routes 404s to the custom error page
- Custom `404.php` error page
- `config.php` and `mpesa_log.json` are gitignored by default

**UI/UX**
- Dark mode — toggleable via 🌙/☀️ button on every page; persists across pages via `localStorage`; respects the OS `prefers-color-scheme` on first visit
- Fully responsive layout on all pages
- SVG favicon

---

## Requirements

- PHP 7.4+ with the `curl` and `json` extensions enabled
- An account with one of the [supported providers](#supported-payment-providers)
- A publicly accessible HTTPS URL for callbacks (use [ngrok](https://ngrok.com) for local development)

---

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/BlusceLabs/DarajaAPI.git
cd DarajaAPI
```

### 2. Configure your credentials

```bash
cp config.example.php config.php
```

Edit `config.php`:

```php
define('TINYPESA_API_KEY', 'your_tinypesa_api_key_here');
define('TINYPESA_URL',     'https://tinypesa.com/api/v1/express/initialize');
```

Get your API key from the [TinyPesa Dashboard](https://tinypesa.com/dashboard).

### 3. Set your callback URL

In your TinyPesa dashboard, set the callback URL to:

```
https://yourdomain.com/callback.php
```

> Safaricom requires a publicly accessible HTTPS URL. The callback cannot point to `localhost`.  
> For local development, expose your server with [ngrok](https://ngrok.com): `ngrok http 8000`

### 4. Run the server

```bash
php -S 0.0.0.0:8000
```

Open `http://localhost:8000` in your browser.

### 5. Verify your setup

Visit `/health.php` to check that all requirements are met before accepting payments.

---

## File Structure

```
├── index.php                  # Payment UI — phone, amount chips, reference, inline validation
├── stk_push.php               # POST endpoint — validates, rate-limits, delegates to active provider
├── callback.php               # Receives STK callbacks (TinyPesa / Daraja), delegates to provider
├── callback_pesapal.php       # PesaPal IPN endpoint — queries transaction status and logs result
├── callback_flutterwave.php   # Flutterwave webhook — verifies signature hash, logs result
├── check_status.php           # GET polling endpoint — confirms payment by phone number
├── admin.php                  # Admin panel — chart, stats, filter, search, drawer, pagination, CSV export
├── export.php                 # Streams mpesa_log.json as a downloadable CSV (supports date range)
├── health.php                 # System health — active provider, credentials, environment checks
├── webhook_test.php           # Dev tool — simulate M-Pesa callbacks with preset scenarios
├── 404.php                    # Custom 404 error page
├── .htaccess                  # Apache: protect sensitive files, security headers, 404 routing
├── config.php                 # Your credentials — gitignored, never commit this file
├── config.example.php         # Safe credential template — copy to config.php
├── favicon.svg                # SVG browser tab icon
├── mpesa_log.json             # Auto-created — one JSON line per callback (gitignored)
├── providers/
│   ├── base.php               # Interface contract — docblocks for all required functions
│   ├── tinypesa.php           # TinyPesa STK Push provider
│   ├── daraja.php             # Safaricom Daraja API STK Push provider
│   ├── pesapal.php            # PesaPal V3 hosted checkout provider
│   └── flutterwave.php        # Flutterwave hosted checkout provider
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
└── LICENSE
```

---

## API Endpoints

### `POST /stk_push.php`

Initiates a payment request using the active provider. Validates input, applies rate limiting, then delegates to the active provider.

**Rate limit:** 5 requests per IP per minute. Returns `429 Too Many Requests` with a `Retry-After` header if exceeded.

**Request body (JSON):**
```json
{
  "phone": "0712345678",
  "amount": "500",
  "reference": "Invoice-001"
}
```

`reference` is optional (max 12 characters). Defaults to an auto-generated order ID if omitted.

**Accepted phone formats:** `0712345678` · `+254712345678` · `254712345678` · `0112345678`

**STK Push success (TinyPesa / Daraja):**
```json
{ "success": true, "message": "STK Push sent! Check your phone.", "reference": "Invoice-001", "flow": "stk", "redirect_url": null }
```

**Hosted checkout success (PesaPal / Flutterwave):**
```json
{ "success": true, "message": "Redirecting to payment page…", "reference": "Invoice-001", "flow": "redirect", "redirect_url": "https://pay.pesapal.com/..." }
```

**Error response:**
```json
{ "success": false, "message": "Invalid Safaricom phone number." }
```

**Rate limit response (HTTP 429):**
```json
{ "success": false, "message": "Too many requests. Please wait before trying again." }
```

The frontend detects `flow: "redirect"` and navigates the user to `redirect_url` automatically.

---

### `POST /callback.php`

Receives the payment result from TinyPesa after the customer enters their M-Pesa PIN. Each callback is appended to `mpesa_log.json` as a single JSON line.

**Expected payload (from TinyPesa/Safaricom):**
```json
{
  "Body": {
    "stkCallback": {
      "MerchantRequestID": "...",
      "CheckoutRequestID": "...",
      "ResultCode": 0,
      "ResultDesc": "The service request is processed successfully.",
      "CallbackMetadata": {
        "Item": [
          { "Name": "Amount",             "Value": 500 },
          { "Name": "MpesaReceiptNumber", "Value": "QHX2Y3Z4AB" },
          { "Name": "TransactionDate",    "Value": 20241210103045 },
          { "Name": "PhoneNumber",        "Value": 254712345678 }
        ]
      }
    }
  }
}
```

`ResultCode: 0` means success. Any other value (e.g. `1032` — user cancelled, `1` — insufficient funds) is logged as a failed transaction.

**Response:**
```json
{ "ResultCode": 0, "ResultDesc": "Accepted" }
```

---

### `GET /check_status.php?phone=0712345678`

Polls `mpesa_log.json` for the most recent confirmed payment matching the given phone number.

**Confirmed:**
```json
{
  "success": true,
  "message": "Payment confirmed",
  "amount": 500,
  "receipt": "QHX2Y3Z4AB",
  "timestamp": "20241210103045"
}
```

**Not yet confirmed:**
```json
{ "success": false }
```

---

### `GET /admin.php`

Browser-based transaction log viewer. Features:
- Summary stat cards (total, confirmed, failed, KES collected)
- 7-day confirmed payments bar chart
- Date range filter (from/to)
- Status filter tabs and phone/receipt search
- Click any row to open a full transaction detail drawer
- Pagination (20 rows per page)
- CSV export button (respects active date filter)

---

### `GET /export.php`

Downloads all transactions as a UTF-8 CSV file (Excel-compatible with BOM).

**Optional query parameters:**

| Parameter | Format       | Description                          |
|-----------|--------------|--------------------------------------|
| `from`    | `YYYY-MM-DD` | Only include transactions on or after |
| `to`      | `YYYY-MM-DD` | Only include transactions on or before |

**Examples:**
```
/export.php                              # All transactions
/export.php?from=2024-12-01              # From 1 Dec 2024 onwards
/export.php?from=2024-12-01&to=2024-12-31  # December 2024 only
```

**CSV columns:** `#`, `Date / Time`, `Phone`, `Amount (KES)`, `Receipt`, `Status`, `Result Code`, `Result Description`, `Reference`

---

### `GET /health.php`

Displays a system health dashboard with pass/warn/fail status for:

| Check | Description |
|-------|-------------|
| PHP Version | Must be 7.4 or higher |
| cURL Extension | Required for TinyPesa API calls |
| JSON Extension | Required for callback parsing |
| config.php | File must exist |
| TinyPesa API Key | Must not be the placeholder value |
| Transaction Log | Directory must be writable |
| Temp Directory | Required for file-based rate limiting |
| HTTPS | Warns if not running over a secure connection |

---

### `GET /webhook_test.php`

Developer-only tool to simulate M-Pesa callbacks without making a real payment. Sends a correctly structured `stkCallback` JSON payload to `callback.php` and shows the full request and response.

**Preset scenarios:**
- ✅ Successful Payment (`ResultCode: 0`)
- ❌ User Cancelled (`ResultCode: 1032`)
- 💸 Insufficient Funds (`ResultCode: 1`)
- 🔒 Wrong PIN (`ResultCode: 2001`)

> Remove or password-protect this page before going to production.

---

## Phone Number Formats Accepted

| Input | Normalised to |
|-------|--------------|
| `0712345678` | `254712345678` |
| `+254712345678` | `254712345678` |
| `254712345678` | `254712345678` |
| `0112345678` | `254112345678` |

---

## Provider Configuration

### TinyPesa (default)

1. Sign up at [tinypesa.com](https://tinypesa.com) and copy your API key from the dashboard
2. In `config.php`, set `PAYMENT_PROVIDER = 'tinypesa'` and `TINYPESA_API_KEY`
3. Set your callback URL in the TinyPesa dashboard to `https://yourdomain.com/callback.php`

### Safaricom Daraja

1. Create an app at [developer.safaricom.co.ke](https://developer.safaricom.co.ke) and get Consumer Key + Consumer Secret
2. Note your Shortcode and Passkey from the Lipa Na M-Pesa Online dashboard
3. Set `PAYMENT_PROVIDER = 'daraja'` and fill in all `DARAJA_*` constants
4. Set `DARAJA_ENV = 'sandbox'` for testing, `'production'` for live
5. Your callback URL is `https://yourdomain.com/callback.php`

### PesaPal V3

1. Register at [developer.pesapal.com](https://developer.pesapal.com) and get Consumer Key + Consumer Secret
2. Set `PAYMENT_PROVIDER = 'pesapal'` and fill in all `PESAPAL_*` constants
3. Set `PESAPAL_IPN_URL` to `https://yourdomain.com/callback_pesapal.php` — PesaPal will POST IPN notifications here
4. Set `PESAPAL_CALLBACK_URL` to where PesaPal should redirect the user after payment (e.g. your homepage)
5. Set `PESAPAL_ENV = 'sandbox'` for testing (`cybqa.pesapal.com`), `'production'` for live (`pay.pesapal.com`)

### Flutterwave

1. Sign up at [flutterwave.com](https://flutterwave.com) and get your Secret Key and Public Key from the dashboard
2. Set `PAYMENT_PROVIDER = 'flutterwave'` and fill in all `FLW_*` constants
3. Set `FLW_REDIRECT_URL` to where Flutterwave should redirect the user after payment
4. In the Flutterwave dashboard under **Webhooks**, set the webhook URL to `https://yourdomain.com/callback_flutterwave.php` and copy the Secret Hash into `FLW_SECRET_HASH`

---

## Security Notes

- **`config.php` is gitignored** — never commit it. Use `config.example.php` as the template.
- **`mpesa_log.json` is gitignored** — it contains real phone numbers and transaction amounts.
- **`.htaccess`** blocks direct browser access to `config.php`, `mpesa_log.json`, and all `.json` files. Enable this by enabling `mod_rewrite` and `AllowOverride All` on Apache.
- **`admin.php` has no authentication by default.** Add HTTP basic auth or a session check before deploying to a public server.
- **`webhook_test.php`** is a development tool — remove it or restrict access in production.
- **Rate limiting** is file-based (uses `sys_get_temp_dir()`). For high-traffic deployments, consider replacing it with Redis or a database-backed approach.
- **Flutterwave webhooks** are signature-verified via `FLW_SECRET_HASH` — always set this in production.
- Callback URLs must be HTTPS endpoints reachable from the internet. Use [ngrok](https://ngrok.com) for local development.

---

## Local Development with ngrok

To test real M-Pesa callbacks on your local machine:

```bash
# Start your PHP server
php -S 0.0.0.0:8000

# In a second terminal, expose it publicly
ngrok http 8000
```

Copy the `https://` URL from ngrok and set it as your callback in the TinyPesa dashboard:
```
https://xxxx-xx-xx-xxx-xx.ngrok-free.app/callback.php
```

Use `/webhook_test.php` to simulate callbacks without ngrok during UI development.

---

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full version history.

---

## License

MIT — see [LICENSE](LICENSE) for details.
