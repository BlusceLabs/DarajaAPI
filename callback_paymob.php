<?php
/**
 * callback_paymob.php — Paymob webhook receiver
 *
 * In Paymob portal: Developers → Webhooks → Transaction processed URL
 * Set to: https://yourdomain.com/callback_paymob.php
 *
 * Paymob signs webhooks with HMAC-SHA512 of a concatenated string of specific
 * transaction fields. Set PAYMOB_HMAC_SECRET to enforce verification (recommended).
 * When PAYMOB_HMAC_SECRET is configured, requests without a valid HMAC are rejected.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/paymob.php';

header('Content-Type: application/json');

// ── 1. Read body ──────────────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// ── 2. Verify HMAC signature — fail-closed when PAYMOB_HMAC_SECRET is set ────
if (defined('PAYMOB_HMAC_SECRET') && PAYMOB_HMAC_SECRET) {
    $hmacHeader = $_SERVER['HTTP_HMAC'] ?? ($_GET['hmac'] ?? '');

    if (!$hmacHeader) {
        // Secret is configured but signature is absent — reject to prevent spoofing
        http_response_code(401);
        echo json_encode(['error' => 'Missing HMAC signature — set PAYMOB_HMAC_SECRET in your Paymob dashboard']);
        exit;
    }

    $obj = $payload['obj'] ?? [];
    // Paymob HMAC concatenates these fields in alphabetical order
    $fields = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
        'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
        'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];
    $concat = '';
    foreach ($fields as $field) {
        if (str_contains($field, '.')) {
            [$parent, $child] = explode('.', $field, 2);
            $val = $obj[$parent][$child] ?? '';
        } else {
            $val = $obj[$field] ?? '';
        }
        $concat .= (is_bool($val) ? ($val ? 'true' : 'false') : (string)$val);
    }
    $expected = hash_hmac('sha512', $concat, PAYMOB_HMAC_SECRET);
    if (!hash_equals($expected, $hmacHeader)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid HMAC signature']);
        exit;
    }
}

// ── 3. Only handle successful transactions ────────────────────────────────────
$obj = $payload['obj'] ?? $payload;
if (!filter_var($obj['success'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'not successful']);
    exit;
}

// ── 4. Normalise and log ──────────────────────────────────────────────────────
$entry   = provider_parse_callback($payload);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok']);
