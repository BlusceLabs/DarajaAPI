<?php
/**
 * providers/airtelmoney.php — Airtel Money Africa Collections API
 * Set PAYMENT_PROVIDER = 'airtelmoney' in config.php to use this provider.
 * Required constants: AIRTEL_CLIENT_ID, AIRTEL_CLIENT_SECRET, AIRTEL_CALLBACK_URL
 * Optional:           AIRTEL_ENV      ('sandbox' | 'production', default 'sandbox')
 *                     AIRTEL_CURRENCY (default 'KES')
 *                     AIRTEL_COUNTRY  (default 'KE' — Kenya; 'UG', 'TZ', 'RW', 'ZM', 'MG', 'MW', 'CG', 'CD', 'NE', 'SL')
 * Supported countries: Kenya, Uganda, Tanzania, Rwanda, Zambia, Madagascar, Malawi,
 *                      Republic of Congo, DRC, Niger, Sierra Leone.
 *
 * Flow: STK-style — sends a USSD payment prompt to the customer's Airtel phone.
 * Webhook endpoint: callback_airtelmoney.php
 */

function provider_flow(): string {
    return 'stk';
}

function _airtel_base_url(): string {
    $env = defined('AIRTEL_ENV') ? AIRTEL_ENV : 'sandbox';
    return $env === 'production'
        ? 'https://openapi.airtel.africa'
        : 'https://openapiuat.airtel.africa';
}

function _airtel_get_token(): string|false {
    $clientId     = defined('AIRTEL_CLIENT_ID')     ? AIRTEL_CLIENT_ID     : '';
    $clientSecret = defined('AIRTEL_CLIENT_SECRET') ? AIRTEL_CLIENT_SECRET : '';

    $payload = json_encode([
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'grant_type'    => 'client_credentials',
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _airtel_base_url() . '/auth/oauth2/token',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$response) return false;

    $data = json_decode($response, true);
    return $data['access_token'] ?? false;
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['AIRTEL_CLIENT_ID', 'AIRTEL_CLIENT_SECRET', 'AIRTEL_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Airtel Money is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
        }
    }

    $token = _airtel_get_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Could not authenticate with Airtel Money. Check AIRTEL_CLIENT_ID and AIRTEL_CLIENT_SECRET.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $currency   = defined('AIRTEL_CURRENCY') ? AIRTEL_CURRENCY : 'KES';
    $country    = defined('AIRTEL_COUNTRY')  ? AIRTEL_COUNTRY  : 'KE';
    $txId       = $reference . '-' . time();

    // Airtel expects the MSISDN without leading + but with country code digits
    $msisdn = preg_replace('/^\+/', '', $phone);

    $payload = json_encode([
        'reference' => $reference,
        'subscriber' => [
            'country' => $country,
            'currency' => $currency,
            'msisdn'  => $msisdn,
        ],
        'transaction' => [
            'amount'   => (string)(int)$amount,
            'country'  => $country,
            'currency' => $currency,
            'id'       => $txId,
        ],
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _airtel_base_url() . '/merchant/v1/payments/',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'X-Country: '           . $country,
            'X-Currency: '          . $currency,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Connection error: ' . $curlErr, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $result = json_decode($response, true);
    $status = strtolower($result['status']['code'] ?? $result['status']['message'] ?? '');

    if ($httpCode === 200 && in_array($status, ['dp00800001006', '200', 'success'], true)) {
        return ['success' => true, 'message' => 'Airtel Money prompt sent! Approve on your phone.', 'reference' => $txId, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $errMsg = $result['status']['message'] ?? ('Unexpected error from Airtel Money. HTTP ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
}

/**
 * Called from callback_airtelmoney.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $resultCode  = 0;
    $transaction = $raw['transaction'] ?? [];
    $statusCode  = strtolower($transaction['status_code'] ?? $transaction['status'] ?? '');

    if (!in_array($statusCode, ['ts', 'success', 'successful', 'completed'], true)) {
        $resultCode = 1;
    }

    $msisdn = $transaction['msisdn'] ?? ($raw['msisdn'] ?? '');
    $phone  = '';
    if ($msisdn) {
        $stripped = preg_replace('/^(\+254|254|0)/', '', $msisdn);
        $phone    = '254' . $stripped;
    }

    $amount = isset($transaction['amount']) ? (float)$transaction['amount'] : 0.0;

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'airtelmoney',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ('Transaction ' . ucfirst($statusCode ?: 'failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $transaction['airtel_money_id'] ?? ($transaction['id'] ?? ''),
        'TransactionDate'    => date('Y-m-d H:i:s'),
        'Reference'          => $transaction['id'] ?? '',
        'currency'           => $transaction['currency'] ?: (defined('AIRTEL_CURRENCY') ? AIRTEL_CURRENCY : 'KES'),
        'AirtelTxId'         => $transaction['airtel_money_id'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
