<?php
// export.php — Downloads transactions as a UTF-8 CSV file
// Optional query params: ?from=YYYY-MM-DD&to=YYYY-MM-DD

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

// ---------------------------------------------------------------
// Optional date-range filter
// ---------------------------------------------------------------
$from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : null;
$to   = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : null;

if ($from || $to) {
    $entries = array_values(array_filter($entries, function ($e) use ($from, $to) {
        $day = substr($e['timestamp'] ?? '', 0, 10);
        if ($from && $day < $from) return false;
        if ($to   && $day > $to)   return false;
        return true;
    }));
}

// ---------------------------------------------------------------
// Build filename
// ---------------------------------------------------------------
$suffix = '';
if ($from || $to) {
    $suffix = '_' . ($from ?: 'start') . '_to_' . ($to ?: 'end');
}
$filename = 'mpesa_transactions' . $suffix . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// BOM for Excel UTF-8 compatibility
fwrite($out, "\xEF\xBB\xBF");

// Header row
fputcsv($out, ['#', 'Date / Time', 'Provider', 'Phone', 'Amount (KES)', 'Receipt', 'Status', 'Result Code', 'Result Description', 'Reference']);


$total = count($entries);
foreach (array_reverse($entries) as $i => $e) {
    $rc       = $e['ResultCode'] ?? null;
    $status   = ($rc === null) ? 'Pending' : ($rc === 0 ? 'Confirmed' : 'Failed');
    $phone    = isset($e['PhoneNumber']) ? '0' . substr((string)$e['PhoneNumber'], 3) : '';
    $amount   = $e['Amount'] ?? '';
    $receipt  = $e['MpesaReceiptNumber'] ?? '';
    $date     = $e['timestamp'] ?? '';
    $desc     = $e['ResultDesc'] ?? '';
    $ref      = $e['AccountReference'] ?? $e['reference'] ?? $e['Reference'] ?? '';
    $provider = $e['provider'] ?? 'tinypesa';

    fputcsv($out, [$total - $i, $date, $provider, $phone, $amount, $receipt, $status, $rc ?? '', $desc, $ref]);

}

fclose($out);
exit();
