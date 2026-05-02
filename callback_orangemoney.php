<?php
/**
 * callback_orangemoney.php — Orange Money payment notification receiver
 *
 * Set ORANGE_NOTIFY_URL to:  https://yourdomain.com/callback_orangemoney.php
 * Set ORANGE_RETURN_URL to the page the customer is redirected to after paying.
 *
 * Orange Money POSTs a JSON notification when a payment is initiated/completed.
 * The notif_token in the payload can be used to verify with the Orange API.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/orangemoney.php';

header('Content-Type: application/json');

// ── 1. Read body ──────────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    $payload = !empty($_POST) ? $_POST : [];
}

// Orange Money also sends a GET redirect after payment — handle both
if (empty($payload) && !empty($_GET)) {
    $payload = $_GET;
}

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// ── 2. Optional: verify notif_token with Orange API ──────────────────────────
// The notif_token can be validated against the Orange Money check endpoint.
// If ORANGE_CLIENT_ID / ORANGE_CLIENT_SECRET are set, we could re-query the
// status. For now we trust the payload (ensure your callback URL is HTTPS-only).

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
