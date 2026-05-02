# M-Pesa STK Push — PHP Integration

A clean, ready-to-use PHP integration for **Lipa Na M-Pesa Online** (STK Push) using the [TinyPesa](https://tinypesa.com) API. No complex OAuth flows — just a simple API key and you're ready to accept M-Pesa payments.

---

## Features

- STK Push — prompts the customer's phone for their M-Pesa PIN
- Quick-amount buttons and optional account reference on the payment form
- Callback handler that parses and logs all payment results as structured JSON
- Payment status polling — auto-detects confirmation in the browser
- Transaction log admin page with summary stats and status filtering
- Phone number validation (rejects non-Safaricom numbers before calling the API)
- No framework dependencies — plain PHP + vanilla JS

---

## Requirements

- PHP 7.4 or higher with the `curl` extension enabled
- A [TinyPesa](https://tinypesa.com) account and API key

---

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/your-username/mpesa-stk-push-php.git
cd mpesa-stk-push-php
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

> Safaricom requires a publicly accessible HTTPS URL. The callback cannot point to localhost.

### 4. Run the server

```bash
php -S 0.0.0.0:8000
```

Open `http://localhost:8000` in your browser.

---

## File Structure

```
├── index.php            # Payment UI — phone, amount, quick-select chips, reference
├── stk_push.php         # POST endpoint — validates input, calls TinyPesa API
├── callback.php         # Receives M-Pesa results from TinyPesa / Safaricom
├── check_status.php     # GET endpoint — polls log for payment confirmation
├── admin.php            # Transaction log viewer with stats and filtering
├── config.php           # Your credentials (gitignored — never commit)
├── config.example.php   # Safe template — copy to config.php
├── favicon.svg          # Browser tab icon
├── mpesa_log.json        # Auto-created — one JSON entry per callback
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
└── LICENSE
```

---

## API Endpoints

### `POST /stk_push.php`

Triggers an STK push to the customer's phone.

**Request body (JSON):**
```json
{
  "phone": "0712345678",
  "amount": "500",
  "reference": "Invoice-001"
}
```
`reference` is optional (max 12 characters). Defaults to an auto-generated order ID.

**Success:**
```json
{ "success": true, "message": "STK Push sent! Check your phone.", "reference": "Invoice-001" }
```

**Error:**
```json
{ "success": false, "message": "Invalid Safaricom phone number." }
```

---

### `POST /callback.php`

Receives the payment result from TinyPesa after the customer enters their PIN.
All callbacks are appended to `mpesa_log.json` as JSON lines.

**Responds with:**
```json
{ "ResultCode": 0, "ResultDesc": "Accepted" }
```

---

### `GET /check_status.php?phone=0712345678`

Polls `mpesa_log.json` for a confirmed payment matching the given phone number.

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

Browser-based transaction log viewer. Shows:
- Summary cards: total requests, confirmed, failed, total KES collected
- A filterable table of all transactions with phone, amount, receipt, status, and result description

---

## Phone Number Formats Accepted

| Input | Normalised |
|-------|-----------|
| `0712345678` | `254712345678` |
| `+254712345678` | `254712345678` |
| `254712345678` | `254712345678` |
| `0112345678` | `254112345678` |

---

## Security Notes

- `config.php` is in `.gitignore` — never commit it
- `mpesa_log.json` is also excluded — it contains real phone numbers and amounts
- In production, restrict browser access to `stk_push.php`, `callback.php`, and `check_status.php` via your web server (nginx/Apache) so only authorised clients can call them
- `admin.php` has no authentication by default — add HTTP basic auth or a session check before deploying publicly

---

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

---

## License

MIT — see [LICENSE](LICENSE) for details.
