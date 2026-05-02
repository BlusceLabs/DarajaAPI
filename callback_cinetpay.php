<?php
/**
 * callback_cinetpay.php — CinetPay webhook / IPN receiver
 *
 * In the CinetPay dashboard: Settings → Notify URL
 * Set to: https://yourdomain.com/callback_cinetpay.php
 *
 * CinetPay sends a POST with JSON (or form-encoded) body containing cpm_trans_id.
 * This callback re-verifies the transaction status via the CinetPay check API
 * and rejects the request if credentials are missing or the check shows non-ACCEPTED.
 *
 * SECURITY: Verification is performed by calling the CinetPay check endpoint
 * with your API key — the incoming payload is never trusted without this check.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/cinetpay.php';

header('Content-Type: application/json');

// ── 1. Read raw body ─────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    // CinetPay sometimes sends form-encoded
    $payload = !empty($_POST) ? $_POST : [];
}

$transId = $payload['cpm_trans_id'] ?? $payload['transaction_id'] ?? '';
if (!$transId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing transaction ID']);
    exit;
}

// ── 2. Re-verify transaction status via CinetPay API (fail-closed) ───────────
if (!defined('CINETPAY_API_KEY') || !defined('CINETPAY_SITE_ID')) {
    http_response_code(503);
    echo json_encode(['error' => 'CinetPay is not configured — cannot verify payment']);
    exit;
}

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
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$checkResp || $httpCode !== 200) {
    http_response_code(503);
    echo json_encode(['error' => 'CinetPay verification endpoint unreachable']);
    exit;
}

$verified = json_decode($checkResp, true);
if (empty($verified['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'CinetPay returned no verification data']);
    exit;
}

// Merge verified API data — this is the authoritative source of truth
$payload = array_merge($payload, $verified['data']);

// Reject if status is not ACCEPTED
$verifiedStatus = strtoupper($verified['data']['cpm_trans_status'] ?? ($verified['data']['status'] ?? ''));
if ($verifiedStatus !== 'ACCEPTED') {
    // Still log as a failed/pending event so admin panel reflects reality
    $entry   = provider_parse_callback($payload);
    $logFile = __DIR__ . '/mpesa_log.json';
    file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

    http_response_code(200); // Return 200 so CinetPay doesn't retry
    echo json_encode(['status' => 'logged', 'result' => strtolower($verifiedStatus)]);
    exit;
}

// ── 3. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
