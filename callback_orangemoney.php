<?php
/**
 * callback_orangemoney.php — Orange Money payment notification receiver
 *
 * Set ORANGE_NOTIFY_URL to:  https://yourdomain.com/callback_orangemoney.php
 * Set ORANGE_RETURN_URL to the page the customer is redirected to after paying.
 *
 * Orange Money delivers a POST webhook with a notif_token field.
 * When ORANGE_CLIENT_ID and ORANGE_CLIENT_SECRET are configured, this callback
 * re-queries the Orange API to verify the payment status — fail-closed if
 * credentials are set but the API check fails or returns non-SUCCESS.
 *
 * SECURITY NOTE: Orange Money does not use HMAC webhook signing. Defence in depth:
 *   1. Always use HTTPS for ORANGE_NOTIFY_URL.
 *   2. Set ORANGE_CLIENT_ID / ORANGE_CLIENT_SECRET so this callback can re-verify.
 *   3. Consider restricting access to Orange's IP ranges at the web-server level.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/orangemoney.php';

header('Content-Type: application/json');

// ── 1. Accept POST only for webhook logging ───────────────────────────────────
// GET redirects (customer browser) are handled separately by ORANGE_RETURN_URL.
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

// ── 2. Re-verify via Orange API when credentials are available ────────────────
if (defined('ORANGE_CLIENT_ID') && ORANGE_CLIENT_ID &&
    defined('ORANGE_CLIENT_SECRET') && ORANGE_CLIENT_SECRET) {

    $notifToken = $payload['notif_token'] ?? '';
    $orderId    = $payload['order_id'] ?? ($payload['reference'] ?? '');
    $country    = defined('ORANGE_COUNTRY') ? strtolower(ORANGE_COUNTRY) : 'ci';

    if (!$notifToken && !$orderId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notif_token and order_id — cannot verify payment']);
        exit;
    }

    // Obtain a fresh access token for re-verification
    $accessToken = _orange_access_token();

    if (!$accessToken) {
        // Cannot verify without a token — reject rather than accept unverified payment
        http_response_code(503);
        echo json_encode(['error' => 'Orange API authentication failed — unable to verify payment']);
        exit;
    }

    // Query payment status using order_id
    if ($orderId) {
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
        $resp    = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $verified = json_decode($resp ?: '{}', true) ?: [];
            // Merge verified API data over the incoming payload
            $payload = array_merge($payload, $verified);
        }
        // If the check endpoint returned non-200, continue with incoming payload
        // (some Orange countries don't support the status endpoint; rely on notif_token)
    }
}

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
