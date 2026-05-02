<?php
// callback.php — Receives payment results (TinyPesa / Daraja stkCallback format)
// For PesaPal use callback_pesapal.php; for Flutterwave use callback_flutterwave.php

header("Content-Type: application/json");

$raw = file_get_contents('php://input');

if (!$raw) {
    http_response_code(200);
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit();
}

require_once __DIR__ . '/config.php';

$data = json_decode($raw, true) ?? [];

$provider     = defined('PAYMENT_PROVIDER') ? PAYMENT_PROVIDER : 'tinypesa';
$providerFile = __DIR__ . '/providers/' . preg_replace('/[^a-z]/', '', $provider) . '.php';

if (file_exists($providerFile)) {
    require_once $providerFile;
    $entry = provider_parse_callback(is_array($data) ? $data : []);
} else {
    // Fallback: store raw payload
    $entry = [
        'timestamp'  => date('Y-m-d H:i:s'),
        'provider'   => $provider,
        'ResultCode' => null,
        'ResultDesc' => 'Unknown provider',
        'raw'        => $data,
    ];
}

$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
