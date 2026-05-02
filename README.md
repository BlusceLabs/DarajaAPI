# 🟢 M-Pesa STK Push — PHP Integration

A clean, ready-to-use PHP implementation of the **Safaricom Daraja API** (STK Push / Lipa Na M-Pesa Online). Drop it into any PHP project to accept M-Pesa payments instantly.

---

## Features

- ✅ STK Push (prompt customer's phone for PIN)
- ✅ Automatic access token generation
- ✅ Supports Paybill and Till Number (Buy Goods)
- ✅ Callback handler that logs all payment results
- ✅ Payment status polling endpoint
- ✅ Polished payment UI (no framework dependencies)
- ✅ Sandbox & Production environments

---

## Requirements

- PHP 7.4 or higher
- `curl` PHP extension enabled
- A [Safaricom Daraja](https://developer.safaricom.co.ke/) account

---

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/your-username/mpesa-stk-push-php.git
cd mpesa-stk-push-php
```

### 2. Configure your credentials

Copy the example config and fill in your details:

```bash
cp config.example.php config.php
```

Then edit `config.php`:

```php
define('CONSUMER_KEY',    'your_consumer_key');
define('CONSUMER_SECRET', 'your_consumer_secret');
define('BUSINESS_SHORTCODE', '174379');          // Sandbox default
define('PASSKEY', 'your_passkey');
define('ENV', 'sandbox');                        // or 'production'
define('CALLBACK_URL', 'https://yourdomain.com/callback.php');
```

### 3. Run the server

```bash
php -S 0.0.0.0:8000
```

Open `http://localhost:8000` in your browser.

---

## File Structure

```
├── index.php           # Payment UI (frontend)
├── stk_push.php        # STK Push API endpoint
├── callback.php        # M-Pesa payment result receiver
├── check_status.php    # Payment status polling endpoint
├── config.php          # Your credentials (not committed)
├── config.example.php  # Template — copy to config.php
└── mpesa_log.json      # Auto-created — transaction log
```

---

## API Endpoints

### `POST /stk_push.php`

Initiates an STK push to the customer's phone.

**Request body (JSON):**
```json
{
  "phone": "0712345678",
  "amount": "100"
}
```

**Success response:**
```json
{ "success": true, "message": "STK Push sent successfully! Check your phone." }
```

**Error response:**
```json
{ "success": false, "message": "M-Pesa Error: ..." }
```

---

### `POST /callback.php`

Receives the payment result from Safaricom after the customer enters their PIN. Set this as your **Callback URL** in `config.php`.

All callbacks are appended to `mpesa_log.json`.

---

### `GET /check_status.php?phone=0712345678`

Polls the transaction log for a confirmed payment matching the given phone number.

**Confirmed response:**
```json
{
  "success": true,
  "message": "Payment confirmed",
  "amount": 100,
  "receipt": "QHX2Y3Z4AB",
  "timestamp": "20241210103045"
}
```

**Not yet confirmed:**
```json
{ "success": false }
```

---

## Paybill vs Till Number

Edit `config.php` to switch between payment types:

| Type | `TRANSACTION_TYPE` | `PARTY_B` |
|------|-------------------|-----------|
| Paybill | `CustomerPayBillOnline` | Shortcode |
| Till (Buy Goods) | `CustomerBuyGoodsOnline` | Till Number |

---

## Going Live (Production)

1. Complete the **Go-Live** checklist on the [Daraja Portal](https://developer.safaricom.co.ke/).
2. Set `ENV` to `'production'` in `config.php`.
3. Update `CONSUMER_KEY`, `CONSUMER_SECRET`, `BUSINESS_SHORTCODE`, and `PASSKEY` with your production values.
4. Update `CALLBACK_URL` to your live HTTPS URL.

> **Important:** Your callback URL must be publicly accessible over HTTPS. Safaricom will not deliver callbacks to localhost.

---

## Security Notes

- `config.php` is listed in `.gitignore` — never commit it.
- `mpesa_log.json` is also excluded from version control.
- In production, restrict direct access to `.php` backend files via your web server config (nginx/Apache).

---

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting a pull request.

---

## License

This project is licensed under the **MIT License** — see [LICENSE](LICENSE) for details.
