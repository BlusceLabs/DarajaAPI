<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lipa Na M-Pesa</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
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
            padding: 28px 32px 24px;
            text-align: center;
        }

        .mpesa-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .mpesa-icon {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }

        .card-header h1 { color: #fff; font-size: 22px; font-weight: 700; }
        .card-header p  { color: rgba(255,255,255,0.78); font-size: 13px; margin-top: 4px; }

        .card-body { padding: 28px 28px 24px; }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #555;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrap { position: relative; }

        .input-prefix {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 13px;
            pointer-events: none;
            user-select: none;
        }

        input[type="tel"],
        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 12px 13px 12px 40px;
            border: 1.5px solid #e0e6e0;
            border-radius: 10px;
            font-size: 15px;
            color: #222;
            background: #fafafa;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #00a651;
            box-shadow: 0 0 0 3px rgba(0,166,81,0.12);
            background: #fff;
        }

        .hint { font-size: 11.5px; color: #aaa; margin-top: 5px; }

        .field-error {
            font-size: 11.5px;
            color: #c0392b;
            margin-top: 5px;
            display: none;
            align-items: center;
            gap: 4px;
        }
        .field-error.show { display: flex; }

        .field-ok {
            font-size: 11.5px;
            color: #1a8a4a;
            margin-top: 5px;
            display: none;
            align-items: center;
            gap: 4px;
        }
        .field-ok.show { display: flex; }

        input.invalid {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 3px rgba(231,76,60,0.10) !important;
        }
        input.valid {
            border-color: #00a651 !important;
            box-shadow: 0 0 0 3px rgba(0,166,81,0.10) !important;
        }

        /* Quick-amount chips */
        .quick-amounts {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 9px;
        }

        .chip {
            padding: 5px 13px;
            border: 1.5px solid #d8e8d8;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #006633;
            background: #f2fbf5;
            cursor: pointer;
            transition: all 0.15s;
        }

        .chip:hover, .chip.selected {
            background: #006633;
            color: #fff;
            border-color: #006633;
        }

        /* Optional reference toggle */
        .ref-toggle {
            font-size: 12px;
            color: #00a651;
            cursor: pointer;
            text-decoration: underline;
            margin-top: -8px;
            margin-bottom: 16px;
            display: inline-block;
        }

        .ref-field { display: none; margin-bottom: 18px; }
        .ref-field.show { display: block; }

        /* Button */
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #006633, #00a651);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.4px;
            transition: opacity 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .btn:active:not(:disabled) { transform: translateY(0); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .spinner {
            width: 16px; height: 16px;
            border: 2.5px solid rgba(255,255,255,0.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Messages */
        .message {
            margin-top: 16px;
            padding: 13px 15px;
            border-radius: 10px;
            font-size: 13.5px;
            display: none;
            gap: 10px;
            align-items: flex-start;
            line-height: 1.5;
        }
        .message.show { display: flex; }
        .message.success { background: #e8f8ef; color: #1a6b3a; border: 1px solid #b3e6c8; }
        .message.error   { background: #fdf2f2; color: #8b2020; border: 1px solid #f5c0c0; }
        .message.waiting { background: #fff8e6; color: #7a5a00; border: 1px solid #f0d98a; }
        .message .icon   { font-size: 17px; flex-shrink: 0; margin-top: 1px; }
        .message .text strong { display: block; margin-bottom: 2px; font-size: 14px; }

        /* Receipt details */
        .receipt-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 14px;
            margin-top: 10px;
            background: rgba(0,0,0,0.03);
            border-radius: 8px;
            padding: 10px 12px;
        }
        .receipt-grid dt { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: #1a6b3a; opacity: 0.7; }
        .receipt-grid dd { font-size: 12.5px; font-weight: 600; color: #1a6b3a; font-family: monospace; }

        /* Progress bar */
        .progress-wrap { margin-top: 14px; display: none; }
        .progress-wrap.show { display: block; }
        .progress-bar { height: 3px; background: #e8e8e8; border-radius: 2px; overflow: hidden; margin-bottom: 6px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #006633, #00a651); border-radius: 2px; animation: prog 60s linear forwards; }
        @keyframes prog { from { width: 0 } to { width: 100% } }
        .progress-label { font-size: 11.5px; color: #aaa; text-align: center; }

        /* Footer */
        .card-footer {
            border-top: 1px solid #f0f0f0;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .footer-link {
            font-size: 12px;
            color: #aaa;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .footer-link:hover { color: #006633; }
        .secure-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            color: #bbb;
        }
        .secure-badge svg { width: 12px; height: 12px; }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <div class="mpesa-logo">
            <div class="mpesa-icon">M</div>
            <h1>Lipa Na M-Pesa</h1>
        </div>
        <p>STK Push &mdash; Secure Mobile Payment</p>
    </div>

    <div class="card-body">
        <form id="paymentForm" novalidate>

            <div class="form-group">
                <label for="phone">Safaricom Phone Number</label>
                <div class="input-wrap">
                    <span class="input-prefix">📱</span>
                    <input type="tel" id="phone" placeholder="0712 345 678" autocomplete="tel" required>
                </div>
                <p class="field-error" id="phoneError">&#9888; Invalid Safaricom number. Use 07XX or 01XX format.</p>
                <p class="field-ok"    id="phoneOk">&#10003; Valid phone number</p>
                <p class="hint" id="phoneHint">Formats accepted: 07XX &bull; 01XX &bull; +254XXX</p>
            </div>

            <div class="form-group">
                <label for="amount">Amount (KES)</label>
                <div class="input-wrap">
                    <span class="input-prefix">KSh</span>
                    <input type="number" id="amount" placeholder="Enter amount" min="1" max="150000" required>
                </div>
                <div class="quick-amounts">
                    <span class="chip" data-val="50">50</span>
                    <span class="chip" data-val="100">100</span>
                    <span class="chip" data-val="500">500</span>
                    <span class="chip" data-val="1000">1,000</span>
                    <span class="chip" data-val="2500">2,500</span>
                    <span class="chip" data-val="5000">5,000</span>
                </div>
                <p class="field-error" id="amountError">&#9888; Enter an amount between KES 1 and KES 150,000.</p>
                <p class="field-ok"    id="amountOk">&#10003; Valid amount</p>
                <p class="hint" id="amountHint" style="margin-top:8px">Min: KES 1 &bull; Max: KES 150,000</p>
            </div>

            <span class="ref-toggle" id="refToggle">+ Add account reference</span>

            <div class="ref-field" id="refField">
                <label for="reference">Account Reference <span style="font-weight:400;text-transform:none;color:#aaa">(optional)</span></label>
                <div class="input-wrap">
                    <span class="input-prefix">#</span>
                    <input type="text" id="reference" placeholder="e.g. Invoice-001 or Order ID" maxlength="12">
                </div>
                <p class="hint">Max 12 characters. Appears on the customer's M-Pesa statement.</p>
            </div>

            <button type="submit" class="btn" id="submitBtn">Pay Now</button>
        </form>

        <div id="message" class="message">
            <span class="icon" id="msgIcon"></span>
            <span class="text" id="msgText"></span>
        </div>

        <div class="progress-wrap" id="progressWrap">
            <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
            <p class="progress-label">Waiting for payment confirmation…</p>
        </div>
    </div>

    <div class="card-footer">
        <a href="admin.php" class="footer-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
            </svg>
            Transaction Log
        </a>
        <div class="secure-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Secured by Safaricom Daraja
        </div>
    </div>
</div>

<script>
    const form        = document.getElementById('paymentForm');
    const phoneInput  = document.getElementById('phone');
    const amountInput = document.getElementById('amount');
    const refInput    = document.getElementById('reference');
    const submitBtn   = document.getElementById('submitBtn');
    const messageDiv  = document.getElementById('message');
    const msgIcon     = document.getElementById('msgIcon');
    const msgText     = document.getElementById('msgText');
    const progressWrap = document.getElementById('progressWrap');
    const refToggle   = document.getElementById('refToggle');
    const refField    = document.getElementById('refField');

    let pollInterval = null;

    // ------------------------------------------------------------------
    // REAL-TIME INLINE VALIDATION
    // ------------------------------------------------------------------
    function setFieldState(input, errorEl, okEl, hintEl, isValid, hasContent) {
        input.classList.toggle('invalid', hasContent && !isValid);
        input.classList.toggle('valid',   hasContent && isValid);
        errorEl.classList.toggle('show',  hasContent && !isValid);
        okEl.classList.toggle('show',     hasContent && isValid);
        if (hintEl) hintEl.style.display = hasContent ? 'none' : '';
    }

    function validatePhone(val) {
        const stripped = val.replace(/^(\+254|254|0)/, '');
        return /^(7|1)\d{8}$/.test(stripped);
    }

    function validateAmount(val) {
        const n = parseFloat(val);
        return !isNaN(n) && n >= 1 && n <= 150000;
    }

    const phoneError  = document.getElementById('phoneError');
    const phoneOk     = document.getElementById('phoneOk');
    const phoneHint   = document.getElementById('phoneHint');
    const amountError = document.getElementById('amountError');
    const amountOk    = document.getElementById('amountOk');
    const amountHint  = document.getElementById('amountHint');

    phoneInput.addEventListener('input', () => {
        const v = phoneInput.value.trim();
        setFieldState(phoneInput, phoneError, phoneOk, phoneHint, validatePhone(v), v.length > 0);
    });

    phoneInput.addEventListener('blur', () => {
        const v = phoneInput.value.trim();
        if (v) setFieldState(phoneInput, phoneError, phoneOk, phoneHint, validatePhone(v), true);
    });

    amountInput.addEventListener('input', () => {
        const v = amountInput.value.trim();
        setFieldState(amountInput, amountError, amountOk, amountHint, validateAmount(v), v.length > 0);
    });

    amountInput.addEventListener('blur', () => {
        const v = amountInput.value.trim();
        if (v) setFieldState(amountInput, amountError, amountOk, amountHint, validateAmount(v), true);
    });

    // Reference field toggle
    refToggle.addEventListener('click', () => {
        const open = refField.classList.toggle('show');
        refToggle.textContent = open ? '- Remove account reference' : '+ Add account reference';
    });

    // Quick-amount chips
    document.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            amountInput.value = chip.dataset.val;
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('selected'));
            chip.classList.add('selected');
        });
    });

    amountInput.addEventListener('input', () => {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('selected'));
        const v = parseInt(amountInput.value);
        document.querySelectorAll('.chip').forEach(c => {
            if (parseInt(c.dataset.val) === v) c.classList.add('selected');
        });
    });

    function showMessage(type, title, body) {
        const icons = { success: '✅', error: '❌', waiting: '⏳' };
        messageDiv.className = 'message show ' + type;
        msgIcon.textContent = icons[type] || 'ℹ️';
        msgText.innerHTML = (title ? '<strong>' + escHtml(title) + '</strong>' : '') + (body || '');
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function setLoading(on) {
        submitBtn.disabled = on;
        submitBtn.innerHTML = on
            ? '<span class="spinner"></span> Sending Request…'
            : 'Pay Now';
    }

    function resetProgress() {
        progressWrap.classList.remove('show');
        const fill = document.getElementById('progressFill');
        fill.style.animation = 'none';
        void fill.offsetWidth;
        fill.style.animation = '';
    }

    function startPolling(phone) {
        progressWrap.classList.add('show');
        let attempts = 0;
        const max = 12;

        pollInterval = setInterval(async () => {
            attempts++;
            try {
                const res  = await fetch('check_status.php?phone=' + encodeURIComponent(phone));
                const data = await res.json();

                if (data.success) {
                    clearInterval(pollInterval);
                    resetProgress();

                    let receiptHtml = '';
                    if (data.receipt || data.amount) {
                        receiptHtml = '<dl class="receipt-grid">';
                        if (data.amount)    receiptHtml += '<dt>Amount</dt><dd>KES ' + Number(data.amount).toLocaleString() + '</dd>';
                        if (data.receipt)   receiptHtml += '<dt>Receipt</dt><dd>' + escHtml(data.receipt) + '</dd>';
                        if (data.timestamp) receiptHtml += '<dt>Date</dt><dd>' + escHtml(String(data.timestamp)) + '</dd>';
                        receiptHtml += '</dl>';
                    }

                    showMessage('success', 'Payment Confirmed!', 'Transaction received successfully.' + receiptHtml);
                    form.reset();
                    document.querySelectorAll('.chip').forEach(c => c.classList.remove('selected'));

                } else if (attempts >= max) {
                    clearInterval(pollInterval);
                    resetProgress();
                    showMessage('waiting', 'Confirmation Pending',
                        'We haven\'t received confirmation yet. Please check your M-Pesa messages.');
                }
            } catch (e) { /* keep polling */ }
        }, 5000);
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (pollInterval) clearInterval(pollInterval);
        resetProgress();
        messageDiv.className = 'message';

        const phone     = phoneInput.value.trim();
        const amount    = amountInput.value.trim();
        const reference = refInput.value.trim();

        if (!phone || !amount) {
            showMessage('error', 'Missing fields', 'Please enter your phone number and amount.');
            return;
        }

        setLoading(true);

        try {
            const res  = await fetch('stk_push.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ phone, amount, reference })
            });
            const data = await res.json();

            if (data.success) {
                const ref = data.reference ? ' <span style="opacity:0.75;font-size:12px">(Ref: ' + escHtml(data.reference) + ')</span>' : '';
                showMessage('waiting', 'Check Your Phone!' + ref,
                    'An M-Pesa prompt has been sent to <strong>' + escHtml(phone) + '</strong>. Enter your PIN to complete the payment.');
                startPolling(phone);
            } else {
                showMessage('error', 'Request Failed', escHtml(data.message || 'Something went wrong. Please try again.'));
            }
        } catch (err) {
            showMessage('error', 'Network Error', 'Could not reach the server. Check your connection and try again.');
        }

        setLoading(false);
    });
</script>
</body>
</html>
