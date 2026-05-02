<?php
/**
 * callback_wave.php — Wave checkout status webhook receiver
 *
 * Set WAVE_CALLBACK_URL to: https://yourdomain.com/callback_wave.php
 *
 * Wave signs every webhook with HMAC-SHA256 using your API key.
 * The signature is in the `Wave-Signature` header. When WAVE_API_KEY is
 * configured this callback verifies the signature — fail-closed: requests
 * without a valid signature are rejected.
 *
 * Wave sends: { "id": "...", "checkout_status": "succeeded|failed|expired",
 *              "amount": "1000", "currency": "XOF", "client_phone": "..." }
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/wave.php';

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

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// ── 3. Verify Wave-Signature — fail-closed when WAVE_API_KEY is set ──────────
if (defined('WAVE_API_KEY') && WAVE_API_KEY) {
    $sigHeader = $_SERVER['HTTP_WAVE_SIGNATURE'] ?? '';

    if (!$sigHeader) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing Wave-Signature header']);
        exit;
    }

    // Wave signature: HMAC-SHA256 of raw request body using API key
    $expected = hash_hmac('sha256', $rawBody, WAVE_API_KEY);
    if (!hash_equals($expected, $sigHeader)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid Wave-Signature']);
        exit;
    }
}

// ── 4. Only log terminal states ───────────────────────────────────────────────
$checkoutStatus = strtolower($payload['checkout_status'] ?? '');
if (!in_array($checkoutStatus, ['succeeded', 'failed', 'expired'])) {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'checkout_status' => $checkoutStatus]);
    exit;
}

// ── 5. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
