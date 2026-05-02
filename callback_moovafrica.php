<?php
/**
 * callback_moovafrica.php — Moov Africa / Flooz payment notification receiver
 *
 * Set MOOV_CALLBACK_URL to: https://yourdomain.com/callback_moovafrica.php
 *
 * Moov Africa POSTs a JSON payload when a transaction resolves.
 *
 * SECURITY NOTE: Verify the Authorization header against MOOV_API_KEY
 * when configured — fail-closed: requests without a matching key are rejected.
 * Additional defence in depth:
 *   1. Always use HTTPS for MOOV_CALLBACK_URL.
 *   2. Restrict access to Moov Africa server IP ranges at your firewall level.
 *   3. Cross-check the reference against your own order records.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/moovafrica.php';

header('Content-Type: application/json');

// ── 1. Accept POST only ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── 2. Verify API key header when configured (fail-closed) ───────────────────
if (defined('MOOV_API_KEY') && MOOV_API_KEY) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $keyHeader  = $_SERVER['HTTP_X_API_KEY'] ?? '';

    $provided = $keyHeader ?: (str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : $authHeader);

    if (!$provided) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing API key header']);
        exit;
    }

    if (!hash_equals(MOOV_API_KEY, $provided)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
}

// ── 3. Read body ──────────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload) || empty($payload)) {
    $payload = !empty($_POST) ? $_POST : [];
}

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// ── 4. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
