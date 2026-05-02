# Changelog

All notable changes to this project are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

## [1.1.0] — 2024-12-10

### Added
- `admin.php` — transaction log viewer with summary stats and status filtering
- Quick-select amount buttons on the payment page (KES 50, 100, 500, 1000, 5000)
- Optional **Account Reference** field on the payment form
- Receipt number, amount, and timestamp shown on payment confirmation
- `CHANGELOG.md` — this file
- `CONTRIBUTING.md` — contribution guide
- `config.example.php` — safe credential template for contributors

### Fixed
- `stk_push.php` — was reading from `$_GET` instead of the POST JSON body sent by the frontend
- `config.php` — removed incorrect `header()` call that broke files requiring this config
- `stk_push.php` — now properly imports `config.php` for credentials
- Phone number validation now rejects non-Safaricom numbers before calling the API

### Improved
- `callback.php` — correctly parses the full `stkCallback` envelope and stores structured JSON lines
- `check_status.php` — proper `ResultCode === 0` check; searches newest entries first
- `.gitignore` — excludes `config.php`, `mpesa_log.json`, OS/editor files, and Replit internals
- Payment UI — loading spinner, status messages, and progress bar while waiting for PIN confirmation

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
