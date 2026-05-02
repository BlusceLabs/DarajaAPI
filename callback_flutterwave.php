<?php
/**
 * callback_flutterwave.php — Flutterwave webhook endpoint
 *
 * Register this URL in your Flutterwave dashboard under Webhooks.
 * Verifies the signature hash and logs the normalised transaction result.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/flutterwave.php';

$raw = file_get_contents('php://input');

if (!$raw) {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit();
}

// Verify webhook signature if FLW_SECRET_HASH is configured
if (defined('FLW_SECRET_HASH') && FLW_SECRET_HASH) {
    $signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';
    if (!hash_equals(FLW_SECRET_HASH, $signature)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit();
    }
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit();
}

// Only process completed charge events
$event = $data['event'] ?? '';
if ($event !== 'charge.completed') {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'event' => $event]);
    exit();
}

$entry   = provider_parse_callback($data);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);

