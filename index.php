<?php
require_once __DIR__ . '/config.php';
$prov = defined('PAYMENT_PROVIDER') ? PAYMENT_PROVIDER : 'tinypesa';

$pCfg = [
    'tinypesa' => [
        'name'         => 'Lipa Na M-Pesa',
        'subtitle'     => 'STK Push &mdash; Secure Mobile Payment',
        'logo'         => '/logos/tinypesa.png',
        'grad_from'    => '#006633', 'grad_to' => '#00a651', 'text_dark' => false,
        'phone_label'  => 'Safaricom Phone Number', 'phone_optional' => false,
        'phone_ph'     => '0712 345 678',
        'phone_hint'   => 'Formats accepted: 07XX &bull; 01XX &bull; +254XXX',
        'phone_err'    => 'Invalid Safaricom number. Use 07XX or 01XX format.',
        'phone_strict' => true,
        'currency'     => 'KES', 'symbol' => 'KSh',
        'amount_min'   => 1, 'amount_max' => 150000,
        'chips'        => [50, 100, 500, 1000, 2500, 5000],
        'ref_hint'     => 'Max 12 characters. Appears on the customer\'s M-Pesa statement.',
        'push_msg'     => 'An M-Pesa prompt has been sent to <strong>{phone}</strong>. Enter your PIN to complete the payment.',
        'secure_badge' => 'Secured by TinyPesa',
        'flow'         => 'stk',
    ],
    'daraja' => [
        'name'         => 'Lipa Na M-Pesa',
        'subtitle'     => 'STK Push &mdash; Secure Mobile Payment',
        'logo'         => '/logos/daraja.svg',
        'grad_from'    => '#006633', 'grad_to' => '#00a651', 'text_dark' => false,
        'phone_label'  => 'Safaricom Phone Number', 'phone_optional' => false,
        'phone_ph'     => '0712 345 678',
        'phone_hint'   => 'Formats accepted: 07XX &bull; 01XX &bull; +254XXX',
        'phone_err'    => 'Invalid Safaricom number. Use 07XX or 01XX format.',
        'phone_strict' => true,
        'currency'     => 'KES', 'symbol' => 'KSh',
        'amount_min'   => 1, 'amount_max' => 150000,
        'chips'        => [50, 100, 500, 1000, 2500, 5000],
        'ref_hint'     => 'Max 12 characters. Appears on the customer\'s M-Pesa statement.',
        'push_msg'     => 'An M-Pesa prompt has been sent to <strong>{phone}</strong>. Enter your PIN to complete the payment.',
        'secure_badge' => 'Secured by Safaricom Daraja',
        'flow'         => 'stk',
    ],
    'pesapal' => [
        'name'         => 'Pay with PesaPal',
        'subtitle'     => 'Secure Hosted Checkout',
        'logo'         => '/logos/pesapal.png',
        'grad_from'    => '#1565C0', 'grad_to' => '#1E88E5', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 0712 345 678',
        'phone_hint'   => 'Your mobile money number (optional &mdash; enter on checkout page)',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => 'KES', 'symbol' => 'KSh',
        'amount_min'   => 1, 'amount_max' => 150000,
        'chips'        => [50, 100, 500, 1000, 2500, 5000],
        'ref_hint'     => 'Max 12 characters. Used as the order reference on the checkout page.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by PesaPal',
        'flow'         => 'redirect',
    ],
    'flutterwave' => [
        'name'         => 'Pay with Flutterwave',
        'subtitle'     => 'Secure Hosted Checkout',
        'logo'         => '/logos/flutterwave_32.png',
        'grad_from'    => '#E8670F', 'grad_to' => '#F5A623', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 0712 345 678',
        'phone_hint'   => 'Mobile money, card &amp; bank transfer accepted on checkout page',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => defined('FLW_CURRENCY') ? FLW_CURRENCY : 'KES',
        'symbol'       => defined('FLW_CURRENCY') ? FLW_CURRENCY : 'KSh',
        'amount_min'   => 1, 'amount_max' => 1000000,
        'chips'        => [50, 100, 500, 1000, 2500, 5000],
        'ref_hint'     => 'Max 12 characters. Used as the order reference on the checkout page.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by Flutterwave',
        'flow'         => 'redirect',
    ],
    'paystack' => [
        'name'         => 'Pay with Paystack',
        'subtitle'     => 'Secure Hosted Checkout',
        'logo'         => '/logos/paystack.png',
        'grad_from'    => '#011B33', 'grad_to' => '#0066CC', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 0712 345 678',
        'phone_hint'   => 'Card, bank transfer &amp; mobile money accepted on checkout page',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => defined('PAYSTACK_CURRENCY') ? PAYSTACK_CURRENCY : 'KES',
        'symbol'       => defined('PAYSTACK_CURRENCY') ? PAYSTACK_CURRENCY : 'KSh',
        'amount_min'   => 1, 'amount_max' => 1000000,
        'chips'        => [100, 500, 1000, 2500, 5000, 10000],
        'ref_hint'     => 'Max 12 characters. Used as the order reference on the checkout page.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by Paystack',
        'flow'         => 'redirect',
    ],
    'mtnmomo' => [
        'name'         => 'MTN Mobile Money',
        'subtitle'     => 'Pay with MTN MoMo',
        'logo'         => '/logos/mtnmomo.svg',
        'grad_from'    => '#FFCC00', 'grad_to' => '#F6A200', 'text_dark' => true,
        'phone_label'  => 'MTN Phone Number', 'phone_optional' => false,
        'phone_ph'     => 'e.g. 0701 234 567',
        'phone_hint'   => 'Enter your MTN registered number',
        'phone_err'    => 'Please enter a valid phone number (min 7 digits).',
        'phone_strict' => false,
        'currency'     => defined('MTNMOMO_CURRENCY') ? MTNMOMO_CURRENCY : 'UGX',
        'symbol'       => defined('MTNMOMO_CURRENCY') ? MTNMOMO_CURRENCY : 'UGX',
        'amount_min'   => 100, 'amount_max' => 10000000,
        'chips'        => [1000, 5000, 10000, 50000, 100000, 500000],
        'ref_hint'     => 'Max 12 characters. Appears on the customer\'s MoMo statement.',
        'push_msg'     => 'An MTN MoMo prompt has been sent to <strong>{phone}</strong>. Approve the request on your phone to complete the payment.',
        'secure_badge' => 'Secured by MTN MoMo',
        'flow'         => 'stk',
    ],
    'airtelmoney' => [
        'name'         => 'Airtel Money',
        'subtitle'     => 'Pay with Airtel Money',
        'logo'         => '/logos/airtelmoney.png',
        'grad_from'    => '#CC0000', 'grad_to' => '#E40521', 'text_dark' => false,
        'phone_label'  => 'Airtel Phone Number', 'phone_optional' => false,
        'phone_ph'     => '0701 234 567',
        'phone_hint'   => 'Your Airtel registered number',
        'phone_err'    => 'Please enter a valid phone number (min 7 digits).',
        'phone_strict' => false,
        'currency'     => defined('AIRTEL_CURRENCY') ? AIRTEL_CURRENCY : 'KES',
        'symbol'       => defined('AIRTEL_CURRENCY') ? AIRTEL_CURRENCY : 'KSh',
        'amount_min'   => 1, 'amount_max' => 500000,
        'chips'        => [50, 100, 500, 1000, 2500, 5000],
        'ref_hint'     => 'Max 12 characters. Appears on the customer\'s Airtel statement.',
        'push_msg'     => 'An Airtel Money prompt has been sent to <strong>{phone}</strong>. Enter your PIN to complete the payment.',
        'secure_badge' => 'Secured by Airtel Money',
        'flow'         => 'stk',
    ],
    'dpopay' => [
        'name'         => 'Pay with DPO Pay',
        'subtitle'     => 'Secure Hosted Checkout',
        'logo'         => '/logos/dpopay.png',
        'grad_from'    => '#1a3c6e', 'grad_to' => '#2861bd', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 0712 345 678',
        'phone_hint'   => 'Cards, mobile money &amp; bank transfer accepted on checkout page',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => defined('DPO_CURRENCY') ? DPO_CURRENCY : 'KES',
        'symbol'       => defined('DPO_CURRENCY') ? DPO_CURRENCY : 'KSh',
        'amount_min'   => 1, 'amount_max' => 1000000,
        'chips'        => [50, 100, 500, 1000, 2500, 5000],
        'ref_hint'     => 'Max 12 characters. Used as the order reference on the checkout page.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by DPO Pay',
        'flow'         => 'redirect',
    ],
    'ozow' => [
        'name'         => 'Pay with Ozow',
        'subtitle'     => 'Instant EFT &mdash; South Africa',
        'logo'         => '/logos/ozow.png',
        'grad_from'    => '#00857D', 'grad_to' => '#00B5B0', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 083 123 4567',
        'phone_hint'   => 'Your South African mobile number (optional)',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => 'ZAR', 'symbol' => 'R',
        'amount_min'   => 1, 'amount_max' => 100000,
        'chips'        => [50, 100, 250, 500, 1000, 2000],
        'ref_hint'     => 'Max 12 characters. Used as the order reference.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by Ozow',
        'flow'         => 'redirect',
    ],

    // ── Francophone Africa ────────────────────────────────────────────────────
    'cinetpay' => [
        'name'         => 'Pay with CinetPay',
        'subtitle'     => 'Mobile Money &amp; Cards &mdash; Francophone Africa',
        'logo'         => '/logos/cinetpay.svg',
        'grad_from'    => '#005BEA', 'grad_to' => '#00C6FB', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. +225 07 00 00 00',
        'phone_hint'   => 'Your mobile number (optional — enter on checkout page)',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => 'XOF', 'symbol' => 'CFA',
        'amount_min'   => 100, 'amount_max' => 5000000,
        'chips'        => [500, 1000, 2500, 5000, 10000, 25000],
        'ref_hint'     => 'Order reference (alphanumeric).',
        'push_msg'     => '',
        'secure_badge' => 'Secured by CinetPay',
        'flow'         => 'redirect',
    ],

    // ── North Africa ──────────────────────────────────────────────────────────
    'paymob' => [
        'name'         => 'Pay with Paymob',
        'subtitle'     => 'Cards &amp; Wallets &mdash; Egypt &amp; North Africa',
        'logo'         => '/logos/paymob.svg',
        'grad_from'    => '#0275D8', 'grad_to' => '#03A9F4', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 01012345678',
        'phone_hint'   => 'Your phone number (optional)',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => 'EGP', 'symbol' => 'EGP',
        'amount_min'   => 1, 'amount_max' => 100000,
        'chips'        => [50, 100, 250, 500, 1000, 5000],
        'ref_hint'     => 'Order reference.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by Paymob',
        'flow'         => 'redirect',
    ],

    // ── Southern Africa ───────────────────────────────────────────────────────
    'ecocash' => [
        'name'         => 'Pay with Ecocash',
        'subtitle'     => 'Mobile Money &mdash; Zimbabwe',
        'logo'         => '/logos/ecocash.svg',
        'grad_from'    => '#E31837', 'grad_to' => '#FF6B35', 'text_dark' => false,
        'phone_label'  => 'Ecocash Phone Number', 'phone_optional' => false,
        'phone_ph'     => 'e.g. 0772 123 456',
        'phone_hint'   => 'Registered Ecocash Zimbabwe number',
        'phone_err'    => 'Please enter a valid Zimbabwe mobile number.',
        'phone_strict' => false,
        'currency'     => 'USD', 'symbol' => '$',
        'amount_min'   => 1, 'amount_max' => 5000,
        'chips'        => [5, 10, 20, 50, 100, 200],
        'ref_hint'     => 'Max 20 characters.',
        'push_msg'     => 'An Ecocash payment prompt will appear on your phone.',
        'secure_badge' => 'Secured by Ecocash',
        'flow'         => 'stk',
    ],

    // ── Orange Belt (West & North Africa) ─────────────────────────────────────
    'orangemoney' => [
        'name'         => 'Pay with Orange Money',
        'subtitle'     => 'Mobile Money &mdash; West &amp; North Africa',
        'logo'         => '/logos/orangemoney.svg',
        'grad_from'    => '#FF7900', 'grad_to' => '#FFA500', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. +225 07 00 00 00',
        'phone_hint'   => 'Your Orange Money number (optional)',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => 'XOF', 'symbol' => 'CFA',
        'amount_min'   => 100, 'amount_max' => 2000000,
        'chips'        => [500, 1000, 2500, 5000, 10000, 25000],
        'ref_hint'     => 'Order reference.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by Orange Money',
        'flow'         => 'redirect',
    ],

    // ── Pan-African (18+ countries) ────────────────────────────────────────────
    'cellulant' => [
        'name'         => 'Pay with Tingg',
        'subtitle'     => 'Mobile Money, Cards &amp; Bank &mdash; 18+ African Countries',
        'logo'         => '/logos/cellulant.svg',
        'grad_from'    => '#4A1B8C', 'grad_to' => '#6C2BD9', 'text_dark' => false,
        'phone_label'  => 'Phone Number', 'phone_optional' => true,
        'phone_ph'     => 'e.g. 0712 345 678',
        'phone_hint'   => 'Your mobile number (optional — enter on checkout page)',
        'phone_err'    => 'Please enter a valid phone number.',
        'phone_strict' => false,
        'currency'     => 'KES', 'symbol' => 'KSh',
        'amount_min'   => 1, 'amount_max' => 500000,
        'chips'        => [100, 500, 1000, 2500, 5000, 10000],
        'ref_hint'     => 'Order reference.',
        'push_msg'     => '',
        'secure_badge' => 'Secured by Cellulant Tingg',
        'flow'         => 'redirect',
    ],
];

$c = $pCfg[$prov] ?? $pCfg['tinypesa'];

$gradStyle    = "background: linear-gradient(135deg, {$c['grad_from']} 0%, {$c['grad_to']} 100%);";
$headerColor  = $c['text_dark'] ? '#111' : '#fff';
$headerColor2 = $c['text_dark'] ? 'rgba(0,0,0,0.55)' : 'rgba(255,255,255,0.78)';
$amountLabel  = htmlspecialchars($c['currency']) . ' Amount';
$amountMin    = $c['amount_min'];
$amountMax    = $c['amount_max'];
$amountHint   = 'Min: ' . $c['symbol'] . ' ' . number_format($amountMin) . ' &bull; Max: ' . $c['symbol'] . ' ' . number_format($amountMax);
$phoneOptionalBadge = $c['phone_optional'] ? ' <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--text-4)">(optional)</span>' : '';

$chipsHtml = '';
foreach ($c['chips'] as $v) {
    $label = $v >= 1000 ? number_format($v / 1000, $v % 1000 === 0 ? 0 : 1) . 'K' : number_format($v);
    $chipsHtml .= '<span class="chip" data-val="' . $v . '">' . $label . '</span>';
}

$jsPhoneStrict = $c['phone_strict'] ? 'true' : 'false';
$jsPhoneOptional = $c['phone_optional'] ? 'true' : 'false';
$jsFlow           = htmlspecialchars($c['flow']);
$jsPushMsg        = addslashes($c['push_msg']);
$jsCurrency       = addslashes($c['symbol']);
$jsAmountMin      = $amountMin;
$jsAmountMax      = $amountMax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($c['name']) ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <style>
        :root {
            --bg: #f0f4f0;
            --surface: #fff;
            --surface-alt: #fafafa;
            --border: #e0e6e0;
            --border-light: #f0f0f0;
            --text: #222;
            --text-2: #555;
            --text-3: #888;
            --text-4: #aaa;
            --text-5: #bbb;
            --text-6: #ccc;
            --shadow: rgba(0,0,0,0.10);
            --chip-bg: #f2fbf5;
            --chip-border: #d8e8d8;
            --receipt-bg: rgba(0,0,0,0.03);
            --toggle-track: #ddd;
            --progress-bg: #e8e8e8;
            --msg-success-bg: #e8f8ef; --msg-success-text: #1a6b3a; --msg-success-border: #b3e6c8;
            --msg-error-bg: #fdf2f2;   --msg-error-text: #8b2020;   --msg-error-border: #f5c0c0;
            --msg-wait-bg: #fff8e6;    --msg-wait-text: #7a5a00;    --msg-wait-border: #f0d98a;
            --receipt-label: rgba(26,107,58,0.7);
            --receipt-value: #1a6b3a;
            --accent: <?= $c['grad_to'] ?>;
            --accent-dark: <?= $c['grad_from'] ?>;
        }
        :root[data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-alt: #0d1117;
            --border: #30363d;
            --border-light: #21262d;
            --text: #e6edf3;
            --text-2: #adbac7;
            --text-3: #768390;
            --text-4: #636e7b;
            --text-5: #545d68;
            --text-6: #444c56;
            --shadow: rgba(0,0,0,0.50);
            --chip-bg: #1c2128;
            --chip-border: #30363d;
            --receipt-bg: rgba(255,255,255,0.05);
            --toggle-track: #444c56;
            --progress-bg: #21262d;
            --msg-success-bg: #0f2a1a; --msg-success-text: #56d364; --msg-success-border: #1a4731;
            --msg-error-bg: #2d1111;   --msg-error-text: #f97171;   --msg-error-border: #5c2020;
            --msg-wait-bg: #2a2000;    --msg-wait-text: #e3b341;    --msg-wait-border: #5a4000;
            --receipt-label: rgba(86,211,100,0.65);
            --receipt-value: #56d364;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: background 0.2s, color 0.2s;
        }

        .card {
            background: var(--surface);
            border-radius: 20px;
            box-shadow: 0 8px 40px var(--shadow);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }

        .card-header {
            <?= $gradStyle ?>
            padding: 28px 32px 24px;
            text-align: center;
        }

        .provider-logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 4px;
        }

        .provider-logo-img {
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.92);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.18);
        }

        .provider-logo-img img {
            width: 26px; height: 26px;
            object-fit: contain;
        }

        .card-header h1 { color: <?= $headerColor ?>; font-size: 22px; font-weight: 700; }
        .card-header p  { color: <?= $headerColor2 ?>; font-size: 13px; margin-top: 4px; }

        .card-body { padding: 28px 28px 24px; }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-2);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrap { position: relative; }

        .input-prefix {
            position: absolute;
            left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-4);
            font-size: 13px;
            pointer-events: none;
            user-select: none;
        }

        input[type="tel"],
        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 12px 13px 12px 40px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            color: var(--text);
            background: var(--surface-alt);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent);
            background: var(--surface);
        }

        .hint { font-size: 11.5px; color: var(--text-4); margin-top: 5px; }

        .field-error {
            font-size: 11.5px; color: #c0392b; margin-top: 5px;
            display: none; align-items: center; gap: 4px;
        }
        :root[data-theme="dark"] .field-error { color: #f97171; }
        .field-error.show { display: flex; }

        .field-ok {
            font-size: 11.5px; color: #1a8a4a; margin-top: 5px;
            display: none; align-items: center; gap: 4px;
        }
        :root[data-theme="dark"] .field-ok { color: #56d364; }
        .field-ok.show { display: flex; }

        input.invalid { border-color: #e74c3c !important; box-shadow: 0 0 0 3px rgba(231,76,60,0.10) !important; }
        input.valid   { border-color: var(--accent) !important; box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent) !important; }

        .quick-amounts {
            display: flex; flex-wrap: wrap; gap: 7px; margin-top: 9px;
        }

        .chip {
            padding: 5px 13px;
            border: 1.5px solid var(--chip-border);
            border-radius: 20px;
            font-size: 12px; font-weight: 600;
            color: var(--accent-dark);
            background: var(--chip-bg);
            cursor: pointer;
            transition: all 0.15s;
        }
        :root[data-theme="dark"] .chip { color: var(--accent); }

        .chip:hover, .chip.selected {
            background: var(--accent-dark); color: #fff; border-color: var(--accent-dark);
        }

        .ref-toggle {
            font-size: 12px; color: var(--accent); cursor: pointer;
            text-decoration: underline;
            margin-top: -8px; margin-bottom: 16px; display: inline-block;
        }

        .ref-field { display: none; margin-bottom: 18px; }
        .ref-field.show { display: block; }

        .btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, <?= $c['grad_from'] ?>, <?= $c['grad_to'] ?>);
            color: <?= $c['text_dark'] ? '#111' : '#fff' ?>; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            letter-spacing: 0.4px;
            transition: opacity 0.2s, transform 0.1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .btn:active:not(:disabled) { transform: translateY(0); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .spinner {
            width: 16px; height: 16px;
            border: 2.5px solid <?= $c['text_dark'] ? 'rgba(0,0,0,0.25)' : 'rgba(255,255,255,0.35)' ?>;
            border-top-color: <?= $c['text_dark'] ? '#111' : '#fff' ?>;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .message {
            margin-top: 16px; padding: 13px 15px; border-radius: 10px;
            font-size: 13.5px; display: none;
            gap: 10px; align-items: flex-start; line-height: 1.5;
        }
        .message.show   { display: flex; }
        .message.success { background: var(--msg-success-bg); color: var(--msg-success-text); border: 1px solid var(--msg-success-border); }
        .message.error   { background: var(--msg-error-bg);   color: var(--msg-error-text);   border: 1px solid var(--msg-error-border); }
        .message.waiting { background: var(--msg-wait-bg);    color: var(--msg-wait-text);    border: 1px solid var(--msg-wait-border); }
        .message .icon   { font-size: 17px; flex-shrink: 0; margin-top: 1px; }
        .message .text strong { display: block; margin-bottom: 2px; font-size: 14px; }

        .receipt-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px;
            margin-top: 10px;
            background: var(--receipt-bg);
            border-radius: 8px; padding: 10px 12px;
        }
        .receipt-grid dt { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: var(--receipt-label); }
        .receipt-grid dd { font-size: 12.5px; font-weight: 600; color: var(--receipt-value); font-family: monospace; }

        .progress-wrap { margin-top: 14px; display: none; }
        .progress-wrap.show { display: block; }
        .progress-bar { height: 3px; background: var(--progress-bg); border-radius: 2px; overflow: hidden; margin-bottom: 6px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, <?= $c['grad_from'] ?>, <?= $c['grad_to'] ?>); border-radius: 2px; animation: prog 60s linear forwards; }
        @keyframes prog { from { width: 0 } to { width: 100% } }
        .progress-label { font-size: 11.5px; color: var(--text-4); text-align: center; }

        .card-footer {
            border-top: 1px solid var(--border-light);
            padding: 14px 28px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
        }
        .footer-link {
            font-size: 12px; color: var(--text-4); text-decoration: none;
            display: flex; align-items: center; gap: 5px;
        }
        .footer-link:hover { color: var(--accent-dark); }
        .secure-badge {
            display: flex; align-items: center; gap: 5px;
            font-size: 11.5px; color: var(--text-5);
        }
        .secure-badge svg { width: 12px; height: 12px; }

        .copy-btn {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 600; padding: 2px 8px;
            border: 1.5px solid currentColor; border-radius: 5px;
            background: none; cursor: pointer; opacity: 0.7;
            transition: opacity 0.15s; color: inherit; margin-left: 6px;
            vertical-align: middle;
        }
        .copy-btn:hover { opacity: 1; }
        .copy-btn.copied { opacity: 1; }

        .pay-again-btn {
            margin-top: 12px; width: 100%; padding: 10px;
            background: none; color: var(--msg-success-text);
            border: 1.5px solid currentColor; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
        }
        .pay-again-btn:hover { opacity: 0.75; }

        .theme-btn {
            position: fixed; bottom: 20px; right: 20px;
            width: 40px; height: 40px; border-radius: 50%;
            border: 1.5px solid var(--border);
            background: var(--surface); color: var(--text-2);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 12px var(--shadow); z-index: 999;
            font-size: 17px; transition: all 0.2s;
        }
        .theme-btn:hover { transform: scale(1.1); }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <div class="provider-logo-wrap">
            <div class="provider-logo-img">
                <img src="<?= htmlspecialchars($c['logo']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" onerror="this.style.display='none'">
            </div>
            <h1><?= htmlspecialchars($c['name']) ?></h1>
        </div>
        <p><?= $c['subtitle'] ?></p>
    </div>

    <div class="card-body">
        <form id="paymentForm" novalidate>

            <div class="form-group">
                <label for="phone"><?= htmlspecialchars($c['phone_label']) ?><?= $phoneOptionalBadge ?></label>
                <div class="input-wrap">
                    <span class="input-prefix">📱</span>
                    <input type="tel" id="phone" placeholder="<?= htmlspecialchars($c['phone_ph']) ?>" autocomplete="tel"<?= $c['phone_optional'] ? '' : ' required' ?>>
                </div>
                <p class="field-error" id="phoneError">&#9888; <?= htmlspecialchars($c['phone_err']) ?></p>
                <p class="field-ok"    id="phoneOk">&#10003; Valid phone number</p>
                <p class="hint" id="phoneHint"><?= $c['phone_hint'] ?></p>
            </div>

            <div class="form-group">
                <label for="amount"><?= $amountLabel ?></label>
                <div class="input-wrap">
                    <span class="input-prefix"><?= htmlspecialchars($c['symbol']) ?></span>
                    <input type="number" id="amount" placeholder="Enter amount" min="<?= $amountMin ?>" max="<?= $amountMax ?>" required>
                </div>
                <div class="quick-amounts">
                    <?= $chipsHtml ?>
                </div>
                <p class="field-error" id="amountError">&#9888; Enter an amount between <?= $c['symbol'] ?> <?= number_format($amountMin) ?> and <?= $c['symbol'] ?> <?= number_format($amountMax) ?>.</p>
                <p class="field-ok"    id="amountOk">&#10003; Valid amount</p>
                <p class="hint" id="amountHint" style="margin-top:8px"><?= $amountHint ?></p>
            </div>

            <span class="ref-toggle" id="refToggle">+ Add account reference</span>

            <div class="ref-field" id="refField">
                <label for="reference">Account Reference <span style="font-weight:400;text-transform:none;color:var(--text-4)">(optional)</span></label>
                <div class="input-wrap">
                    <span class="input-prefix">#</span>
                    <input type="text" id="reference" placeholder="e.g. Invoice-001 or Order ID" maxlength="12">
                </div>
                <p class="hint"><?= htmlspecialchars($c['ref_hint']) ?></p>
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
            <?= htmlspecialchars($c['secure_badge']) ?>
        </div>
    </div>
</div>

<button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle dark mode"></button>

<script>
    // ---- Config from PHP ----
    const PHONE_STRICT   = <?= $jsPhoneStrict ?>;
    const PHONE_OPTIONAL = <?= $jsPhoneOptional ?>;
    const FLOW           = '<?= $jsFlow ?>';
    const PUSH_MSG       = '<?= $jsPushMsg ?>';
    const CURRENCY_SYM   = '<?= $jsCurrency ?>';
    const AMOUNT_MIN     = <?= $jsAmountMin ?>;
    const AMOUNT_MAX     = <?= $jsAmountMax ?>;

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

    // ---- Form ----
    const form         = document.getElementById('paymentForm');
    const phoneInput   = document.getElementById('phone');
    const amountInput  = document.getElementById('amount');
    const refInput     = document.getElementById('reference');
    const submitBtn    = document.getElementById('submitBtn');
    const messageDiv   = document.getElementById('message');
    const msgIcon      = document.getElementById('msgIcon');
    const msgText      = document.getElementById('msgText');
    const progressWrap = document.getElementById('progressWrap');
    const refToggle    = document.getElementById('refToggle');
    const refField     = document.getElementById('refField');
    const phoneError   = document.getElementById('phoneError');
    const phoneOk      = document.getElementById('phoneOk');
    const phoneHint    = document.getElementById('phoneHint');
    const amountError  = document.getElementById('amountError');
    const amountOk     = document.getElementById('amountOk');
    const amountHint   = document.getElementById('amountHint');

    let pollInterval = null;

    function setFieldState(input, errorEl, okEl, hintEl, isValid, hasContent) {
        input.classList.toggle('invalid', hasContent && !isValid);
        input.classList.toggle('valid',   hasContent && isValid);
        errorEl.classList.toggle('show',  hasContent && !isValid);
        okEl.classList.toggle('show',     hasContent && isValid);
        if (hintEl) hintEl.style.display = hasContent ? 'none' : '';
    }

    function validatePhone(val) {
        if (!val) return PHONE_OPTIONAL;
        if (PHONE_STRICT) {
            const stripped = val.replace(/^(\+254|254|0)/, '');
            return /^(7|1)\d{8}$/.test(stripped);
        }
        return val.replace(/\D/g, '').length >= 7;
    }

    function validateAmount(val) {
        const n = parseFloat(val);
        return !isNaN(n) && n >= AMOUNT_MIN && n <= AMOUNT_MAX;
    }

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

    refToggle.addEventListener('click', () => {
        const open = refField.classList.toggle('show');
        refToggle.textContent = open ? '- Remove account reference' : '+ Add account reference';
    });

    document.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            amountInput.value = chip.dataset.val;
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('selected'));
            chip.classList.add('selected');
            amountInput.dispatchEvent(new Event('input'));
        });
    });

    amountInput.addEventListener('input', () => {
        const v = parseInt(amountInput.value);
        document.querySelectorAll('.chip').forEach(c => {
            c.classList.toggle('selected', parseInt(c.dataset.val) === v);
        });
    });

    function showMessage(type, title, body) {
        const icons = { success: '✅', error: '❌', waiting: '⏳' };
        messageDiv.className = 'message show ' + type;
        msgIcon.textContent = icons[type] || 'ℹ️';
        msgText.innerHTML = (title ? '<strong>' + escHtml(title) + '</strong>' : '') + (body || '');
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
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
                        if (data.amount)    receiptHtml += '<dt>Amount</dt><dd>' + CURRENCY_SYM + ' ' + Number(data.amount).toLocaleString() + '</dd>';
                        if (data.receipt)   receiptHtml += '<dt>Receipt</dt><dd>' + escHtml(data.receipt) + '<button class="copy-btn" onclick="copyReceipt(\'' + escHtml(data.receipt) + '\', this)" title="Copy receipt">Copy</button></dd>';
                        if (data.timestamp) receiptHtml += '<dt>Date</dt><dd>' + escHtml(String(data.timestamp)) + '</dd>';
                        receiptHtml += '</dl>';
                    }
                    const payAgainHtml = '<button class="pay-again-btn" onclick="payAgain()">Make Another Payment</button>';

                    showMessage('success', 'Payment Confirmed!', 'Transaction received successfully.' + receiptHtml + payAgainHtml);
                    form.reset();
                    document.querySelectorAll('.chip').forEach(c => c.classList.remove('selected'));

                } else if (attempts >= max) {
                    clearInterval(pollInterval);
                    resetProgress();
                    showMessage('waiting', 'Confirmation Pending',
                        'We haven\'t received confirmation yet. Please check your payment app messages.');
                }
            } catch (e) { /* keep polling */ }
        }, 5000);
    }

    function copyReceipt(receipt, btn) {
        navigator.clipboard.writeText(receipt).then(() => {
            const prev = btn.textContent;
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(() => { btn.textContent = prev; btn.classList.remove('copied'); }, 1500);
        }).catch(() => {});
    }

    function payAgain() {
        messageDiv.className = 'message';
        resetProgress();
        [phoneInput, amountInput, refInput].forEach(inp => {
            inp.value = '';
            inp.classList.remove('valid', 'invalid');
        });
        [phoneError, phoneOk, amountError, amountOk].forEach(el => el.classList.remove('show'));
        phoneHint.style.display = '';
        amountHint.style.display = '';
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('selected'));
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Pay Now';
        phoneInput.focus();
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (pollInterval) clearInterval(pollInterval);
        resetProgress();
        messageDiv.className = 'message';

        const phone     = phoneInput.value.trim();
        const amount    = amountInput.value.trim();
        const reference = refInput.value.trim();

        if (!PHONE_OPTIONAL && !phone) {
            showMessage('error', 'Missing fields', 'Please enter your phone number and amount.');
            return;
        }
        if (!amount) {
            showMessage('error', 'Missing amount', 'Please enter the amount to pay.');
            return;
        }
        if (!validateAmount(amount)) {
            showMessage('error', 'Invalid amount', 'Enter an amount between ' + CURRENCY_SYM + ' ' + AMOUNT_MIN.toLocaleString() + ' and ' + CURRENCY_SYM + ' ' + AMOUNT_MAX.toLocaleString() + '.');
            return;
        }
        if (phone && !validatePhone(phone)) {
            showMessage('error', 'Invalid phone', 'Please enter a valid phone number.');
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
                if (data.flow === 'redirect' && data.redirect_url) {
                    showMessage('waiting', 'Redirecting…',
                        'Taking you to the payment page. Please complete your payment there.');
                    setTimeout(() => { window.location.href = data.redirect_url; }, 1500);
                } else {
                    const ref = data.reference ? ' <span style="opacity:0.75;font-size:12px">(Ref: ' + escHtml(data.reference) + ')</span>' : '';
                    const pushBody = PUSH_MSG ? PUSH_MSG.replace('{phone}', escHtml(phone)) : 'A payment request has been sent to <strong>' + escHtml(phone) + '</strong>. Approve it to complete the payment.';
                    showMessage('waiting', 'Check Your Phone!' + ref, pushBody);
                    startPolling(phone);
                }
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
