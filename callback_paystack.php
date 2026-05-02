<?php
/**
 * callback_paystack.php — Paystack webhook receiver
 *
 * In Paystack dashboard: Settings → API Keys & Webhooks → Webhook URL
 * Set to: https://yourdomain.com/callback_paystack.php
 *
 * Paystack signs each request with HMAC-SHA512 using your secret key.
 * The signature is in the X-Paystack-Signature header.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/paystack.php';

header('Content-Type: application/json');

// ── 1. Read raw body ────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');

// ── 2. Verify signature ─────────────────────────────────────────────────────
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
if (!defined('PAYSTACK_SECRET_KEY') || !$signature) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$expected = hash_hmac('sha512', $rawBody, PAYSTACK_SECRET_KEY);
if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// ── 3. Decode payload ───────────────────────────────────────────────────────
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// ── 4. Only handle charge.success ───────────────────────────────────────────
if (($payload['event'] ?? '') !== 'charge.success') {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'event' => $payload['event'] ?? 'unknown']);
    exit;
}

// ── 5. Normalise and log ─────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
