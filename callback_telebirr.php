<?php
/**
 * callback_telebirr.php — Telebirr payment notification receiver
 *
 * Set TELEBIRR_NOTIFY_URL to: https://yourdomain.com/callback_telebirr.php
 *
 * Telebirr POSTs a JSON notification when a transaction resolves.
 * The payload contains tradeNo, outBizNo, totalAmount, tradeStatus, msisdn.
 *
 * SECURITY NOTE: Telebirr does not use HMAC webhook signing.
 * Defence in depth for production deployments:
 *   1. Always use HTTPS for TELEBIRR_NOTIFY_URL.
 *   2. Restrict access to Ethiotelecom server IP ranges at your firewall level
 *      (request the current IP list from the Telebirr developer portal).
 *   3. Validate outBizNo against your own order records before marking orders paid.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/telebirr.php';

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

// Telebirr expects a specific acknowledgement response
http_response_code(200);
echo json_encode(['code' => '0', 'message' => 'success']);
