<?php
/**
 * callback_cinetpay.php — CinetPay webhook / IPN receiver
 *
 * In the CinetPay dashboard: Settings → Notify URL
 * Set to: https://yourdomain.com/callback_cinetpay.php
 *
 * CinetPay sends a POST with JSON body containing cpm_trans_id, cpm_trans_status, etc.
 * We verify by calling the CinetPay check endpoint with the transaction ID + API key.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/cinetpay.php';

header('Content-Type: application/json');

// ── 1. Read raw body ─────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    // CinetPay sometimes sends form-encoded; fall back to $_POST
    $payload = !empty($_POST) ? $_POST : [];
}

if (empty($payload['cpm_trans_id']) && empty($payload['transaction_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing transaction ID']);
    exit;
}

// ── 2. Verify transaction status with CinetPay ───────────────────────────────
if (defined('CINETPAY_API_KEY') && defined('CINETPAY_SITE_ID')) {
    $transId  = $payload['cpm_trans_id'] ?? $payload['transaction_id'] ?? '';
    $checkPayload = json_encode([
        'apikey'         => CINETPAY_API_KEY,
        'site_id'        => CINETPAY_SITE_ID,
        'transaction_id' => $transId,
    ]);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api-checkout.cinetpay.com/v2/payment/check',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $checkPayload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $checkResp = curl_exec($ch);
    curl_close($ch);
    $verified = json_decode($checkResp ?: '{}', true);
    if (!empty($verified['data'])) {
        $payload = array_merge($payload, $verified['data']);
    }
}

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
