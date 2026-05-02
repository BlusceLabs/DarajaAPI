# Contributing to M-Pesa STK Push PHP

Thank you for considering contributing! Here's how to get involved.

---

## Getting Started

1. **Fork** the repository on GitHub.
2. **Clone** your fork locally:
   ```bash
   git clone https://github.com/your-username/mpesa-stk-push-php.git
   ```
3. **Create a branch** for your change:
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. **Set up** your local config:
   ```bash
   cp config.example.php config.php
   # Fill in your Sandbox credentials
   ```

---

## What to Contribute

- Bug fixes
- Support for additional Daraja API endpoints (B2C, C2B, transaction status, etc.)
- Better error handling or logging
- UI improvements
- Documentation improvements
- Tests

---

## Code Style

- Follow existing code style (PSR-12 for PHP).
- Keep functions small and focused.
- Add comments for non-obvious logic.
- Never commit real credentials — use `config.example.php` as a template.

---

## Submitting a Pull Request

1. Make sure your changes work against the **Sandbox** environment.
2. Update `README.md` if you add or change any endpoints or configuration options.
3. Open a Pull Request against the `main` branch with a clear description of what you changed and why.

---

## Reporting Bugs

Open a [GitHub Issue](../../issues) with:
- PHP version
- Steps to reproduce
- Expected vs actual behaviour
- Any relevant log output (sanitize credentials first!)

---

## Questions?

Open a Discussion on GitHub or raise an Issue — we're happy to help.
