<?php
// export.php — Downloads all transactions as a CSV file

$logFile = __DIR__ . '/mpesa_log.json';

if (!file_exists($logFile)) {
    http_response_code(404);
    exit('No transaction log found.');
}

$lines   = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$entries = [];
foreach ($lines as $line) {
    $e = json_decode($line, true);
    if (is_array($e)) $entries[] = $e;
}

$filename = 'mpesa_transactions_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// BOM for Excel UTF-8 compatibility
fwrite($out, "\xEF\xBB\xBF");

// Header row
fputcsv($out, ['#', 'Date / Time', 'Phone', 'Amount (KES)', 'Receipt', 'Status', 'Result Description', 'Reference']);

$total = count($entries);
foreach (array_reverse($entries) as $i => $e) {
    $rc      = $e['ResultCode'] ?? null;
    $status  = ($rc === null) ? 'Pending' : ($rc === 0 ? 'Confirmed' : 'Failed');
    $phone   = isset($e['PhoneNumber']) ? '0' . substr((string)$e['PhoneNumber'], 3) : '';
    $amount  = $e['Amount'] ?? '';
    $receipt = $e['MpesaReceiptNumber'] ?? '';
    $date    = $e['timestamp'] ?? '';
    $desc    = $e['ResultDesc'] ?? '';
    $ref     = $e['AccountReference'] ?? '';

    fputcsv($out, [$total - $i, $date, $phone, $amount, $receipt, $status, $desc, $ref]);
}

fclose($out);
exit();
?>
