<?php
// callback.php — Receives M-Pesa payment results from TinyPesa / Safaricom

header("Content-Type: application/json");

$raw = file_get_contents('php://input');

if (!$raw) {
    http_response_code(200);
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit();
}

$data = json_decode($raw, true);

$entry = ['timestamp' => date('Y-m-d H:i:s')];

// Standard Safaricom / TinyPesa stkCallback envelope
if (isset($data['Body']['stkCallback'])) {
    $cb = $data['Body']['stkCallback'];

    $entry['MerchantRequestID'] = $cb['MerchantRequestID'] ?? null;
    $entry['CheckoutRequestID'] = $cb['CheckoutRequestID'] ?? null;
    $entry['ResultCode']        = $cb['ResultCode']        ?? null;
    $entry['ResultDesc']        = $cb['ResultDesc']        ?? null;

    if (isset($cb['CallbackMetadata']['Item']) && is_array($cb['CallbackMetadata']['Item'])) {
        foreach ($cb['CallbackMetadata']['Item'] as $item) {
            $entry[$item['Name']] = $item['Value'] ?? null;
        }
    }
} else {
    // Fallback: store raw payload for debugging
    $entry['raw'] = $data;
}

// Append as a JSON line to the log file
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
?>
