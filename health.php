<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health — M-Pesa</title>
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
        .wrap { max-width: 680px; margin: 0 auto; }
        .page-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
        }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #006633; }
        .page-header p  { font-size: 13px; color: #888; margin-top: 4px; }
        .nav-links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
        .nav-link {
            font-size: 13px; color: #006633; text-decoration: none;
            display: flex; align-items: center; gap: 5px; font-weight: 600;
            padding: 7px 14px; border: 1.5px solid #c8e6d4; border-radius: 8px;
            transition: background 0.15s;
        }
        .nav-link:hover { background: #f0faf5; }

        .overall {
            border-radius: 14px; padding: 18px 22px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 14px;
        }
        .overall.ok      { background: #e8f8ef; border: 1.5px solid #b3e6c8; }
        .overall.warning { background: #fff8e6; border: 1.5px solid #f0d98a; }
        .overall.error   { background: #fdf2f2; border: 1.5px solid #f5c0c0; }
        .overall-icon { font-size: 28px; }
        .overall h2 { font-size: 16px; font-weight: 700; }
        .overall p  { font-size: 13px; margin-top: 2px; opacity: 0.75; }

        .section { margin-bottom: 18px; }
        .section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.6px; color: #aaa; margin-bottom: 10px;
        }
        .checks { display: flex; flex-direction: column; gap: 8px; }
        .check {
            background: #fff; border-radius: 12px; padding: 14px 16px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 12px;
        }
        .check-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
        .check-icon.ok      { background: #e8f8ef; }
        .check-icon.warning { background: #fff8e6; }
        .check-icon.error   { background: #fdf2f2; }
        .check-info { flex: 1; min-width: 0; }
        .check-info strong { font-size: 13.5px; font-weight: 700; display: block; }
        .check-info span   { font-size: 12px; color: #888; margin-top: 2px; display: block; }
        .check-badge {
            font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; flex-shrink: 0;
        }
        .check-badge.ok      { background: #e8f8ef; color: #1a6b3a; }
        .check-badge.warning { background: #fff8e6; color: #7a5a00; }
        .check-badge.error   { background: #fdf2f2; color: #8b2020; }

        .ts { text-align: center; font-size: 11px; color: #ccc; margin-top: 24px; }
    </style>
</head>
<body>
<?php

// ---------------------------------------------------------------
// Run checks
// ---------------------------------------------------------------
$checks  = [];
$overall = 'ok'; // ok | warning | error

function addCheck(array &$checks, string $title, string $detail, string $status, string $badge) {
    $checks[] = compact('title', 'detail', 'status', 'badge');
}

function degradeOverall(string &$overall, string $status) {
    if ($status === 'error')   $overall = 'error';
    elseif ($status === 'warning' && $overall === 'ok') $overall = 'warning';
}

// 1. PHP version
$phpVersion = PHP_VERSION;
$phpOk      = version_compare($phpVersion, '7.4.0', '>=');
$phpStatus  = $phpOk ? 'ok' : 'error';
addCheck($checks, 'PHP Version', 'Running PHP ' . $phpVersion . ($phpOk ? ' ✓' : ' — PHP 7.4+ required'), $phpStatus, $phpOk ? 'OK' : 'UPGRADE REQUIRED');
degradeOverall($overall, $phpStatus);

// 2. cURL extension
$curlLoaded = extension_loaded('curl');
$curlStatus = $curlLoaded ? 'ok' : 'error';
addCheck($checks, 'cURL Extension', $curlLoaded ? 'Enabled — required for API calls' : 'Not loaded — install php-curl', $curlStatus, $curlLoaded ? 'Enabled' : 'Missing');
degradeOverall($overall, $curlStatus);

// 3. JSON extension
$jsonLoaded = extension_loaded('json');
$jsonStatus = $jsonLoaded ? 'ok' : 'error';
addCheck($checks, 'JSON Extension', $jsonLoaded ? 'Enabled' : 'Not loaded', $jsonStatus, $jsonLoaded ? 'Enabled' : 'Missing');
degradeOverall($overall, $jsonStatus);

// 4. config.php exists
$configExists = file_exists(__DIR__ . '/config.php');
$configStatus = $configExists ? 'ok' : 'error';
addCheck($checks, 'config.php', $configExists ? 'Found' : 'Missing — copy config.example.php to config.php', $configStatus, $configExists ? 'Found' : 'Missing');
degradeOverall($overall, $configStatus);

// 5. API key configured
$apiKeySet = false;
if ($configExists) {
    require_once __DIR__ . '/config.php';
    $apiKeySet = defined('TINYPESA_API_KEY') && TINYPESA_API_KEY !== 'YOUR_TINYPESA_API_KEY_HERE' && strlen(TINYPESA_API_KEY) > 10;
}
$apiStatus = $apiKeySet ? 'ok' : 'warning';
addCheck($checks, 'TinyPesa API Key', $apiKeySet ? 'Configured' : 'Placeholder value detected — update config.php with your real API key', $apiStatus, $apiKeySet ? 'Set' : 'Not Set');
degradeOverall($overall, $apiStatus);

// 6. Log file writable
$logFile    = __DIR__ . '/mpesa_log.json';
$logDir     = __DIR__;
$logWritable = is_writable($logDir);
$logStatus  = $logWritable ? 'ok' : 'error';
$logDetail  = $logWritable
    ? (file_exists($logFile) ? 'Log exists with ' . count(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) . ' entries' : 'Directory writable — log will be created on first callback')
    : 'Directory not writable — callbacks cannot be logged';
addCheck($checks, 'Transaction Log', $logDetail, $logStatus, $logWritable ? 'Writable' : 'Not Writable');
degradeOverall($overall, $logStatus);

// 7. Temp dir writable (rate limiting)
$tmpWritable = is_writable(sys_get_temp_dir());
$tmpStatus   = $tmpWritable ? 'ok' : 'warning';
addCheck($checks, 'Temp Directory', $tmpWritable ? sys_get_temp_dir() . ' is writable — rate limiting active' : 'Temp dir not writable — rate limiting will not work', $tmpStatus, $tmpWritable ? 'Writable' : 'Not Writable');
degradeOverall($overall, $tmpStatus);

// 8. HTTPS check
$isHttps  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
$httpStatus = $isHttps ? 'ok' : 'warning';
addCheck($checks, 'HTTPS', $isHttps ? 'Connection is secure' : 'Not running over HTTPS — callbacks require a public HTTPS URL in production', $httpStatus, $isHttps ? 'Secure' : 'HTTP Only');
degradeOverall($overall, $httpStatus);

$icons   = ['ok' => '✅', 'warning' => '⚠️', 'error' => '❌'];
$overallMsgs = [
    'ok'      => ['All Systems Operational', 'Your M-Pesa integration is ready to accept payments.'],
    'warning' => ['Attention Required',        'Some optional checks need review. Core functionality may still work.'],
    'error'   => ['Setup Incomplete',          'Critical issues detected. Resolve them before accepting payments.'],
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
</body>
</html>
