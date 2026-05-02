<?php
// check_status.php — Polls the log for a confirmed payment by phone number

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/config.php';

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

// Load the active provider for its check_status logic
$provider     = defined('PAYMENT_PROVIDER') ? PAYMENT_PROVIDER : 'tinypesa';
$providerFile = __DIR__ . '/providers/' . $provider . '.php';

if (file_exists($providerFile)) {
    require_once $providerFile;
    $useProviderCheck = true;
} else {
    $useProviderCheck = false;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Search newest entries first
foreach (array_reverse($lines) as $line) {
    $entry = json_decode($line, true);
    if (!is_array($entry)) continue;

    $confirmed = false;

    if ($useProviderCheck) {
        $confirmed = provider_check_status($entry, $phone);
    } else {
        // Fallback: standard Safaricom ResultCode 0 + PhoneNumber match
        $entryPhone = (string)($entry['PhoneNumber'] ?? '');
        $resultCode = $entry['ResultCode'] ?? null;
        $confirmed  = ($entryPhone === $phone && $resultCode === 0);
    }

    if ($confirmed) {
        echo json_encode([
            'success'   => true,
            'message'   => 'Payment confirmed',
            'amount'    => $entry['Amount']             ?? null,
            'currency'  => $entry['currency']           ?? 'KES',
            'receipt'   => $entry['MpesaReceiptNumber'] ?? null,
            'timestamp' => $entry['TransactionDate']    ?? $entry['timestamp'] ?? null,
        ]);
        exit();
    }
}

echo json_encode(['success' => false]);
?>
