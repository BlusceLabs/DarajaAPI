<?php
/**
 * callback_ecocash.php — Ecocash payment notification receiver
 *
 * Configure ECOCASH_CALLBACK_URL to point to this file:
 *   https://yourdomain.com/callback_ecocash.php
 *
 * Ecocash sends a JSON POST when a transaction resolves.
 * The payload contains transactionOperationStatus, paymentAmount, etc.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/ecocash.php';

header('Content-Type: application/json');

// ── 1. Read body ──────────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    $payload = !empty($_POST) ? $_POST : [];
}

if (empty($payload['transactionOperationStatus']) && empty($payload['serverCorrelator'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unrecognised Ecocash payload']);
    exit;
}

// ── 2. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
