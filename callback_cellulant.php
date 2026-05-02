<?php
/**
 * callback_cellulant.php — Cellulant Tingg payment notification receiver
 *
 * Set CELLULANT_CALLBACK_URL to: https://yourdomain.com/callback_cellulant.php
 *
 * Tingg POSTs a JSON payload when a checkout resolves (requestStatusCode 178 = PAID).
 * Tingg also redirects customers to this URL after payment — only POST is logged.
 *
 * SECURITY NOTE: Tingg webhook payloads are authenticated via OAuth2 Bearer token
 * in the Authorization header. When CELLULANT_API_KEY is configured, this callback
 * validates the header value against the known key — fail-closed if it mismatches.
 * Defence in depth:
 *   1. Always use HTTPS for CELLULANT_CALLBACK_URL.
 *   2. Set CELLULANT_API_KEY so incoming webhook tokens can be validated.
 *   3. Restrict access to Tingg server IP ranges at your firewall level.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/cellulant.php';

header('Content-Type: application/json');

// ── 1. Accept POST only for webhook (GET is the customer redirect) ────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Silently return 200 for browser redirects — do not log GET requests
    http_response_code(200);
    echo json_encode(['status' => 'redirect']);
    exit;
}

// ── 2. Verify API-Key header — fail-closed when CELLULANT_API_KEY is set ─────
if (defined('CELLULANT_API_KEY') && CELLULANT_API_KEY) {
    $apiKeyHeader = $_SERVER['HTTP_API_KEY'] ?? '';
    if (!$apiKeyHeader) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing API-Key header']);
        exit;
    }
    if (!hash_equals(CELLULANT_API_KEY, $apiKeyHeader)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API-Key']);
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
