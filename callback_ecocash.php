<?php
/**
 * callback_ecocash.php — Ecocash payment notification receiver
 *
 * Configure ECOCASH_CALLBACK_URL to point to this file:
 *   https://yourdomain.com/callback_ecocash.php
 *
 * Ecocash POSTs a JSON payload when a transaction resolves.
 *
 * SECURITY NOTE: The Ecocash Merchant API does not provide HMAC webhook signing.
 * Defence in depth for production deployments:
 *   1. Always use HTTPS for ECOCASH_CALLBACK_URL.
 *   2. Restrict access to Ecocash server IP ranges at your web-server / firewall level
 *      (obtain the current IP list from your Ecocash merchant support contact).
 *   3. Validate the serverCorrelator against your own transaction records before
 *      acting on a payment notification.
 *
 * This callback only logs the notification — your application code should cross-check
 * the correlator against a database of pending transactions before marking orders paid.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/ecocash.php';

header('Content-Type: application/json');

// ── 1. Accept POST only ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── 2. Read body ──────────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload) || empty($payload)) {
    $payload = !empty($_POST) ? $_POST : [];
}

if (empty($payload['transactionOperationStatus']) && empty($payload['serverCorrelator'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unrecognised Ecocash payload structure']);
    exit;
}

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
