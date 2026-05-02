# M-Pesa STK Push — PHP Integration

## Project Overview
Open-source PHP integration for Lipa Na M-Pesa Online (STK Push) using the TinyPesa API. Accepts M-Pesa payments via a polished web UI with real-time status polling.

## Architecture

### Server
- PHP built-in dev server on port **5000** (workflow: `PHP Server`)
- No framework — plain PHP + vanilla JS

### File Structure
```
index.php            Payment UI — phone, amount chips, reference, inline validation
stk_push.php         POST endpoint — validates input, rate-limits, calls TinyPesa API
callback.php         Receives M-Pesa payment callbacks, appends JSON lines to log
check_status.php     GET polling endpoint — returns confirmed payment details
admin.php            Transaction log viewer — stats, filter, search, CSV export link
export.php           Streams mpesa_log.json as a downloadable CSV
404.php              Custom 404 error page
config.php           Credentials (gitignored — never commit)
config.example.php   Safe template — copy to config.php
favicon.svg          SVG browser tab icon
mpesa_log.json       Auto-created — one JSON line per callback (gitignored)
README.md            Full setup and API documentation
CHANGELOG.md         Version history
CONTRIBUTING.md      Contribution guide
LICENSE              MIT
```

### Data Flow
1. User submits phone + amount on `index.php`
2. Frontend POSTs JSON to `stk_push.php`
3. `stk_push.php` validates, rate-limits, calls TinyPesa API
4. TinyPesa sends STK prompt to customer's phone
5. Customer enters PIN → Safaricom sends callback to `callback.php`
6. `callback.php` parses and appends to `mpesa_log.json`
7. Frontend polls `check_status.php` every 5s for up to 60s
8. On confirmation, shows receipt number, amount, and timestamp

### Payment Log Format (mpesa_log.json)
One JSON object per line:
```json
{
  "timestamp": "2024-12-10 10:30:45",
  "MerchantRequestID": "...",
  "CheckoutRequestID": "...",
  "ResultCode": 0,
  "ResultDesc": "The service request is processed successfully.",
  "Amount": 500,
  "MpesaReceiptNumber": "QHX2Y3Z4AB",
  "TransactionDate": 20241210103045,
  "PhoneNumber": 254712345678
}
```

## Key Configuration
- Credentials in `config.php` — uses `TINYPESA_API_KEY` and `TINYPESA_URL`
- Rate limit: 5 requests per IP per minute (file-based, stored in `/tmp/`)
- Phone validation: strips prefix, requires `(7|1)\d{8}` pattern (Safaricom only)
- Amount range: KES 1 – 150,000

## User Preferences
- Open source project — all code must remain clean, documented, and framework-free
- Keep UI polished but lightweight (no external CSS/JS libraries)
- Every enhancement should improve real-world usability, not just aesthetics
