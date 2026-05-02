<?php
// stk_push.php — Initiates an M-Pesa STK Push via TinyPesa

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once 'config.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ------------------------------------------------------------------
// RATE LIMITING — max 5 requests per IP per minute
// ------------------------------------------------------------------
$ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile  = sys_get_temp_dir() . '/mpesa_rate_' . md5($ip) . '.json';
$rateLimit = 5;
$rateWindow = 60; // seconds

$rateData = ['count' => 0, 'window_start' => time()];
if (file_exists($rateFile)) {
    $stored = json_decode(file_get_contents($rateFile), true);
    if (is_array($stored) && (time() - $stored['window_start']) < $rateWindow) {
        $rateData = $stored;
    }
}

if ($rateData['count'] >= $rateLimit) {
    $retryAfter = $rateWindow - (time() - $rateData['window_start']);
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    echo json_encode([
        'success' => false,
        'message' => 'Too many requests. Please wait ' . $retryAfter . ' seconds and try again.',
    ]);
    exit();
}

$rateData['count']++;
file_put_contents($rateFile, json_encode($rateData), LOCK_EX);

// ------------------------------------------------------------------
// INPUT
// ------------------------------------------------------------------
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$phone     = trim($data['phone']     ?? '');
$amount    = trim($data['amount']    ?? '');
$reference = trim($data['reference'] ?? '');

// Validation
if (!$phone || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Phone number and amount are required.']);
    exit();
}

if (!is_numeric($amount) || (float)$amount < 1 || (float)$amount > 150000) {
    echo json_encode(['success' => false, 'message' => 'Amount must be between KES 1 and KES 150,000.']);
    exit();
}

// Normalise phone to 2547XXXXXXXX or 2541XXXXXXXX
$phone = preg_replace('/^(\+254|254|0)/', '', $phone);
if (!preg_match('/^(7|1)\d{8}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Safaricom phone number. Use 07XX or 01XX format.']);
    exit();
}
$phone = '254' . $phone;

// Sanitise reference (alphanumeric + hyphens, max 12 chars)
$reference = preg_replace('/[^A-Za-z0-9\-]/', '', $reference);
$reference = substr($reference, 0, 12);
if (!$reference) {
    $reference = 'Order-' . strtoupper(substr(md5(uniqid()), 0, 6));
}

// ------------------------------------------------------------------
// CALL TINYPESA
// ------------------------------------------------------------------
$body = http_build_query([
    'amount'     => (int)$amount,
    'msisdn'     => $phone,
    'account_no' => $reference,
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => TINYPESA_URL,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'ApiKey: '     . TINYPESA_API_KEY,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'message' => 'Connection error: ' . $curlErr]);
    exit();
}

$result = json_decode($response, true);

if ($httpCode === 200) {
    echo json_encode([
        'success'   => true,
        'message'   => 'STK Push sent! Check your phone.',
        'reference' => $reference,
    ]);
} else {
    $errMsg = $result['message'] ?? $result['detail'] ?? 'Unexpected error from payment provider.';
    echo json_encode(['success' => false, 'message' => $errMsg]);
}
?>
