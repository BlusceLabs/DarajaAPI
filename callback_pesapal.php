<?php
/**
 * callback_pesapal.php — PesaPal V3 IPN (Instant Payment Notification) endpoint
 *
 * Register this URL in your PesaPal dashboard as your IPN URL.
 * PesaPal sends a GET request with OrderTrackingId and OrderMerchantReference.
 * This endpoint queries the transaction status and logs the result.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/pesapal.php';

$orderTrackingId = $_GET['OrderTrackingId']        ?? '';
$merchantRef     = $_GET['OrderMerchantReference'] ?? '';

if (!$orderTrackingId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing OrderTrackingId']);
    exit();
}

// Get a fresh token to query transaction status
$token = _pesapal_get_token();
if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'Authentication failed']);
    exit();
}

// Query transaction status from PesaPal
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => _pesapal_base_url() . '/api/Transactions/GetTransactionStatus?orderTrackingId=' . urlencode($orderTrackingId),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to query transaction status']);
    exit();
}

$txStatus = json_decode($response, true);
if (!is_array($txStatus)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from PesaPal']);
    exit();
}

// Normalise and log
$entry = provider_parse_callback($txStatus);

$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['OrderNotificationType' => 'IPNCHANGE', 'OrderTrackingId' => $orderTrackingId, 'OrderMerchantReference' => $merchantRef, 'status' => 200]);

