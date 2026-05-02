<?php
/**
 * providers/base.php — Provider interface contract
 *
 * Every provider file (tinypesa.php, daraja.php, pesapal.php, flutterwave.php)
 * must implement the four functions declared below.
 *
 * This file is documentation only — it is not required() at runtime.
 */

/**
 * Returns the payment flow type for this provider.
 *
 * @return string  'stk'      — sends a PIN prompt to the customer's phone (TinyPesa, Daraja)
 *                 'redirect' — returns a hosted checkout URL (PesaPal, Flutterwave)
 */
// function provider_flow(): string {}

/**
 * Initiates a payment request.
 *
 * @param  string $phone      Normalised phone number, e.g. "254712345678"
 * @param  float  $amount     Payment amount in KES
 * @param  string $reference  Sanitised order/account reference (max 12 chars)
 * @return array {
 *   bool        success      true on success, false on error
 *   string      message      Human-readable result description
 *   string      reference    Echo back the reference used
 *   string|null redirect_url Hosted checkout URL (redirect flow only), null for STK
 *   string      flow         'stk' | 'redirect' — mirrors provider_flow()
 * }
 */
// function provider_initiate(string $phone, float $amount, string $reference): array {}

/**
 * Parses a raw callback/webhook payload and returns a normalised log entry.
 *
 * All providers must map their payload to the shared schema:
 *   timestamp           string   "Y-m-d H:i:s"
 *   provider            string   provider name, e.g. "tinypesa"
 *   ResultCode          int      0 = success, non-zero = failure/cancel
 *   ResultDesc          string   Human-readable status description
 *   PhoneNumber         string   "254XXXXXXXXX" — used by check_status.php
 *   Amount              float    KES amount
 *   MpesaReceiptNumber  string   Transaction/receipt ID (any provider)
 *   TransactionDate     string   ISO date or Safaricom-format timestamp
 *   Reference           string   Order reference
 *
 * @param  array $raw  Decoded JSON payload from the callback request
 * @return array       Normalised log entry ready to append to mpesa_log.json
 */
// function provider_parse_callback(array $raw): array {}

/**
 * Checks whether a specific log entry represents a confirmed payment for the given phone.
 *
 * Used by check_status.php to find confirmed payments without knowing the provider.
 *
 * @param  array  $entry  One decoded log entry from mpesa_log.json
 * @param  string $phone  Normalised phone "254XXXXXXXXX"
 * @return bool
 */
// function provider_check_status(array $entry, string $phone): bool {}
