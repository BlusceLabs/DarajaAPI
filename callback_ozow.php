<?php
/**
 * callback_ozow.php — Ozow payment notification receiver
 *
 * Ozow POSTs to your NotifyUrl when a payment is complete/cancelled/error.
 * Set OZOW_NOTIFY_URL = 'https://yourdomain.com/callback_ozow.php' in config.php.
 *
 * Hash verification: SHA512 of all POST fields + private key (lowercase, no spaces).
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/ozow.php';

header('Content-Type: application/json');

// ── 1. Read POST data ────────────────────────────────────────────────────────
$data = $_POST;

if (empty($data)) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// ── 2. Verify hash ───────────────────────────────────────────────────────────
if (!defined('OZOW_PRIVATE_KEY')) {
    http_response_code(500);
    echo json_encode(['error' => 'Ozow not configured']);
    exit;
}

$receivedHash = strtolower($data['Hash'] ?? '');
unset($data['Hash']);

// Build hash input: concatenate all field values in the order Ozow sends them, append private key
$hashInput = strtolower(implode('', array_values($data)) . OZOW_PRIVATE_KEY);
$expected  = hash('sha512', $hashInput);

if (!hash_equals($expected, $receivedHash)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid hash']);
    exit;
}

// ── 3. Normalise and log ─────────────────────────────────────────────────────
$entry   = provider_parse_callback($data);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
