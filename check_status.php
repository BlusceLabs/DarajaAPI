<?php
// check_status.php — Polls the log for a confirmed payment by phone number

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$rawPhone = trim($_GET['phone'] ?? '');
$logFile  = __DIR__ . '/mpesa_log.json';

if (!$rawPhone) {
    echo json_encode(['success' => false, 'message' => 'Phone number required.']);
    exit();
}

// Normalise to 254XXXXXXXXX
$phone = preg_replace('/^(\+254|254|0)/', '', $rawPhone);
$phone = '254' . $phone;

if (!file_exists($logFile)) {
    echo json_encode(['success' => false]);
    exit();
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Search newest entries first
foreach (array_reverse($lines) as $line) {
    $entry = json_decode($line, true);
    if (!is_array($entry)) continue;

    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    $resultCode = $entry['ResultCode'] ?? null;

    // Match phone and confirm ResultCode 0 = success
    if ($entryPhone === $phone && $resultCode === 0) {
        echo json_encode([
            'success'   => true,
            'message'   => 'Payment confirmed',
            'amount'    => $entry['Amount']              ?? null,
            'receipt'   => $entry['MpesaReceiptNumber']  ?? null,
            'timestamp' => $entry['TransactionDate']     ?? $entry['timestamp'] ?? null,
        ]);
        exit();
    }
}

echo json_encode(['success' => false]);
?>
