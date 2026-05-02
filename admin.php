<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Log — M-Pesa Admin</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f0;
            min-height: 100vh;
            padding: 32px 20px;
            color: #222;
        }
        .header {
            max-width: 900px;
            margin: 0 auto 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #006633;
        }
        .header p { font-size: 13px; color: #888; margin-top: 3px; }
        .back-link {
            font-size: 13px;
            color: #006633;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }
        .back-link:hover { text-decoration: underline; }

        .stats {
            max-width: 900px;
            margin: 0 auto 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }
        .stat-card .label { font-size: 12px; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .value { font-size: 26px; font-weight: 700; margin-top: 6px; color: #006633; }
        .stat-card .value.neutral { color: #222; }
        .stat-card .value.red { color: #c0392b; }

        .table-wrap {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .table-toolbar {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .table-toolbar h2 { font-size: 15px; font-weight: 700; }
        .filter-tabs { display: flex; gap: 6px; }
        .filter-tab {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #ddd;
            background: #fff;
            color: #666;
            transition: all 0.15s;
        }
        .filter-tab.active, .filter-tab:hover {
            background: #006633;
            color: #fff;
            border-color: #006633;
        }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f7faf7;
            text-align: left;
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #888;
            border-bottom: 1px solid #eee;
        }
        tbody tr { border-bottom: 1px solid #f5f5f5; transition: background 0.1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f9fbf9; }
        td { padding: 12px 16px; font-size: 13px; vertical-align: middle; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge.success { background: #e6f7ee; color: #1a6b3a; }
        .badge.failed  { background: #fdf2f2; color: #8b2020; }
        .badge.pending { background: #fff8e6; color: #7a5a00; }
        .receipt { font-family: monospace; font-size: 12px; color: #555; }
        .amount  { font-weight: 700; }
        .phone   { color: #555; }
        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #bbb;
        }
        .empty svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.4; }
        .empty p { font-size: 14px; }
    </style>
</head>
<body>

<?php
$logFile = __DIR__ . '/mpesa_log.json';
$entries = [];

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $e = json_decode($line, true);
        if (is_array($e)) $entries[] = $e;
    }
}

// Stats
$total     = count($entries);
$confirmed = 0;
$failed    = 0;
$totalAmt  = 0;

foreach ($entries as $e) {
    $rc = $e['ResultCode'] ?? null;
    if ($rc === 0)  { $confirmed++; $totalAmt += (float)($e['Amount'] ?? 0); }
    if ($rc !== null && $rc !== 0) $failed++;
}

$pending = $total - $confirmed - $failed;
?>

<div class="header">
    <div>
        <h1>Transaction Log</h1>
        <p>All M-Pesa STK Push callbacks received</p>
    </div>
    <a href="/" class="back-link">&#8592; Payment Page</a>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="label">Total Requests</div>
        <div class="value neutral"><?= $total ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Confirmed</div>
        <div class="value"><?= $confirmed ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Failed / Cancelled</div>
        <div class="value red"><?= $failed ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Total Collected</div>
        <div class="value">KES <?= number_format($totalAmt, 2) ?></div>
    </div>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <h2>All Transactions</h2>
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterTable('all', this)">All</button>
            <button class="filter-tab" onclick="filterTable('success', this)">Confirmed</button>
            <button class="filter-tab" onclick="filterTable('failed', this)">Failed</button>
        </div>
    </div>

    <?php if (empty($entries)): ?>
    <div class="empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
        </svg>
        <p>No transactions yet. They will appear here after payments are made.</p>
    </div>
    <?php else: ?>
    <table id="txTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Date / Time</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (array_reverse($entries) as $i => $e):
            $rc      = $e['ResultCode'] ?? null;
            $status  = ($rc === null) ? 'pending' : ($rc === 0 ? 'success' : 'failed');
            $label   = ($rc === null) ? 'Pending'  : ($rc === 0 ? 'Confirmed' : 'Failed');
            $icon    = ($rc === null) ? '⏳'        : ($rc === 0 ? '✅' : '❌');
            $phone   = isset($e['PhoneNumber']) ? '0' . substr((string)$e['PhoneNumber'], 3) : '—';
            $amount  = isset($e['Amount']) ? 'KES ' . number_format((float)$e['Amount'], 2) : '—';
            $receipt = $e['MpesaReceiptNumber'] ?? '—';
            $date    = $e['timestamp'] ?? '—';
            $desc    = $e['ResultDesc'] ?? '—';
        ?>
        <tr data-status="<?= $status ?>">
            <td style="color:#bbb;font-size:12px"><?= $total - $i ?></td>
            <td style="white-space:nowrap"><?= htmlspecialchars($date) ?></td>
            <td class="phone"><?= htmlspecialchars($phone) ?></td>
            <td class="amount"><?= htmlspecialchars($amount) ?></td>
            <td class="receipt"><?= htmlspecialchars($receipt) ?></td>
            <td><span class="badge <?= $status ?>"><?= $icon ?> <?= $label ?></span></td>
            <td style="color:#888;font-size:12px;max-width:220px"><?= htmlspecialchars($desc) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
function filterTable(type, btn) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#txTable tbody tr').forEach(row => {
        row.style.display = (type === 'all' || row.dataset.status === type) ? '' : 'none';
    });
}
</script>
</body>
</html>
