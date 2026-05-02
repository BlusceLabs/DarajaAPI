<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health — M-Pesa</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <style>
        :root {
            --bg: #f0f4f0;
            --surface: #fff;
            --border: #e0e6e0;
            --text: #222;
            --text-2: #555;
            --text-3: #888;
            --text-4: #aaa;
            --shadow: rgba(0,0,0,0.06);
            --nav-border: #c8e6d4;
            --nav-hover: #f0faf5;
            --overall-ok-bg: #e8f8ef;      --overall-ok-border: #b3e6c8;
            --overall-warn-bg: #fff8e6;    --overall-warn-border: #f0d98a;
            --overall-err-bg: #fdf2f2;     --overall-err-border: #f5c0c0;
            --icon-ok-bg: #e8f8ef;
            --icon-warn-bg: #fff8e6;
            --icon-err-bg: #fdf2f2;
            --badge-ok-bg: #e8f8ef;    --badge-ok-text: #1a6b3a;
            --badge-warn-bg: #fff8e6;  --badge-warn-text: #7a5a00;
            --badge-err-bg: #fdf2f2;   --badge-err-text: #8b2020;
        }
        :root[data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --border: #30363d;
            --text: #e6edf3;
            --text-2: #adbac7;
            --text-3: #768390;
            --text-4: #636e7b;
            --shadow: rgba(0,0,0,0.30);
            --nav-border: #1a4731;
            --nav-hover: #0f2a1a;
            --overall-ok-bg: #0f2a1a;      --overall-ok-border: #1a4731;
            --overall-warn-bg: #2a2000;    --overall-warn-border: #5a4000;
            --overall-err-bg: #2d1111;     --overall-err-border: #5c2020;
            --icon-ok-bg: #0f2a1a;
            --icon-warn-bg: #2a2000;
            --icon-err-bg: #2d1111;
            --badge-ok-bg: #0f2a1a;    --badge-ok-text: #56d364;
            --badge-warn-bg: #2a2000;  --badge-warn-text: #e3b341;
            --badge-err-bg: #2d1111;   --badge-err-text: #f97171;
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
        .wrap { max-width: 680px; margin: 0 auto; }
        .page-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
        }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #006633; }
        :root[data-theme="dark"] .page-header h1 { color: #3fb950; }
        .page-header p  { font-size: 13px; color: var(--text-3); margin-top: 4px; }
        .nav-links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
        .nav-link {
            font-size: 13px; color: #006633; text-decoration: none;
            display: flex; align-items: center; gap: 5px; font-weight: 600;
            padding: 7px 14px; border: 1.5px solid var(--nav-border); border-radius: 8px;
            transition: background 0.15s;
        }
        :root[data-theme="dark"] .nav-link { color: #3fb950; }
        .nav-link:hover { background: var(--nav-hover); }

        .overall {
            border-radius: 14px; padding: 18px 22px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 14px;
        }
        .overall.ok      { background: var(--overall-ok-bg);   border: 1.5px solid var(--overall-ok-border); }
        .overall.warning { background: var(--overall-warn-bg);  border: 1.5px solid var(--overall-warn-border); }
        .overall.error   { background: var(--overall-err-bg);   border: 1.5px solid var(--overall-err-border); }
        .overall-icon { font-size: 28px; }
        .overall h2 { font-size: 16px; font-weight: 700; }
        .overall p  { font-size: 13px; margin-top: 2px; opacity: 0.75; }

        .section { margin-bottom: 18px; }
        .section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.6px; color: var(--text-4); margin-bottom: 10px;
        }
        .checks { display: flex; flex-direction: column; gap: 8px; }
        .check {
            background: var(--surface); border-radius: 12px; padding: 14px 16px;
            box-shadow: 0 1px 6px var(--shadow);
            display: flex; align-items: center; gap: 12px;
        }
        .check-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .check-icon.ok      { background: var(--icon-ok-bg); }
        .check-icon.warning { background: var(--icon-warn-bg); }
        .check-icon.error   { background: var(--icon-err-bg); }
        .check-info { flex: 1; min-width: 0; }
        .check-info strong { font-size: 13.5px; font-weight: 700; display: block; }
        .check-info span   { font-size: 12px; color: var(--text-3); margin-top: 2px; display: block; }
        .check-badge {
            font-size: 11px; font-weight: 700; padding: 3px 10px;
            border-radius: 20px; flex-shrink: 0;
        }
        .check-badge.ok      { background: var(--badge-ok-bg);   color: var(--badge-ok-text); }
        .check-badge.warning { background: var(--badge-warn-bg);  color: var(--badge-warn-text); }
        .check-badge.error   { background: var(--badge-err-bg);   color: var(--badge-err-text); }

        .ts { text-align: center; font-size: 11px; color: var(--text-4); margin-top: 24px; }

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

$checks  = [];
$overall = 'ok';

function addCheck(array &$checks, string $title, string $detail, string $status, string $badge) {
    $checks[] = compact('title', 'detail', 'status', 'badge');
}

function degradeOverall(string &$overall, string $status) {
    if ($status === 'error')   $overall = 'error';
    elseif ($status === 'warning' && $overall === 'ok') $overall = 'warning';
}

$phpVersion = PHP_VERSION;
$phpOk      = version_compare($phpVersion, '7.4.0', '>=');
$phpStatus  = $phpOk ? 'ok' : 'error';
addCheck($checks, 'PHP Version', 'Running PHP ' . $phpVersion . ($phpOk ? ' ✓' : ' — PHP 7.4+ required'), $phpStatus, $phpOk ? 'OK' : 'UPGRADE REQUIRED');
degradeOverall($overall, $phpStatus);

$curlLoaded = extension_loaded('curl');
$curlStatus = $curlLoaded ? 'ok' : 'error';
addCheck($checks, 'cURL Extension', $curlLoaded ? 'Enabled — required for API calls' : 'Not loaded — install php-curl', $curlStatus, $curlLoaded ? 'Enabled' : 'Missing');
degradeOverall($overall, $curlStatus);

$jsonLoaded = extension_loaded('json');
$jsonStatus = $jsonLoaded ? 'ok' : 'error';
addCheck($checks, 'JSON Extension', $jsonLoaded ? 'Enabled' : 'Not loaded', $jsonStatus, $jsonLoaded ? 'Enabled' : 'Missing');
degradeOverall($overall, $jsonStatus);

$configExists = file_exists(__DIR__ . '/config.php');
$configStatus = $configExists ? 'ok' : 'error';
addCheck($checks, 'config.php', $configExists ? 'Found' : 'Missing — copy config.example.php to config.php', $configStatus, $configExists ? 'Found' : 'Missing');
degradeOverall($overall, $configStatus);

$apiKeySet = false;
if ($configExists) {
    require_once __DIR__ . '/config.php';
    $apiKeySet = defined('TINYPESA_API_KEY') && TINYPESA_API_KEY !== 'YOUR_TINYPESA_API_KEY_HERE' && strlen(TINYPESA_API_KEY) > 10;
}
$apiStatus = $apiKeySet ? 'ok' : 'warning';
addCheck($checks, 'TinyPesa API Key', $apiKeySet ? 'Configured' : 'Placeholder value detected — update config.php with your real API key', $apiStatus, $apiKeySet ? 'Set' : 'Not Set');
degradeOverall($overall, $apiStatus);

$logFile     = __DIR__ . '/mpesa_log.json';
$logDir      = __DIR__;
$logWritable = is_writable($logDir);
$logStatus   = $logWritable ? 'ok' : 'error';
$logDetail   = $logWritable
    ? (file_exists($logFile) ? 'Log exists with ' . count(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) . ' entries' : 'Directory writable — log will be created on first callback')
    : 'Directory not writable — callbacks cannot be logged';
addCheck($checks, 'Transaction Log', $logDetail, $logStatus, $logWritable ? 'Writable' : 'Not Writable');
degradeOverall($overall, $logStatus);

$tmpWritable = is_writable(sys_get_temp_dir());
$tmpStatus   = $tmpWritable ? 'ok' : 'warning';
addCheck($checks, 'Temp Directory', $tmpWritable ? sys_get_temp_dir() . ' is writable — rate limiting active' : 'Temp dir not writable — rate limiting will not work', $tmpStatus, $tmpWritable ? 'Writable' : 'Not Writable');
degradeOverall($overall, $tmpStatus);

$isHttps    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
$httpStatus = $isHttps ? 'ok' : 'warning';
addCheck($checks, 'HTTPS', $isHttps ? 'Connection is secure' : 'Not running over HTTPS — callbacks require a public HTTPS URL in production', $httpStatus, $isHttps ? 'Secure' : 'HTTP Only');
degradeOverall($overall, $httpStatus);

$icons = ['ok' => '✅', 'warning' => '⚠️', 'error' => '❌'];
$overallMsgs = [
    'ok'      => ['All Systems Operational', 'Your M-Pesa integration is ready to accept payments.'],
    'warning' => ['Attention Required',       'Some optional checks need review. Core functionality may still work.'],
    'error'   => ['Setup Incomplete',         'Critical issues detected. Resolve them before accepting payments.'],
];
?>

<div class="wrap">
    <div class="page-header">
        <div>
            <h1>System Health</h1>
            <p>Checks your server environment, config, and file permissions</p>
        </div>
        <div class="nav-links">
            <a href="/admin.php" class="nav-link">Transaction Log</a>
            <a href="/" class="nav-link">&#8592; Payment Page</a>
        </div>
    </div>

    <div class="overall <?= $overall ?>">
        <div class="overall-icon"><?= $icons[$overall] ?></div>
        <div>
            <h2><?= $overallMsgs[$overall][0] ?></h2>
            <p><?= $overallMsgs[$overall][1] ?></p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Environment &amp; Configuration</div>
        <div class="checks">
        <?php foreach ($checks as $c): ?>
            <div class="check">
                <div class="check-icon <?= $c['status'] ?>"><?= $icons[$c['status']] ?></div>
                <div class="check-info">
                    <strong><?= htmlspecialchars($c['title']) ?></strong>
                    <span><?= htmlspecialchars($c['detail']) ?></span>
                </div>
                <span class="check-badge <?= $c['status'] ?>"><?= htmlspecialchars($c['badge']) ?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <p class="ts">Checked at <?= date('Y-m-d H:i:s') ?></p>
</div>

<button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle dark mode"></button>

<script>
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
</script>
</body>
</html>
