<?php
/**
 * webhook_test.php — Dev-only webhook simulator
 *
 * Supports three providers:
 *  - TinyPesa / Daraja  → POST to callback.php
 *  - PesaPal            → GET  to callback_pesapal.php
 *  - Flutterwave        → POST to callback_flutterwave.php (via server-side proxy
 *                         so FLW_SECRET_HASH is never sent to the browser)
 */

// ── Server-side action: proxy Flutterwave webhook ─────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'flw_sim') {
    header('Content-Type: application/json');

    if (file_exists(__DIR__ . '/config.php')) {
        @include_once __DIR__ . '/config.php';
    }

    $raw = file_get_contents('php://input');
    if (!$raw) {
        http_response_code(400);
        echo json_encode(['error' => 'No payload']);
        exit;
    }

    $hash = defined('FLW_SECRET_HASH') ? FLW_SECRET_HASH : '';

    // Build the local URL for callback_flutterwave.php
    $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host        = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $port        = $_SERVER['SERVER_PORT'] ?? '80';
    $callbackUrl = $scheme . '://' . $host . ':' . $port . '/callback_flutterwave.php';

    $curlHeaders = ['Content-Type: application/json'];
    if ($hash) {
        $curlHeaders[] = 'verif-hash: ' . $hash;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $callbackUrl,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $raw,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => $curlHeaders,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        http_response_code(500);
        echo json_encode(['error' => 'cURL error: ' . $curlErr]);
    } else {
        http_response_code($httpCode);
        echo $response;
    }
    exit;
}

// Detect if FLW_SECRET_HASH is configured (boolean only — never send value to browser)
$flwHashConfigured = false;
if (file_exists(__DIR__ . '/config.php')) {
    @include_once __DIR__ . '/config.php';
    $flwHashConfigured = defined('FLW_SECRET_HASH') && FLW_SECRET_HASH !== '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhook Simulator — M-Pesa Dev Tool</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <style>
        :root {
            --bg: #f0f4f0;
            --surface: #fff;
            --surface-alt: #fafafa;
            --border: #e0e6e0;
            --text: #222;
            --text-2: #555;
            --text-3: #888;
            --text-4: #aaa;
            --shadow: rgba(0,0,0,0.08);
            --nav-border: #c8e6d4;
            --nav-hover: #f0faf5;
            --scenario-bg: #fafafa;
            --scenario-text: #555;
            --scenario-hover-bg: #f0faf5;
            --log-alt: #fafafa;
            --log-sent-bg: #e8f4fd;    --log-sent-text: #1a5f8b;
            --log-recv-bg: #e8f8ef;    --log-recv-text: #1a6b3a;
            --log-err-bg: #fdf2f2;     --log-err-text: #8b2020;
            --log-body-bg: #fafafa;    --log-body-text: #333;
        }
        :root[data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-alt: #0d1117;
            --border: #30363d;
            --text: #e6edf3;
            --text-2: #adbac7;
            --text-3: #768390;
            --text-4: #636e7b;
            --shadow: rgba(0,0,0,0.40);
            --nav-border: #1a4731;
            --nav-hover: #0f2a1a;
            --scenario-bg: #1c2128;
            --scenario-text: #adbac7;
            --scenario-hover-bg: #0f2a1a;
            --log-alt: #0d1117;
            --log-sent-bg: #0a1a2a;    --log-sent-text: #58a6ff;
            --log-recv-bg: #0f2a1a;    --log-recv-text: #56d364;
            --log-err-bg: #2d1111;     --log-err-text: #f97171;
            --log-body-bg: #0d1117;    --log-body-text: #e6edf3;
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
        .layout {
            max-width: 900px; margin: 0 auto;
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;
        }
        @media (max-width: 640px) { .layout { grid-template-columns: 1fr; } }

        .page-header {
            max-width: 900px; margin: 0 auto 24px;
            display: flex; align-items: flex-start; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #006633; }
        :root[data-theme="dark"] .page-header h1 { color: #3fb950; }
        .page-header p  { font-size: 13px; color: var(--text-3); margin-top: 4px; }
        .dev-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff3cd; color: #856404;
            border: 1px solid #ffc107; border-radius: 6px;
            padding: 5px 12px; font-size: 12px; font-weight: 700;
        }
        :root[data-theme="dark"] .dev-badge {
            background: #2a2000; color: #e3b341; border-color: #5a4000;
        }
        .nav-links { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-link {
            font-size: 13px; color: #006633; text-decoration: none;
            display: flex; align-items: center; gap: 5px; font-weight: 600;
            padding: 7px 14px; border: 1.5px solid var(--nav-border); border-radius: 8px;
            transition: background 0.15s;
        }
        :root[data-theme="dark"] .nav-link { color: #3fb950; }
        .nav-link:hover { background: var(--nav-hover); }

        .card {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 2px 16px var(--shadow);
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
        }
        .card-header h2 { font-size: 14px; font-weight: 700; }
        .card-header span { font-size: 11px; color: var(--text-4); }
        .card-body { padding: 20px; }

        /* Provider tabs */
        .provider-tabs {
            display: flex; gap: 0; margin-bottom: 18px;
            border: 1.5px solid var(--border); border-radius: 10px; overflow: hidden;
        }
        .provider-tab {
            flex: 1; padding: 8px 6px; font-size: 12px; font-weight: 700;
            border: none; background: var(--surface-alt); color: var(--text-2);
            cursor: pointer; transition: all 0.15s; text-align: center;
            border-right: 1px solid var(--border);
        }
        .provider-tab:last-child { border-right: none; }
        .provider-tab.active { background: #006633; color: #fff; }
        .provider-tab:not(.active):hover { background: var(--nav-hover); color: #006633; }
        :root[data-theme="dark"] .provider-tab:not(.active):hover { color: #3fb950; }

        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 12px; font-weight: 700; color: var(--text-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
        input, select {
            width: 100%; padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 14px;
            color: var(--text); background: var(--surface-alt);
            transition: border-color 0.2s, background 0.2s;
        }
        input:focus, select:focus { outline: none; border-color: #00a651; background: var(--surface); }

        .scenario-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
        .scenario-btn {
            padding: 9px 10px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; text-align: center;
            background: var(--scenario-bg); color: var(--scenario-text);
            transition: all 0.15s;
        }
        .scenario-btn:hover { border-color: #00a651; color: #006633; background: var(--nav-hover); }
        :root[data-theme="dark"] .scenario-btn:hover { color: #3fb950; }
        .scenario-btn.active { border-color: #006633; background: #006633; color: #fff; }

        .send-btn {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #006633, #00a651);
            color: #fff; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            transition: opacity 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .send-btn:hover { opacity: 0.9; }
        .send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .spinner {
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff; border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .log-wrap { display: flex; flex-direction: column; gap: 10px; }
        .log-entry { border-radius: 10px; overflow: hidden; border: 1px solid var(--border); font-size: 12px; }
        .log-entry-header {
            padding: 8px 12px;
            display: flex; align-items: center; justify-content: space-between;
            font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .log-entry-header.sent     { background: var(--log-sent-bg); color: var(--log-sent-text); }
        .log-entry-header.received { background: var(--log-recv-bg); color: var(--log-recv-text); }
        .log-entry-header.error    { background: var(--log-err-bg);  color: var(--log-err-text); }
        .log-entry-body {
            padding: 12px; background: var(--log-body-bg);
            font-family: monospace; font-size: 12px;
            white-space: pre-wrap; word-break: break-all;
            color: var(--log-body-text); max-height: 200px; overflow-y: auto;
        }
        .log-time { font-weight: 400; opacity: 0.7; font-size: 10px; }
        .empty-log {
            text-align: center; padding: 40px 20px;
            color: var(--text-4); font-size: 13px;
        }
        .empty-log svg { width: 36px; height: 36px; margin-bottom: 10px; opacity: 0.3; display: block; margin: 0 auto 10px; }

        .clear-btn {
            font-size: 11px; color: var(--text-4); background: none; border: none;
            cursor: pointer; padding: 0; text-decoration: underline;
        }
        .clear-btn:hover { color: #c0392b; }

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

        .provider-section { display: none; }
        .provider-section.active { display: block; }

        .info-note {
            font-size: 11px; color: var(--text-3);
            background: var(--surface-alt); border: 1px solid var(--border);
            border-radius: 6px; padding: 8px 10px; margin-bottom: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="page-header">
    <div>
        <h1>Webhook Simulator</h1>
        <p>Send test payment callbacks without real transactions</p>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
        <span class="dev-badge">⚠ Dev Tool — Remove in Production</span>
        <div class="nav-links">
            <a href="/admin.php" class="nav-link">Transaction Log</a>
            <a href="/" class="nav-link">&#8592; Payment Page</a>
        </div>
    </div>
</div>

<div class="layout">

    <div class="card">
        <div class="card-header">
            <h2>Callback Payload</h2>
            <span id="cardTargetLabel">Sent to callback.php</span>
        </div>
        <div class="card-body">

            <!-- Provider selector -->
            <div class="provider-tabs">
                <button class="provider-tab active" data-provider="daraja"      onclick="switchProvider('daraja', this)">TinyPesa / Daraja</button>
                <button class="provider-tab"        data-provider="pesapal"     onclick="switchProvider('pesapal', this)">PesaPal</button>
                <button class="provider-tab"        data-provider="flutterwave" onclick="switchProvider('flutterwave', this)">Flutterwave</button>
            </div>

            <!-- ── Daraja / TinyPesa ── -->
            <div class="provider-section active" id="section-daraja">
                <p style="font-size:12px;color:var(--text-3);margin-bottom:16px">Choose a preset scenario or fill in the fields manually.</p>

                <div class="scenario-grid">
                    <button class="scenario-btn active" data-scenario="daraja-success"      onclick="loadDarajaScenario('success', this)">✅ Successful Payment</button>
                    <button class="scenario-btn"        data-scenario="daraja-cancelled"    onclick="loadDarajaScenario('cancelled', this)">❌ User Cancelled</button>
                    <button class="scenario-btn"        data-scenario="daraja-insufficient" onclick="loadDarajaScenario('insufficient', this)">💸 Insufficient Funds</button>
                    <button class="scenario-btn"        data-scenario="daraja-wrong_pin"    onclick="loadDarajaScenario('wrong_pin', this)">🔒 Wrong PIN</button>
                </div>

                <div class="form-group">
                    <label for="simPhone">Phone Number</label>
                    <input type="tel" id="simPhone" value="254712345678" placeholder="254XXXXXXXXX">
                </div>
                <div class="form-group">
                    <label for="simAmount">Amount (KES)</label>
                    <input type="number" id="simAmount" value="500" min="1">
                </div>
                <div class="form-group">
                    <label for="simResultCode">Result Code</label>
                    <select id="simResultCode" onchange="syncResultDesc()">
                        <option value="0">0 — Success</option>
                        <option value="1">1 — Insufficient Funds</option>
                        <option value="1032">1032 — Request Cancelled by User</option>
                        <option value="1037">1037 — DS Timeout User Cannot Be Reached</option>
                        <option value="2001">2001 — Wrong PIN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="simResultDesc">Result Description</label>
                    <input type="text" id="simResultDesc" value="The service request is processed successfully.">
                </div>
            </div>

            <!-- ── PesaPal ── -->
            <div class="provider-section" id="section-pesapal">
                <div class="info-note">PesaPal IPN uses a GET request. The callback queries PesaPal's API for the real transaction status — results depend on your sandbox credentials and whether the tracking ID exists.</div>

                <div class="scenario-grid">
                    <button class="scenario-btn active" data-scenario="pesapal-success" onclick="loadPesapalScenario('success', this)">✅ Successful IPN</button>
                    <button class="scenario-btn"        data-scenario="pesapal-failed"  onclick="loadPesapalScenario('failed', this)">❌ Failed IPN</button>
                </div>

                <div class="form-group">
                    <label for="ppOrderTrackingId">Order Tracking ID</label>
                    <input type="text" id="ppOrderTrackingId" placeholder="e.g. b03e4a56-1234-4567-abcd-ef0123456789">
                </div>
                <div class="form-group">
                    <label for="ppMerchantReference">Order Merchant Reference</label>
                    <input type="text" id="ppMerchantReference" placeholder="e.g. REF-20260502-001">
                </div>
            </div>

            <!-- ── Flutterwave ── -->
            <div class="provider-section" id="section-flutterwave">
                <div class="info-note">Sends a <code>charge.completed</code> webhook to <code>callback_flutterwave.php</code> via a server-side proxy that appends the <code>verif-hash</code> header using your configured <code>FLW_SECRET_HASH</code>.<?php if (!$flwHashConfigured): ?> <strong>Note:</strong> FLW_SECRET_HASH is not configured — signature verification will be skipped.<?php endif; ?></div>

                <div class="scenario-grid">
                    <button class="scenario-btn active" data-scenario="flw-success" onclick="loadFlwScenario('success', this)">✅ charge.completed (Success)</button>
                    <button class="scenario-btn"        data-scenario="flw-failed"  onclick="loadFlwScenario('failed', this)">❌ charge.completed (Failed)</button>
                </div>

                <div class="form-group">
                    <label for="flwPhone">Phone Number</label>
                    <input type="tel" id="flwPhone" value="254712345678" placeholder="254XXXXXXXXX">
                </div>
                <div class="form-group">
                    <label for="flwAmount">Amount (KES)</label>
                    <input type="number" id="flwAmount" value="500" min="1">
                </div>
                <div class="form-group">
                    <label for="flwTxRef">Transaction Reference (tx_ref)</label>
                    <input type="text" id="flwTxRef" placeholder="e.g. SIM-REF-1746000000">
                </div>
                <div class="form-group">
                    <label for="flwStatus">Payment Status</label>
                    <select id="flwStatus" onchange="syncFlwStatus()">
                        <option value="successful">successful</option>
                        <option value="failed">failed</option>
                    </select>
                </div>
            </div>

            <button class="send-btn" id="sendBtn" onclick="sendCallback()">Send Callback</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Response Log</h2>
            <button class="clear-btn" onclick="clearLog()">Clear</button>
        </div>
        <div class="card-body">
            <div class="log-wrap" id="logWrap">
                <div class="empty-log" id="emptyLog">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Send a callback to see the request and response here.
                </div>
            </div>
        </div>
    </div>

</div>

<button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle dark mode"></button>

<script>
    // FLW_SECRET_HASH is never sent to the browser.
    // The server-side proxy (?action=flw_sim) appends it privately.
    const FLW_HASH_CONFIGURED = <?php echo $flwHashConfigured ? 'true' : 'false'; ?>;

    // ── Theme ──────────────────────────────────────────────────────────────────
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

    // ── Provider switching ─────────────────────────────────────────────────────
    let currentProvider = 'daraja';
    const targetLabels = {
        daraja:      'Sent to callback.php',
        pesapal:     'Sent to callback_pesapal.php',
        flutterwave: 'Sent via server proxy → callback_flutterwave.php',
    };

    function switchProvider(provider, btn) {
        document.querySelectorAll('.provider-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.provider-section').forEach(s => s.classList.remove('active'));
        document.getElementById('section-' + provider).classList.add('active');
        document.getElementById('cardTargetLabel').textContent = targetLabels[provider];
        currentProvider = provider;
    }

    // ── Daraja scenarios ───────────────────────────────────────────────────────
    const darajaScenarios = {
        success:      { code: '0',    desc: 'The service request is processed successfully.' },
        cancelled:    { code: '1032', desc: 'Request cancelled by user.' },
        insufficient: { code: '1',    desc: 'The balance is insufficient for the transaction.' },
        wrong_pin:    { code: '2001', desc: 'The initiator information is invalid.' },
    };

    function loadDarajaScenario(key, btn) {
        document.querySelectorAll('#section-daraja .scenario-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const s = darajaScenarios[key];
        document.getElementById('simResultCode').value = s.code;
        document.getElementById('simResultDesc').value = s.desc;
    }

    function syncResultDesc() {
        const code = document.getElementById('simResultCode').value;
        const map = {
            '0':    darajaScenarios.success.desc,
            '1':    darajaScenarios.insufficient.desc,
            '1032': darajaScenarios.cancelled.desc,
            '1037': 'DS Timeout - User cannot be reached.',
            '2001': darajaScenarios.wrong_pin.desc,
        };
        if (map[code]) document.getElementById('simResultDesc').value = map[code];
    }

    function buildDarajaPayload() {
        const phone   = document.getElementById('simPhone').value.trim();
        const amount  = parseFloat(document.getElementById('simAmount').value) || 0;
        const rc      = parseInt(document.getElementById('simResultCode').value);
        const desc    = document.getElementById('simResultDesc').value.trim();
        const receipt = 'TEST' + Math.random().toString(36).substr(2, 8).toUpperCase();
        const ts      = new Date().toISOString().replace(/[-T:.Z]/g, '').substr(0, 14);
        const merchantId = 'TEST-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        const checkoutId = 'ws_CO_' + ts + Math.floor(Math.random() * 9999);

        const payload = {
            Body: {
                stkCallback: {
                    MerchantRequestID: merchantId,
                    CheckoutRequestID: checkoutId,
                    ResultCode: rc,
                    ResultDesc: desc,
                }
            }
        };

        if (rc === 0) {
            payload.Body.stkCallback.CallbackMetadata = {
                Item: [
                    { Name: 'Amount',             Value: amount },
                    { Name: 'MpesaReceiptNumber', Value: receipt },
                    { Name: 'TransactionDate',    Value: parseInt(ts) },
                    { Name: 'PhoneNumber',        Value: parseInt(phone) },
                ]
            };
        }

        return payload;
    }

    // ── PesaPal scenarios ──────────────────────────────────────────────────────
    // PesaPal IPN is a GET notification: OrderTrackingId + OrderMerchantReference.
    // "success" and "failed" scenarios use distinguishable ID prefixes and
    // reference patterns so developers can identify which scenario was triggered.
    const pesapalScenarios = {
        success: {
            trackingPrefix: 'SIM-PASS',
            refPrefix:      'SIM-OK',
            label:          'Successful IPN',
        },
        failed: {
            trackingPrefix: 'SIM-FAIL',
            refPrefix:      'SIM-ERR',
            label:          'Failed IPN',
        },
    };

    let currentPesapalScenario = 'success';

    function loadPesapalScenario(key, btn) {
        document.querySelectorAll('#section-pesapal .scenario-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentPesapalScenario = key;

        const cfg = pesapalScenarios[key];
        const uid = randomUUID();
        document.getElementById('ppOrderTrackingId').value    = cfg.trackingPrefix + '-' + uid;
        document.getElementById('ppMerchantReference').value  = cfg.refPrefix + '-' + Date.now();
    }

    function buildPesapalParams() {
        return {
            OrderTrackingId:        document.getElementById('ppOrderTrackingId').value.trim()  || (pesapalScenarios[currentPesapalScenario].trackingPrefix + '-' + randomUUID()),
            OrderMerchantReference: document.getElementById('ppMerchantReference').value.trim() || (pesapalScenarios[currentPesapalScenario].refPrefix + '-' + Date.now()),
        };
    }

    // ── Flutterwave scenarios ──────────────────────────────────────────────────
    let currentFlwScenario = 'success';

    function loadFlwScenario(key, btn) {
        document.querySelectorAll('#section-flutterwave .scenario-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFlwScenario = key;
        document.getElementById('flwTxRef').value  = 'SIM-REF-' + Date.now();
        document.getElementById('flwStatus').value = key === 'success' ? 'successful' : 'failed';
    }

    function syncFlwStatus() {
        const status = document.getElementById('flwStatus').value;
        const successBtn = document.querySelector('#section-flutterwave [data-scenario="flw-success"]');
        const failedBtn  = document.querySelector('#section-flutterwave [data-scenario="flw-failed"]');
        document.querySelectorAll('#section-flutterwave .scenario-btn').forEach(b => b.classList.remove('active'));
        if (status === 'successful') { successBtn.classList.add('active'); currentFlwScenario = 'success'; }
        else                         { failedBtn.classList.add('active');  currentFlwScenario = 'failed'; }
    }

    function buildFlwPayload() {
        const phone   = document.getElementById('flwPhone').value.trim()  || '254712345678';
        const amount  = parseFloat(document.getElementById('flwAmount').value) || 0;
        const txRef   = document.getElementById('flwTxRef').value.trim()  || ('SIM-REF-' + Date.now());
        const status  = document.getElementById('flwStatus').value;
        const flwId   = Math.floor(Math.random() * 9000000) + 1000000;
        const flwRef  = 'FLW-SIM-' + Math.random().toString(36).substr(2, 10).toUpperCase();
        const now     = new Date().toISOString();

        return {
            event: 'charge.completed',
            data: {
                id:                 flwId,
                uid:                'UID-' + flwId,
                tx_ref:             txRef,
                flw_ref:            flwRef,
                device_fingerprint: 'sim',
                amount:             amount,
                currency:           'KES',
                charged_amount:     amount,
                app_fee:            parseFloat((amount * 0.014).toFixed(2)),
                merchant_fee:       0,
                processor_response: status === 'successful' ? 'Success' : 'Payment failed',
                auth_model:         'MOBILEMONEY',
                ip:                 '::ffff:127.0.0.1',
                narration:          'Simulated payment',
                status:             status,
                payment_type:       'mobilemoney',
                created_at:         now,
                account_id:         12345,
                customer: {
                    id:           67890,
                    name:         'Test Customer',
                    phone_number: phone,
                    email:        'test@example.com',
                    created_at:   now,
                },
                meta: { origins: ['sim'] },
            }
        };
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    function randomUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    function addLogEntry(type, title, content) {
        const emptyLog = document.getElementById('emptyLog');
        if (emptyLog) emptyLog.remove();

        const now = new Date().toLocaleTimeString();
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        entry.innerHTML = `
            <div class="log-entry-header ${type}">
                <span>${title}</span>
                <span class="log-time">${now}</span>
            </div>
            <div class="log-entry-body">${escHtml(typeof content === 'object' ? JSON.stringify(content, null, 2) : content)}</div>
        `;
        document.getElementById('logWrap').prepend(entry);
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function clearLog() {
        document.getElementById('logWrap').innerHTML = '<div class="empty-log" id="emptyLog"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Send a callback to see the request and response here.</div>';
    }

    // ── Main send ──────────────────────────────────────────────────────────────
    async function sendCallback() {
        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Sending…';

        try {
            if (currentProvider === 'daraja') {
                await sendDarajaCallback();
            } else if (currentProvider === 'pesapal') {
                await sendPesapalCallback();
            } else if (currentProvider === 'flutterwave') {
                await sendFlutterwaveCallback();
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Send Callback';
        }
    }

    async function sendDarajaCallback() {
        const payload = buildDarajaPayload();
        addLogEntry('sent', '⬆ Sending to callback.php', payload);

        try {
            const res  = await fetch('/callback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const text = await res.text();
            let parsed;
            try { parsed = JSON.parse(text); } catch { parsed = text; }
            addLogEntry('received', '⬇ Response from callback.php (HTTP ' + res.status + ')', parsed);
        } catch (err) {
            addLogEntry('error', '✗ Request Failed', err.message);
        }
    }

    async function sendPesapalCallback() {
        const params  = buildPesapalParams();
        const scenLbl = pesapalScenarios[currentPesapalScenario].label;
        const qs      = new URLSearchParams(params).toString();
        const url     = '/callback_pesapal.php?' + qs;

        addLogEntry('sent',
            '⬆ ' + scenLbl + ' — GET to callback_pesapal.php',
            'Scenario: ' + scenLbl + '\n\nGET params:\n' + JSON.stringify(params, null, 2)
        );

        try {
            const res  = await fetch(url, { method: 'GET' });
            const text = await res.text();
            let parsed;
            try { parsed = JSON.parse(text); } catch { parsed = text; }
            addLogEntry('received', '⬇ Response from callback_pesapal.php (HTTP ' + res.status + ')', parsed);
        } catch (err) {
            addLogEntry('error', '✗ Request Failed', err.message);
        }
    }

    async function sendFlutterwaveCallback() {
        const payload = buildFlwPayload();
        const scenLbl = currentFlwScenario === 'success' ? 'charge.completed (Success)' : 'charge.completed (Failed)';
        const hashNote = FLW_HASH_CONFIGURED
            ? 'verif-hash: [added server-side from FLW_SECRET_HASH]'
            : 'verif-hash: (not set — FLW_SECRET_HASH not configured)';

        addLogEntry('sent',
            '⬆ ' + scenLbl + ' — POST via server proxy',
            'Scenario: ' + scenLbl + '\n' + hashNote + '\n\n' + JSON.stringify(payload, null, 2)
        );

        try {
            // POST to the server-side proxy which adds verif-hash privately
            const res  = await fetch('/webhook_test.php?action=flw_sim', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const text = await res.text();
            let parsed;
            try { parsed = JSON.parse(text); } catch { parsed = text; }
            addLogEntry('received', '⬇ Response from callback_flutterwave.php (HTTP ' + res.status + ')', parsed);
        } catch (err) {
            addLogEntry('error', '✗ Request Failed', err.message);
        }
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    loadDarajaScenario('success', document.querySelector('[data-scenario="daraja-success"]'));
    loadPesapalScenario('success', document.querySelector('[data-scenario="pesapal-success"]'));
    loadFlwScenario('success', document.querySelector('[data-scenario="flw-success"]'));
</script>
</body>
</html>
