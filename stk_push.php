<?php
// stk_push.php — Payment initiation router (provider-agnostic)

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
$ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile   = sys_get_temp_dir() . '/mpesa_rate_' . md5($ip) . '.json';
$rateLimit  = 5;
$rateWindow = 60;

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

if (!$phone || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Phone number and amount are required.']);
    exit();
}

if (!is_numeric($amount) || (float)$amount < 1 || (float)$amount > 150000) {
    echo json_encode(['success' => false, 'message' => 'Amount must be between KES 1 and KES 150,000.']);
    exit();
}

// Normalise phone to 254XXXXXXXXX
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
// LOAD ACTIVE PROVIDER
// ------------------------------------------------------------------
$provider     = defined('PAYMENT_PROVIDER') ? PAYMENT_PROVIDER : 'tinypesa';
$providerFile = __DIR__ . '/providers/' . preg_replace('/[^a-z]/', '', $provider) . '.php';

if (!file_exists($providerFile)) {
    echo json_encode(['success' => false, 'message' => 'Unsupported payment provider: ' . htmlspecialchars($provider)]);
    exit();
}

require_once $providerFile;

// ------------------------------------------------------------------
// INITIATE PAYMENT
// ------------------------------------------------------------------
$result = provider_initiate($phone, (float)$amount, $reference);
echo json_encode($result);
