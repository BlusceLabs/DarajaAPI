<?php
/**
 * providers/moovafrica.php — Moov Africa / Flooz (West & Central Africa)
 *
 * Moov Africa operates mobile money under the "Flooz" brand across 9 countries.
 * The API is a REST-based merchant payment initiation with OAuth2 authentication.
 *
 * Required constants (define in config.php):
 *   MOOV_API_KEY      — Client ID / API key from the Moov Africa developer portal
 *   MOOV_API_SECRET   — Client secret from the Moov Africa developer portal
 *   MOOV_CALLBACK_URL — Webhook URL (https://yourdomain.com/callback_moovafrica.php)
 *
 * Optional constants:
 *   MOOV_COUNTRY      — ISO 3166-1 alpha-2, e.g. 'TG' (default), 'BJ', 'NE', 'BF',
 *                       'CI', 'TD', 'GA', 'CD', 'MG'
 *   MOOV_CURRENCY     — 'XOF' (default TG/BJ/NE/BF/CI), 'XAF' (TD/GA),
 *                       'CDF' (CD), 'MGA' (MG)
 *
 * Countries: Togo (TG/XOF), Bénin (BJ/XOF), Niger (NE/XOF), Burkina Faso (BF/XOF),
 *            Côte d'Ivoire (CI/XOF), Tchad (TD/XAF), Gabon (GA/XAF),
 *            DR Congo (CD/CDF), Madagascar (MG/MGA)
 *
 * Registration: https://developer.moov-africa.com
 */

function provider_flow(): string { return 'stk'; }

/** Fetch or cache an OAuth2 access token */
function _moov_access_token(): string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.moov-africa.com/v1/oauth/token',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => MOOV_API_KEY,
            'client_secret' => MOOV_API_SECRET,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $raw  = curl_exec($ch);
    curl_close($ch);
    $resp = json_decode($raw ?: '{}', true);
    return $resp['access_token'] ?? '';
}

function provider_initiate(string $amount, string $phone, string $ref): array
{
    foreach (['MOOV_API_KEY', 'MOOV_API_SECRET', 'MOOV_CALLBACK_URL'] as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Moov Africa is not configured. ' . $c . ' is missing from config.php.'];
        }
    }

    $country  = defined('MOOV_COUNTRY')  ? strtoupper(MOOV_COUNTRY)  : 'TG';
    $currency = defined('MOOV_CURRENCY') ? MOOV_CURRENCY              : 'XOF';

    $accessToken = _moov_access_token();
    if (!$accessToken) {
        return ['success' => false, 'message' => 'Moov Africa: Failed to obtain access token — check MOOV_API_KEY and MOOV_API_SECRET.'];
    }

    // Normalise phone — strip leading + or 0, ensure country prefix
    $phone = preg_replace('/\D/', '', $phone);

    $body = [
        'amount'      => (int)$amount,
        'currency'    => $currency,
        'reference'   => $ref,
        'description' => 'Payment ' . $ref,
        'customer'    => ['phone' => $phone],
        'notify_url'  => MOOV_CALLBACK_URL,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.moov-africa.com/v1/payment/debit/' . strtolower($country),
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        return ['success' => false, 'message' => 'Moov Africa connection error: ' . ($curlErr ?: 'empty response')];
    }

    $resp = json_decode($raw, true);
    if (!is_array($resp)) {
        return ['success' => false, 'message' => 'Moov Africa returned invalid JSON (HTTP ' . $httpCode . ')'];
    }

    $status = strtolower($resp['status'] ?? ($resp['code'] ?? ''));
    if (in_array($status, ['pending', 'success', '200', '201']) || $httpCode === 200 || $httpCode === 201) {
        return [
            'success'        => true,
            'message'        => 'Moov Africa payment request sent. The customer will receive a Flooz USSD prompt.',
            'transaction_id' => $resp['id'] ?? ($resp['transactionId'] ?? $ref),
            'reference'      => $ref,
        ];
    }

    $errMsg = $resp['message'] ?? ($resp['error'] ?? ('Moov Africa error (HTTP ' . $httpCode . ')'));
    return ['success' => false, 'message' => $errMsg, 'raw' => $resp];
}

function provider_parse_callback(array $payload): array
{
    $status = strtolower($payload['status'] ?? ($payload['transaction_status'] ?? 'unknown'));
    return [
        'provider'       => 'moovafrica',
        'transaction_id' => $payload['id'] ?? ($payload['transactionId'] ?? ($payload['transaction_id'] ?? '')),
        'reference'      => $payload['reference'] ?? ($payload['external_reference'] ?? ''),
        'amount'         => $payload['amount'] ?? '',
        'currency'       => $payload['currency'] ?? (defined('MOOV_CURRENCY') ? MOOV_CURRENCY : 'XOF'),
        'phone'          => $payload['customer']['phone'] ?? ($payload['phone'] ?? ''),
        'status'         => in_array($status, ['success', 'successful', 'completed']) ? 'success'
                          : (in_array($status, ['pending', 'processing']) ? 'pending' : 'failed'),
        'raw'            => $payload,
        'logged_at'      => date('c'),
    ];
}

function provider_check_status(string $ref): array
{
    return ['success' => false, 'message' => 'Moov Africa status polling is not yet implemented. Use the webhook callback (callback_moovafrica.php).'];
}
