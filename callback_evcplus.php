<?php
/**
 * callback_evcplus.php — EVC Plus async notification receiver
 *
 * EVC Plus (Hormuud) is primarily a synchronous API — the payment result is
 * returned immediately in the initiation response. This file handles any
 * asynchronous push notifications Hormuud may send for disputed or delayed
 * transactions.
 *
 * SECURITY NOTE: Hormuud EVC Plus does not provide HMAC webhook signing.
 * Defence in depth for production deployments:
 *   1. Always use HTTPS for your callback URL.
 *   2. Restrict access to Hormuud server IP ranges at your web-server / firewall
 *      level (obtain the current IP list from Hormuud merchant support).
 *   3. Validate the issuerTransactionId against your own transaction records before
 *      marking an order as paid — never trust the payload blindly.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/evcplus.php';

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

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
