<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Log — M-Pesa Admin</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <style>
        :root {
            --bg: #f0f4f0;
            --surface: #fff;
            --surface-alt: #fafafa;
            --surface-head: #f8faf8;
            --border: #e0e6e0;
            --border-light: #f0f0f0;
            --border-row: #f5f5f5;
            --text: #222;
            --text-2: #555;
            --text-3: #888;
            --text-4: #aaa;
            --text-5: #bbb;
            --text-6: #ccc;
            --text-7: #999;
            --text-8: #777;
            --shadow: rgba(0,0,0,0.06);
            --shadow-lg: rgba(0,0,0,0.08);
            --row-hover: #f9fbf9;
            --filter-border: #ddd;
            --filter-text: #666;
            --search-border: #e8e8e8;
            --search-text: #333;
            --badge-ok-bg: #e6f7ee;    --badge-ok-text: #1a6b3a;
            --badge-fail-bg: #fdf2f2;  --badge-fail-text: #8b2020;
            --badge-pend-bg: #fff8e6;  --badge-pend-text: #7a5a00;
            --stat-value: #006633;
            --stat-neutral: #222;
            --stat-red: #c0392b;
            --nav-border: #c8e6d4;
            --nav-hover: #f0faf5;
        }
        :root[data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-alt: #0d1117;
            --surface-head: #1c2128;
            --border: #30363d;
            --border-light: #21262d;
            --border-row: #21262d;
            --text: #e6edf3;
            --text-2: #adbac7;
            --text-3: #768390;
            --text-4: #636e7b;
            --text-5: #545d68;
            --text-6: #444c56;
            --text-7: #768390;
            --text-8: #636e7b;
            --shadow: rgba(0,0,0,0.30);
            --shadow-lg: rgba(0,0,0,0.40);
            --row-hover: #1c2128;
            --filter-border: #30363d;
            --filter-text: #adbac7;
            --search-border: #30363d;
            --search-text: #e6edf3;
            --badge-ok-bg: #0f2a1a;    --badge-ok-text: #56d364;
            --badge-fail-bg: #2d1111;  --badge-fail-text: #f97171;
            --badge-pend-bg: #2a2000;  --badge-pend-text: #e3b341;
            --stat-value: #3fb950;
            --stat-neutral: #e6edf3;
            --stat-red: #f97171;
            --nav-border: #1a4731;
            --nav-hover: #0f2a1a;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 32px 20px;
            transition: background 0.2s, color 0.2s;
        }
        .header {
            max-width: 960px; margin: 0 auto 24px;
            display: flex; align-items: flex-start; justify-content: space-between;
            flex-wrap: wrap; gap: 14px;
        }
        .header h1 { font-size: 22px; font-weight: 700; color: #006633; }
        :root[data-theme="dark"] .header h1 { color: #3fb950; }
        .header-meta { font-size: 12px; color: var(--text-4); margin-top: 4px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .live-dot { width: 7px; height: 7px; background: #00a651; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
        .header-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .back-link {
            font-size: 13px; color: #006633; text-decoration: none;
            display: flex; align-items: center; gap: 5px; font-weight: 600;
            padding: 7px 14px; border: 1.5px solid var(--nav-border); border-radius: 8px;
            transition: background 0.15s;
        }
        :root[data-theme="dark"] .back-link { color: #3fb950; }
        .back-link:hover { background: var(--nav-hover); }
        .export-btn {
            font-size: 13px; color: #fff; text-decoration: none;
            display: flex; align-items: center; gap: 6px; font-weight: 600;
            padding: 7px 14px; background: #006633; border-radius: 8px;
            transition: opacity 0.15s;
        }
        .export-btn:hover { opacity: 0.85; }
        .refresh-toggle {
            font-size: 12px; color: var(--text-3); display: flex; align-items: center; gap: 6px;
            cursor: pointer; user-select: none;
        }
        .toggle-switch {
            width: 32px; height: 18px; background: var(--filter-border); border-radius: 9px;
            position: relative; transition: background 0.2s; flex-shrink: 0;
        }
        .toggle-switch.on { background: #00a651; }
        .toggle-switch::after {
            content: ''; position: absolute; width: 14px; height: 14px;
            background: #fff; border-radius: 50%; top: 2px; left: 2px;
            transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle-switch.on::after { left: 16px; }

        .stats {
            max-width: 960px; margin: 0 auto 22px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;
        }
        .stat-card {
            background: var(--surface); border-radius: 14px;
            padding: 16px 18px; box-shadow: 0 2px 10px var(--shadow);
        }
        .stat-card .label { font-size: 11px; color: var(--text-4); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; }
        .stat-card .value { font-size: 28px; font-weight: 800; margin-top: 5px; color: var(--stat-value); }
        .stat-card .value.neutral { color: var(--stat-neutral); }
        .stat-card .value.red { color: var(--stat-red); }
        .stat-card .sub { font-size: 11px; color: var(--text-5); margin-top: 3px; }

        .date-filter {
            max-width: 960px; margin: 0 auto 16px;
            background: var(--surface); border-radius: 14px;
            box-shadow: 0 2px 10px var(--shadow);
            padding: 14px 18px;
            display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
        }
        .date-filter label { font-size: 12px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; }
        .date-filter input[type="date"] {
            padding: 7px 10px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; color: var(--text); background: var(--surface-alt); cursor: pointer;
            transition: border-color 0.2s;
        }
        .date-filter input[type="date"]:focus { outline: none; border-color: #00a651; }
        .date-sep { font-size: 12px; color: var(--text-5); }
        .date-clear {
            font-size: 12px; color: var(--text-4); background: none; border: none;
            cursor: pointer; text-decoration: underline; margin-left: auto; padding: 0;
        }
        .date-clear:hover { color: #c0392b; }

        .table-wrap {
            max-width: 960px; margin: 0 auto;
            background: var(--surface); border-radius: 16px;
            box-shadow: 0 2px 16px var(--shadow-lg); overflow: hidden;
        }
        .table-toolbar {
            padding: 14px 18px; border-bottom: 1px solid var(--border-light);
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap;
        }
        .toolbar-left { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .table-toolbar h2 { font-size: 14px; font-weight: 700; }
        .filter-tabs { display: flex; gap: 6px; }
        .filter-tab {
            padding: 4px 13px; border-radius: 20px; font-size: 12px; font-weight: 600;
            cursor: pointer; border: 1.5px solid var(--filter-border);
            background: var(--surface); color: var(--filter-text);
            transition: all 0.15s;
        }
        .filter-tab.active, .filter-tab:hover { background: #006633; color: #fff; border-color: #006633; }

        .search-wrap { position: relative; }
        .search-wrap input {
            padding: 6px 10px 6px 30px; border: 1.5px solid var(--search-border); border-radius: 8px;
            font-size: 12px; color: var(--search-text); background: var(--surface-alt);
            width: 180px; transition: border-color 0.2s;
        }
        .search-wrap input:focus { outline: none; border-color: #00a651; }
        .search-wrap svg { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--surface-head); text-align: left; padding: 10px 14px;
            font-size: 10.5px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.6px; color: var(--text-4); border-bottom: 1px solid var(--border-light);
        }
        tbody tr { border-bottom: 1px solid var(--border-row); transition: background 0.1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--row-hover); }
        td { padding: 11px 14px; font-size: 13px; vertical-align: middle; }
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .badge.success { background: var(--badge-ok-bg);   color: var(--badge-ok-text); }
        .badge.failed  { background: var(--badge-fail-bg); color: var(--badge-fail-text); }
        .badge.pending { background: var(--badge-pend-bg); color: var(--badge-pend-text); }
        .receipt { font-family: monospace; font-size: 12px; color: var(--text-2); }
        .amount  { font-weight: 700; }
        .phone   { color: var(--text-2); }
        .no-results { text-align: center; padding: 30px; color: var(--text-5); font-size: 13px; display: none; }
        .empty { text-align: center; padding: 60px 20px; color: var(--text-5); }
        .empty svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.35; }
        .empty p { font-size: 14px; }
        .countdown { font-size: 11px; color: var(--text-5); }

        .theme-btn {
            position: fixed; bottom: 20px; right: 20px;
            width: 40px; height: 40px; border-radius: 50%;
            border: 1.5px solid var(--border);
            background: var(--surface); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 12px var(--shadow); z-index: 999;
            font-size: 17px; transition: all 0.2s;
        }
        .theme-btn:hover { transform: scale(1.1); }
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

$total     = count($entries);
$confirmed = 0;
$failed    = 0;
$totalAmt  = 0;

foreach ($entries as $e) {
    $rc = $e['ResultCode'] ?? null;
    if ($rc === 0)              { $confirmed++; $totalAmt += (float)($e['Amount'] ?? 0); }
    if ($rc !== null && $rc !== 0) $failed++;
}

$pending     = $total - $confirmed - $failed;
$successRate = $total > 0 ? round(($confirmed / $total) * 100) : 0;
$lastUpdated = date('H:i:s');
?>

<div class="header">
    <div>
        <h1>Transaction Log</h1>
        <div class="header-meta">
            <span class="live-dot"></span>
            <span>Last updated: <strong id="lastUpdated"><?= $lastUpdated ?></strong></span>
            <span class="countdown" id="countdown"></span>
        </div>
    </div>
    <div class="header-actions">
        <label class="refresh-toggle" title="Auto-refresh every 30 seconds">
            <span class="toggle-switch on" id="toggleSwitch"></span>
            Auto-refresh
        </label>
        <a href="/export.php" class="export-btn" download>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
        </a>
        <a href="/health.php" class="back-link">Health</a>
        <a href="/webhook_test.php" class="back-link">Simulator</a>
        <a href="/" class="back-link">&#8592; Payment Page</a>
    </div>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="label">Total Requests</div>
        <div class="value neutral"><?= $total ?></div>
        <div class="sub">All time</div>
    </div>
    <div class="stat-card">
        <div class="label">Confirmed</div>
        <div class="value"><?= $confirmed ?></div>
        <div class="sub"><?= $successRate ?>% success rate</div>
    </div>
    <div class="stat-card">
        <div class="label">Failed / Cancelled</div>
        <div class="value red"><?= $failed ?></div>
        <div class="sub"><?= $pending ?> pending</div>
    </div>
    <div class="stat-card">
        <div class="label">Total Collected</div>
        <div class="value">KES <?= number_format($totalAmt, 2) ?></div>
        <div class="sub">Confirmed payments only</div>
    </div>
</div>

<div class="date-filter">
    <label>Date Range</label>
    <input type="date" id="dateFrom" onchange="applySearch()" title="From date">
    <span class="date-sep">—</span>
    <input type="date" id="dateTo" onchange="applySearch()" title="To date">
    <button class="date-clear" onclick="clearDates()">Clear dates</button>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="toolbar-left">
            <h2>All Transactions</h2>
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="applyFilter('all', this)">All</button>
                <button class="filter-tab" onclick="applyFilter('success', this)">Confirmed</button>
                <button class="filter-tab" onclick="applyFilter('failed', this)">Failed</button>
                <button class="filter-tab" onclick="applyFilter('pending', this)">Pending</button>
            </div>
        </div>
        <div class="search-wrap">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--text-5)">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Search phone or receipt…" oninput="applySearch()">
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
            $rawPhone = (string)($e['PhoneNumber'] ?? '');
            $phone   = $rawPhone ? '0' . substr($rawPhone, 3) : '—';
            $amount  = isset($e['Amount']) ? 'KES ' . number_format((float)$e['Amount'], 2) : '—';
            $receipt = $e['MpesaReceiptNumber'] ?? '—';
            $date    = $e['timestamp'] ?? '—';
            $desc    = $e['ResultDesc'] ?? '—';
        ?>
        <tr data-status="<?= $status ?>" data-date="<?= substr($date, 0, 10) ?>" data-search="<?= strtolower(htmlspecialchars($phone . ' ' . $receipt)) ?>">
            <td style="color:var(--text-6);font-size:12px"><?= $total - $i ?></td>
            <td style="white-space:nowrap;color:var(--text-8)"><?= htmlspecialchars($date) ?></td>
            <td class="phone"><?= htmlspecialchars($phone) ?></td>
            <td class="amount"><?= htmlspecialchars($amount) ?></td>
            <td class="receipt"><?= htmlspecialchars($receipt) ?></td>
            <td><span class="badge <?= $status ?>"><?= $icon ?> <?= $label ?></span></td>
            <td style="color:var(--text-7);font-size:12px;max-width:200px"><?= htmlspecialchars($desc) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="no-results" id="noResults">No transactions match your search.</div>
    <?php endif; ?>
</div>

<button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle dark mode"></button>

<script>
    // ---- Theme ----
    function toggleTheme() {
        const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        updateThemeIcon();
    }
    function updateThemeIcon() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.getElementById('themeBtn').textContent = dark ? '☀️' : '🌙';
    }
    updateThemeIcon();

    // ---- Auto-refresh ----
    let currentFilter = 'all';
    let autoRefresh   = true;
    let countdown     = 30;
    let timer         = null;

    const toggleSwitch = document.getElementById('toggleSwitch');
    const countdownEl  = document.getElementById('countdown');

    toggleSwitch.parentElement.addEventListener('click', () => {
        autoRefresh = !autoRefresh;
        toggleSwitch.classList.toggle('on', autoRefresh);
        if (autoRefresh) { countdown = 30; startCountdown(); }
        else { clearInterval(timer); countdownEl.textContent = ''; }
    });

    function startCountdown() {
        clearInterval(timer);
        timer = setInterval(() => {
            countdown--;
            countdownEl.textContent = '(refreshing in ' + countdown + 's)';
            if (countdown <= 0) window.location.reload();
        }, 1000);
    }

    if (autoRefresh) startCountdown();

    // ---- Filter ----
    function applyFilter(type, btn) {
        currentFilter = type;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        applySearch();
    }

    // ---- Search + date filter ----
    function applySearch() {
        const q        = document.getElementById('searchInput').value.toLowerCase().trim();
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo   = document.getElementById('dateTo').value;
        let visible = 0;

        document.querySelectorAll('#txTable tbody tr').forEach(row => {
            const matchFilter = currentFilter === 'all' || row.dataset.status === currentFilter;
            const matchSearch = !q || row.dataset.search.includes(q);
            const rowDate     = row.dataset.date || '';
            const matchFrom   = !dateFrom || rowDate >= dateFrom;
            const matchTo     = !dateTo   || rowDate <= dateTo;
            const show = matchFilter && matchSearch && matchFrom && matchTo;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const noRes = document.getElementById('noResults');
        if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
    }

    function clearDates() {
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value   = '';
        applySearch();
    }
</script>
</body>
</html>
