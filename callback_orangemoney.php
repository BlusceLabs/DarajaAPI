<?php
/**
 * callback_orangemoney.php — Orange Money payment notification receiver
 *
 * Set ORANGE_NOTIFY_URL to:  https://yourdomain.com/callback_orangemoney.php
 * Set ORANGE_RETURN_URL to the page the customer is redirected to after paying.
 *
 * Orange Money delivers a POST webhook with an order_id / notif_token field.
 * When ORANGE_CLIENT_ID and ORANGE_CLIENT_SECRET are configured this callback
 * re-queries the Orange API to verify the payment status (fail-closed):
 *   - Auth failure  → 503, do not log
 *   - API non-200  → 503, do not log
 *   - Non-SUCCESS  → log as failed, return 200 (so Orange stops retrying)
 *
 * SECURITY NOTE: Orange Money does not use HMAC webhook signing. Defence in depth:
 *   1. Always use HTTPS for ORANGE_NOTIFY_URL.
 *   2. Set ORANGE_CLIENT_ID / ORANGE_CLIENT_SECRET so this callback can re-verify.
 *   3. Consider restricting access to Orange's IP ranges at the web-server level.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/orangemoney.php';

header('Content-Type: application/json');

// ── 1. Accept POST only ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

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

// ── 2. Re-verify via Orange API when credentials are configured (fail-closed) ─
if (defined('ORANGE_CLIENT_ID') && ORANGE_CLIENT_ID &&
    defined('ORANGE_CLIENT_SECRET') && ORANGE_CLIENT_SECRET) {

    $orderId = $payload['order_id'] ?? ($payload['reference'] ?? '');
    $country = defined('ORANGE_COUNTRY') ? strtolower(ORANGE_COUNTRY) : 'ci';

    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing order_id — cannot verify payment']);
        exit;
    }

    // Obtain a fresh access token
    $accessToken = _orange_access_token();
    if (!$accessToken) {
        // Cannot authenticate with Orange — reject rather than accept unverified data
        http_response_code(503);
        echo json_encode(['error' => 'Orange API authentication failed — unable to verify payment']);
        exit;
    }

    // Re-query payment status using order_id (fail-closed on non-200)
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => "https://api.orange.com/orange-money-webpay/{$country}/v1/webpayment/{$orderId}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$resp) {
        // Orange API unreachable or returned an error — do not accept unverified payment
        http_response_code(503);
        echo json_encode(['error' => 'Orange Money verification failed — HTTP ' . $httpCode]);
        exit;
    }

    $verified = json_decode($resp, true) ?: [];
    // Merge verified data from the API (authoritative) over the incoming payload
    $payload = array_merge($payload, $verified);
}

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
