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
            --provider-bg: #eef4ff;    --provider-border: #c0d8ff; --provider-text: #1a3a8f;
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
            --provider-bg: #0d1e3a;    --provider-border: #1a3a6b; --provider-text: #79b8ff;
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

        /* Active provider banner */
        .provider-banner {
            border-radius: 12px; padding: 14px 18px; margin-bottom: 18px;
            background: var(--provider-bg); border: 1.5px solid var(--provider-border);
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .provider-icon { font-size: 20px; }
        .provider-info { flex: 1; }
        .provider-info strong { font-size: 13.5px; font-weight: 700; color: var(--provider-text); display: block; }
        .provider-info span   { font-size: 12px; color: var(--text-3); margin-top: 2px; display: block; }
        .provider-badge {
            font-size: 11px; font-weight: 700; padding: 4px 12px;
            border-radius: 20px; background: var(--provider-text); color: #fff;
            text-transform: uppercase; letter-spacing: 0.4px; flex-shrink: 0;
        }

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

// ---- Environment checks (provider-independent) ----
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

// ---- Load config and determine active provider ----
$activeProvider = 'tinypesa';
$providerLabels = [
    'tinypesa'    => ['TinyPesa',      'stk',      '📱'],
    'daraja'      => ['Safaricom Daraja', 'stk',   '📱'],
    'pesapal'     => ['PesaPal V3',    'redirect',  '🌐'],
    'flutterwave' => ['Flutterwave',   'redirect',  '🌐'],
];

if ($configExists) {
    require_once __DIR__ . '/config.php';
    $activeProvider = defined('PAYMENT_PROVIDER') ? PAYMENT_PROVIDER : 'tinypesa';
}

$providerLabel = $providerLabels[$activeProvider] ?? [$activeProvider, 'unknown', '❓'];

// ---- Provider-specific credential checks ----
$providerChecks = [];

switch ($activeProvider) {
    case 'tinypesa':
        $ok = defined('TINYPESA_API_KEY') && TINYPESA_API_KEY !== 'YOUR_TINYPESA_API_KEY_HERE' && strlen(TINYPESA_API_KEY) > 10;
        $providerChecks[] = ['TinyPesa API Key', $ok ? 'Configured' : 'Placeholder value — update config.php with your real API key', $ok ? 'ok' : 'warning', $ok ? 'Set' : 'Not Set'];
        break;

    case 'daraja':
        $ck = defined('DARAJA_CONSUMER_KEY')    && strlen(DARAJA_CONSUMER_KEY)    > 5 && DARAJA_CONSUMER_KEY    !== 'YOUR_DARAJA_CONSUMER_KEY';
        $cs = defined('DARAJA_CONSUMER_SECRET') && strlen(DARAJA_CONSUMER_SECRET) > 5 && DARAJA_CONSUMER_SECRET !== 'YOUR_DARAJA_CONSUMER_SECRET';
        $sc = defined('DARAJA_SHORTCODE')       && strlen(DARAJA_SHORTCODE)       > 3;
        $pk = defined('DARAJA_PASSKEY')         && strlen(DARAJA_PASSKEY)         > 5;
        $cb = defined('DARAJA_CALLBACK_URL')    && strpos(DARAJA_CALLBACK_URL, 'http') === 0;
        $providerChecks[] = ['Daraja Consumer Key',    $ck ? 'Configured' : 'Not set or placeholder', $ck ? 'ok' : 'warning', $ck ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Daraja Consumer Secret', $cs ? 'Configured' : 'Not set or placeholder', $cs ? 'ok' : 'warning', $cs ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Daraja Shortcode',       $sc ? 'Configured' : 'Not set',                $sc ? 'ok' : 'warning', $sc ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Daraja Passkey',         $pk ? 'Configured' : 'Not set',                $pk ? 'ok' : 'warning', $pk ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Daraja Callback URL',    $cb ? (DARAJA_CALLBACK_URL) : 'Not set — set to your public HTTPS callback URL', $cb ? 'ok' : 'warning', $cb ? 'Set' : 'Not Set'];
        $env = defined('DARAJA_ENV') ? DARAJA_ENV : 'sandbox';
        $providerChecks[] = ['Daraja Environment',     'Currently: ' . $env, 'ok', strtoupper($env)];
        break;

    case 'pesapal':
        $ck  = defined('PESAPAL_CONSUMER_KEY')    && strlen(PESAPAL_CONSUMER_KEY)    > 5;
        $cs  = defined('PESAPAL_CONSUMER_SECRET') && strlen(PESAPAL_CONSUMER_SECRET) > 5;
        $ipn = defined('PESAPAL_IPN_URL')         && strpos(PESAPAL_IPN_URL, 'http') === 0;
        $cb  = defined('PESAPAL_CALLBACK_URL')    && strpos(PESAPAL_CALLBACK_URL, 'http') === 0;
        $providerChecks[] = ['PesaPal Consumer Key',    $ck  ? 'Configured' : 'Not set', $ck  ? 'ok' : 'warning', $ck  ? 'Set' : 'Not Set'];
        $providerChecks[] = ['PesaPal Consumer Secret', $cs  ? 'Configured' : 'Not set', $cs  ? 'ok' : 'warning', $cs  ? 'Set' : 'Not Set'];
        $providerChecks[] = ['PesaPal IPN URL',         $ipn ? (PESAPAL_IPN_URL) : 'Not set — must point to callback_pesapal.php', $ipn ? 'ok' : 'warning', $ipn ? 'Set' : 'Not Set'];
        $providerChecks[] = ['PesaPal Callback URL',    $cb  ? (PESAPAL_CALLBACK_URL) : 'Not set', $cb  ? 'ok' : 'warning', $cb  ? 'Set' : 'Not Set'];
        $env = defined('PESAPAL_ENV') ? PESAPAL_ENV : 'sandbox';
        $providerChecks[] = ['PesaPal Environment', 'Currently: ' . $env, 'ok', strtoupper($env)];
        break;

    case 'flutterwave':
        $sk = defined('FLW_SECRET_KEY')   && strlen(FLW_SECRET_KEY)   > 10 && FLW_SECRET_KEY !== 'YOUR_FLW_SECRET_KEY';
        $pk = defined('FLW_PUBLIC_KEY')   && strlen(FLW_PUBLIC_KEY)   > 10 && FLW_PUBLIC_KEY !== 'YOUR_FLW_PUBLIC_KEY';
        $ru = defined('FLW_REDIRECT_URL') && strpos(FLW_REDIRECT_URL, 'http') === 0;
        $sh = defined('FLW_SECRET_HASH')  && strlen(FLW_SECRET_HASH)  > 5;
        $providerChecks[] = ['Flutterwave Secret Key',   $sk ? 'Configured' : 'Not set or placeholder', $sk ? 'ok' : 'warning', $sk ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Flutterwave Public Key',   $pk ? 'Configured' : 'Not set or placeholder', $pk ? 'ok' : 'warning', $pk ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Flutterwave Redirect URL', $ru ? (FLW_REDIRECT_URL) : 'Not set', $ru ? 'ok' : 'warning', $ru ? 'Set' : 'Not Set'];
        $providerChecks[] = ['Webhook Secret Hash',      $sh ? 'Configured — webhooks will be verified' : 'Not set — webhook signature verification disabled', $sh ? 'ok' : 'warning', $sh ? 'Set' : 'Not Set'];
        break;

    default:
        $providerChecks[] = ['Provider', 'Unknown provider "' . htmlspecialchars($activeProvider) . '" — check PAYMENT_PROVIDER in config.php', 'error', 'Unknown'];
}

foreach ($providerChecks as [$title, $detail, $status, $badge]) {
    addCheck($checks, $title, $detail, $status, $badge);
    degradeOverall($overall, $status);
}

// ---- Shared checks ----
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
    'ok'      => ['All Systems Operational', 'Your payment integration is ready to accept payments.'],
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

    <div class="provider-banner">
        <span class="provider-icon"><?= $providerLabel[2] ?></span>
        <div class="provider-info">
            <strong>Active Provider: <?= htmlspecialchars($providerLabel[0]) ?></strong>
            <span><?= $providerLabel[1] === 'stk' ? 'STK Push — sends a PIN prompt directly to the customer\'s phone' : 'Hosted Checkout — redirects the customer to a payment page' ?></span>
        </div>
        <span class="provider-badge"><?= htmlspecialchars($activeProvider) ?></span>
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
