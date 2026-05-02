<?php
/**
 * callback_mtnmomo.php — MTN MoMo webhook receiver
 *
 * In MTN MoMo API setup: set X-Callback-Url header when calling requesttopay.
 * MTN will POST a JSON payload to this URL when the transaction is resolved.
 *
 * MTN does not sign webhook payloads — rely on your server being HTTPS-only
 * and validate the data by querying GET /collection/v1_0/requesttopay/{referenceId}.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/mtnmomo.php';

header('Content-Type: application/json');

// ── 1. Read raw body ────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// ── 2. Normalise and log ─────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
