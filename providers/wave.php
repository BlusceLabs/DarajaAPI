<?php
/**
 * providers/wave.php — Wave Mobile Money (West & Central Africa)
 *
 * Wave is the leading mobile money app in Senegal and Côte d'Ivoire, and growing
 * rapidly in Mali, Burkina Faso, Cameroon, Uganda, and Zambia.
 *
 * Required constants (define in config.php):
 *   WAVE_API_KEY        — Secret API key from Wave Business dashboard
 *   WAVE_SUCCESS_URL    — Where to redirect after a successful payment
 *   WAVE_ERROR_URL      — Where to redirect after a failed / cancelled payment
 *   WAVE_CALLBACK_URL   — Webhook URL for async payment status notifications
 *                         (set to https://yourdomain.com/callback_wave.php)
 *
 * Optional constants:
 *   WAVE_CURRENCY       — 'XOF' (default, SN/CI/ML/BF), 'XAF' (CM),
 *                         'UGX' (UG), 'ZMW' (ZM)
 *
 * Countries: Senegal (SN/XOF), Côte d'Ivoire (CI/XOF), Mali (ML/XOF),
 *            Burkina Faso (BF/XOF), Cameroon (CM/XAF), Uganda (UG/UGX),
 *            Zambia (ZM/ZMW)
 *
 * Registration: https://www.wave.com/en/business/
 * API docs:     https://docs.wave.com/
 */

function provider_flow(): string { return 'redirect'; }

function provider_initiate(string $amount, string $phone, string $ref): array
{
    if (!defined('WAVE_API_KEY') || !defined('WAVE_SUCCESS_URL') || !defined('WAVE_ERROR_URL') || !defined('WAVE_CALLBACK_URL')) {
        return ['success' => false, 'message' => 'Wave is not configured. Set WAVE_API_KEY, WAVE_SUCCESS_URL, WAVE_ERROR_URL and WAVE_CALLBACK_URL in config.php.'];
    }

    $currency = defined('WAVE_CURRENCY') ? WAVE_CURRENCY : 'XOF';

    $body = [
        'currency'             => $currency,
        'amount'               => (string)(int)$amount,
        'error_url'            => WAVE_ERROR_URL,
        'success_url'          => WAVE_SUCCESS_URL,
        'checkout_status_url'  => WAVE_CALLBACK_URL,
        'payment_reason'       => 'Payment ' . $ref,
    ];
    if ($phone) {
        $body['restrict_mobile_money_providers'] = ['wave'];
        $body['client_phone'] = preg_replace('/\D/', '', $phone);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.wave.com/v1/checkout/sessions',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . WAVE_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        return ['success' => false, 'message' => 'Wave connection error: ' . ($curlErr ?: 'empty response')];
    }

    $resp = json_decode($raw, true);
    if (!is_array($resp)) {
        return ['success' => false, 'message' => 'Wave returned invalid JSON (HTTP ' . $httpCode . ')'];
    }

    if (!empty($resp['wave_launch_url'])) {
        return [
            'success'      => true,
            'redirect_url' => $resp['wave_launch_url'],
            'session_id'   => $resp['id'] ?? '',
            'reference'    => $ref,
        ];
    }

    $errMsg = $resp['message'] ?? ($resp['error'] ?? 'Unknown error from Wave');
    return ['success' => false, 'message' => 'Wave: ' . $errMsg, 'raw' => $resp];
}

function provider_parse_callback(array $payload): array
{
    $status = strtolower($payload['checkout_status'] ?? ($payload['status'] ?? 'unknown'));
    return [
        'provider'       => 'wave',
        'transaction_id' => $payload['id'] ?? ($payload['transaction_id'] ?? ''),
        'reference'      => $payload['client_reference'] ?? ($payload['payment_reason'] ?? ''),
        'amount'         => $payload['amount'] ?? '',
        'currency'       => $payload['currency'] ?? 'XOF',
        'phone'          => $payload['client_phone'] ?? ($payload['phone'] ?? ''),
        'status'         => ($status === 'succeeded') ? 'success' : (($status === 'processing' || $status === 'open') ? 'pending' : 'failed'),
        'raw'            => $payload,
        'logged_at'      => date('c'),
    ];
}

function provider_check_status(string $ref): array
{
    return ['success' => false, 'message' => 'Wave status check is not available via phone lookup — use the Wave Business dashboard or webhook callback.'];
}
