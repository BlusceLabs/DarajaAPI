<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhook Simulator — M-Pesa Dev Tool</title>
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
        .layout {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 640px) { .layout { grid-template-columns: 1fr; } }

        .page-header {
            max-width: 900px;
            margin: 0 auto 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #006633; }
        .page-header p  { font-size: 13px; color: #888; margin-top: 4px; }
        .dev-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff3cd; color: #856404; border: 1px solid #ffc107;
            border-radius: 6px; padding: 5px 12px; font-size: 12px; font-weight: 700;
        }
        .nav-links { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-link {
            font-size: 13px; color: #006633; text-decoration: none;
            display: flex; align-items: center; gap: 5px; font-weight: 600;
            padding: 7px 14px; border: 1.5px solid #c8e6d4; border-radius: 8px;
            transition: background 0.15s;
        }
        .nav-link:hover { background: #f0faf5; }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .card-header h2 { font-size: 14px; font-weight: 700; }
        .card-header span { font-size: 11px; color: #aaa; }
        .card-body { padding: 20px; }

        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #555; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
        input, select {
            width: 100%; padding: 10px 12px; border: 1.5px solid #e0e6e0;
            border-radius: 8px; font-size: 14px; color: #222; background: #fafafa;
            transition: border-color 0.2s;
        }
        input:focus, select:focus { outline: none; border-color: #00a651; background: #fff; }

        .scenario-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px;
        }
        .scenario-btn {
            padding: 9px 10px; border: 1.5px solid #e0e0e0; border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; text-align: center;
            background: #fafafa; color: #555; transition: all 0.15s;
        }
        .scenario-btn:hover { border-color: #00a651; color: #006633; background: #f0faf5; }
        .scenario-btn.active { border-color: #006633; background: #006633; color: #fff; }

        .send-btn {
            width: 100%; padding: 13px; background: linear-gradient(135deg, #006633, #00a651);
            color: #fff; border: none; border-radius: 10px; font-size: 14px;
            font-weight: 700; cursor: pointer; transition: opacity 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .send-btn:hover { opacity: 0.9; }
        .send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .spinner {
            width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Response log */
        .log-wrap { display: flex; flex-direction: column; gap: 10px; }
        .log-entry {
            border-radius: 10px; overflow: hidden;
            border: 1px solid #e8e8e8; font-size: 12px;
        }
        .log-entry-header {
            padding: 8px 12px;
            display: flex; align-items: center; justify-content: space-between;
            font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .log-entry-header.sent { background: #e8f4fd; color: #1a5f8b; }
        .log-entry-header.received { background: #e8f8ef; color: #1a6b3a; }
        .log-entry-header.error { background: #fdf2f2; color: #8b2020; }
        .log-entry-body {
            padding: 12px; background: #fafafa;
            font-family: monospace; font-size: 12px;
            white-space: pre-wrap; word-break: break-all;
            color: #333; max-height: 200px; overflow-y: auto;
        }
        .log-time { font-weight: 400; opacity: 0.7; font-size: 10px; }
        .empty-log {
            text-align: center; padding: 40px 20px;
            color: #bbb; font-size: 13px;
        }
        .empty-log svg { width: 36px; height: 36px; margin-bottom: 10px; opacity: 0.3; display: block; margin: 0 auto 10px; }

        .clear-btn {
            font-size: 11px; color: #aaa; background: none; border: none; cursor: pointer;
            padding: 0; text-decoration: underline;
        }
        .clear-btn:hover { color: #c0392b; }
    </style>
</head>
<body>

<div class="page-header">
    <div>
        <h1>Webhook Simulator</h1>
        <p>Send test M-Pesa callbacks to <code>callback.php</code> without real transactions</p>
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

    <!-- LEFT: Form -->
    <div class="card">
        <div class="card-header">
            <h2>Callback Payload</h2>
            <span>Sent to callback.php</span>
        </div>
        <div class="card-body">
            <p style="font-size:12px;color:#888;margin-bottom:16px">Choose a preset scenario or fill in the fields manually.</p>

            <div class="scenario-grid">
                <button class="scenario-btn active" data-scenario="success" onclick="loadScenario('success', this)">✅ Successful Payment</button>
                <button class="scenario-btn" data-scenario="cancelled" onclick="loadScenario('cancelled', this)">❌ User Cancelled</button>
                <button class="scenario-btn" data-scenario="insufficient" onclick="loadScenario('insufficient', this)">💸 Insufficient Funds</button>
                <button class="scenario-btn" data-scenario="wrong_pin" onclick="loadScenario('wrong_pin', this)">🔒 Wrong PIN</button>
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

            <button class="send-btn" id="sendBtn" onclick="sendCallback()">
                Send Callback
            </button>
        </div>
    </div>

    <!-- RIGHT: Log -->
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

<script>
    const scenarios = {
        success:      { code: '0',    desc: 'The service request is processed successfully.' },
        cancelled:    { code: '1032', desc: 'Request cancelled by user.' },
        insufficient: { code: '1',    desc: 'The balance is insufficient for the transaction.' },
        wrong_pin:    { code: '2001', desc: 'The initiator information is invalid.' },
    };

    function loadScenario(key, btn) {
        document.querySelectorAll('.scenario-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const s = scenarios[key];
        document.getElementById('simResultCode').value = s.code;
        document.getElementById('simResultDesc').value = s.desc;
        // For non-success scenarios, clear the amount field visual indicator
    }

    function syncResultDesc() {
        const code = document.getElementById('simResultCode').value;
        const map = { '0': scenarios.success.desc, '1': scenarios.insufficient.desc, '1032': scenarios.cancelled.desc, '1037': 'DS Timeout - User cannot be reached.', '2001': scenarios.wrong_pin.desc };
        if (map[code]) document.getElementById('simResultDesc').value = map[code];
    }

    function buildPayload() {
        const phone  = document.getElementById('simPhone').value.trim();
        const amount = parseFloat(document.getElementById('simAmount').value) || 0;
        const rc     = parseInt(document.getElementById('simResultCode').value);
        const desc   = document.getElementById('simResultDesc').value.trim();
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
                    { Name: 'Amount',              Value: amount },
                    { Name: 'MpesaReceiptNumber',  Value: receipt },
                    { Name: 'TransactionDate',     Value: parseInt(ts) },
                    { Name: 'PhoneNumber',         Value: parseInt(phone) },
                ]
            };
        }

        return payload;
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
        const wrap = document.getElementById('logWrap');
        wrap.innerHTML = '<div class="empty-log" id="emptyLog"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Send a callback to see the request and response here.</div>';
    }

    async function sendCallback() {
        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Sending…';

        const payload = buildPayload();
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

        btn.disabled = false;
        btn.innerHTML = 'Send Callback';
    }

    // Load default scenario on page load
    loadScenario('success', document.querySelector('[data-scenario="success"]'));
</script>
</body>
</html>
