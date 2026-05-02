<?php
// callback.php — Receives M-Pesa payment results from Safaricom

header("Content-Type: application/json");

$raw = file_get_contents('php://input');

if (!$raw) {
    http_response_code(200);
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit();
}

$data = json_decode($raw, true);

$logFile = __DIR__ . '/mpesa_log.json';

$entry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'raw'       => $data,
];

// Parse useful fields if present
if (isset($data['Body']['stkCallback'])) {
    $cb = $data['Body']['stkCallback'];
    $entry['MerchantRequestID'] = $cb['MerchantRequestID'] ?? null;
    $entry['CheckoutRequestID'] = $cb['CheckoutRequestID'] ?? null;
    $entry['ResultCode']        = $cb['ResultCode'] ?? null;
    $entry['ResultDesc']        = $cb['ResultDesc'] ?? null;

    if (isset($cb['CallbackMetadata']['Item'])) {
        foreach ($cb['CallbackMetadata']['Item'] as $item) {
            $entry[$item['Name']] = $item['Value'] ?? null;
        }
    }
}

// Append as JSON lines
$line = json_encode($entry) . PHP_EOL;
file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
?>
