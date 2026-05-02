<?php
/**
 * callback_airtelmoney.php — Airtel Money webhook receiver
 *
 * In Airtel Money API setup: configure the callback URL in your app settings.
 * Airtel will POST a JSON payload to this URL when the transaction is resolved.
 *
 * Set AIRTEL_CALLBACK_URL = 'https://yourdomain.com/callback_airtelmoney.php' in config.php.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/airtelmoney.php';

header('Content-Type: application/json');

// ── 1. Read raw body ────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// ── 2. Normalise and log ─────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
