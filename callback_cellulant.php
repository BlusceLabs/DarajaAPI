<?php
/**
 * callback_cellulant.php — Cellulant Tingg payment notification receiver
 *
 * Set CELLULANT_CALLBACK_URL to: https://yourdomain.com/callback_cellulant.php
 *
 * Tingg POSTs a JSON notification when a checkout payment resolves.
 * The payload contains requestStatusCode (178 = PAID), merchantTransactionID, etc.
 * Tingg also redirects customers here after payment (GET) — both are handled.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/cellulant.php';

header('Content-Type: application/json');

// ── 1. Read body (POST webhook) or query string (GET redirect) ────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload) || empty($payload)) {
    $payload = !empty($_POST) ? $_POST : [];
}
if (empty($payload) && !empty($_GET)) {
    $payload = $_GET;
}

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// ── 2. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
