<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lipa Na M-Pesa</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.10);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #006633 0%, #00a651 100%);
            padding: 32px 32px 28px;
            text-align: center;
        }

        .mpesa-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .mpesa-logo svg {
            width: 38px;
            height: 38px;
        }

        .card-header h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .card-header p {
            color: rgba(255,255,255,0.82);
            font-size: 14px;
            margin-top: 4px;
        }

        .card-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 7px;
            letter-spacing: 0.3px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap span {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 14px;
            pointer-events: none;
        }

        input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 1.5px solid #dde2dd;
            border-radius: 10px;
            font-size: 15px;
            color: #222;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        input:focus {
            outline: none;
            border-color: #00a651;
            box-shadow: 0 0 0 3px rgba(0,166,81,0.12);
            background: #fff;
        }

        .hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #006633, #00a651);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: opacity 0.2s, transform 0.1s;
            margin-top: 4px;
        }

        .btn:hover:not(:disabled) { opacity: 0.92; transform: translateY(-1px); }
        .btn:active:not(:disabled) { transform: translateY(0); }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2.5px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .message {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 14px;
            display: none;
            align-items: flex-start;
            gap: 10px;
        }

        .message.show { display: flex; }

        .message.success {
            background: #e8f8ef;
            color: #1a6b3a;
            border: 1px solid #b3e6c8;
        }

        .message.error {
            background: #fdf2f2;
            color: #8b2020;
            border: 1px solid #f5c0c0;
        }

        .message.waiting {
            background: #fff8e6;
            color: #7a5a00;
            border: 1px solid #f0d98a;
        }

        .message .icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
        .message .text { line-height: 1.5; }
        .message .text strong { display: block; margin-bottom: 2px; }

        .status-check {
            display: none;
            margin-top: 16px;
            text-align: center;
        }

        .status-check.show { display: block; }

        .progress-bar {
            height: 4px;
            background: #e8e8e8;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #006633, #00a651);
            border-radius: 2px;
            animation: progress 30s linear forwards;
        }

        @keyframes progress { from { width: 0% } to { width: 100% } }

        .status-label {
            font-size: 12px;
            color: #888;
        }

        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 24px 0 20px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #999;
            justify-content: center;
        }

        .info-row svg { width: 14px; height: 14px; flex-shrink: 0; }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <div class="mpesa-logo">
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="20" fill="rgba(255,255,255,0.15)"/>
                <text x="20" y="26" text-anchor="middle" font-size="18" font-weight="bold" fill="white" font-family="Arial">M</text>
            </svg>
            <h1>Lipa Na M-Pesa</h1>
        </div>
        <p>STK Push &mdash; Secure Mobile Payment</p>
    </div>

    <div class="card-body">
        <form id="paymentForm" novalidate>
            <div class="form-group">
                <label for="phone">Safaricom Phone Number</label>
                <div class="input-wrap">
                    <span>📱</span>
                    <input type="tel" id="phone" placeholder="0712 345 678" autocomplete="tel" required>
                </div>
                <p class="hint">Formats accepted: 07XX, 01XX, or +254XXX</p>
            </div>

            <div class="form-group">
                <label for="amount">Amount (KES)</label>
                <div class="input-wrap">
                    <span>KSh</span>
                    <input type="number" id="amount" placeholder="e.g. 500" min="1" max="150000" required>
                </div>
                <p class="hint">Minimum: KES 1 &nbsp;&bull;&nbsp; Maximum: KES 150,000</p>
            </div>

            <button type="submit" class="btn" id="submitBtn">
                Pay Now
            </button>
        </form>

        <div id="message" class="message">
            <span class="icon" id="msgIcon"></span>
            <span class="text" id="msgText"></span>
        </div>

        <div id="statusCheck" class="status-check">
            <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
            <p class="status-label" id="statusLabel">Waiting for payment confirmation…</p>
        </div>

        <hr class="divider">

        <div class="info-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Payments secured by Safaricom Daraja API
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('paymentForm');
    const phoneInput = document.getElementById('phone');
    const amountInput = document.getElementById('amount');
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('message');
    const msgIcon = document.getElementById('msgIcon');
    const msgText = document.getElementById('msgText');
    const statusCheck = document.getElementById('statusCheck');
    const statusLabel = document.getElementById('statusLabel');

    let pollInterval = null;

    function showMessage(type, title, body) {
        const icons = { success: '✅', error: '❌', waiting: '⏳' };
        messageDiv.className = 'message show ' + type;
        msgIcon.textContent = icons[type] || 'ℹ️';
        msgText.innerHTML = (title ? '<strong>' + title + '</strong>' : '') + (body || '');
    }

    function setLoading(loading) {
        submitBtn.disabled = loading;
        submitBtn.innerHTML = loading
            ? '<span class="spinner"></span> Sending Request…'
            : 'Pay Now';
    }

    function startPolling(phone) {
        statusCheck.classList.add('show');
        let attempts = 0;
        const maxAttempts = 12;

        pollInterval = setInterval(async () => {
            attempts++;
            try {
                const res = await fetch('check_status.php?phone=' + encodeURIComponent(phone));
                const data = await res.json();

                if (data.success) {
                    clearInterval(pollInterval);
                    statusCheck.classList.remove('show');
                    showMessage('success', 'Payment Confirmed!', 'Your M-Pesa payment was received successfully.');
                    form.reset();
                } else if (attempts >= maxAttempts) {
                    clearInterval(pollInterval);
                    statusCheck.classList.remove('show');
                    statusLabel.textContent = '';
                    showMessage('waiting', 'Confirmation Pending', 'We haven\'t received confirmation yet. Check your M-Pesa messages.');
                }
            } catch (e) {
                // silent – keep polling
            }
        }, 5000);
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (pollInterval) clearInterval(pollInterval);
        statusCheck.classList.remove('show');
        messageDiv.className = 'message';

        const phone = phoneInput.value.trim();
        const amount = amountInput.value.trim();

        if (!phone || !amount) {
            showMessage('error', 'Missing fields', 'Please enter both phone number and amount.');
            return;
        }

        setLoading(true);

        try {
            const res = await fetch('stk_push.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ phone, amount })
            });

            const data = await res.json();

            if (data.success) {
                showMessage('waiting', 'Check Your Phone!', 'An M-Pesa prompt has been sent. Enter your PIN to complete the payment.');
                startPolling(phone);
            } else {
                showMessage('error', 'Request Failed', data.message || 'Something went wrong. Please try again.');
            }
        } catch (err) {
            showMessage('error', 'Network Error', 'Could not reach the server. Please check your connection.');
        }

        setLoading(false);
    });
</script>
</body>
</html>
