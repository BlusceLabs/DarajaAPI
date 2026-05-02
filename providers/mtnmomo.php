<?php
/**
 * providers/mtnmomo.php — MTN Mobile Money (MoMo) Collections API
 * Set PAYMENT_PROVIDER = 'mtnmomo' in config.php to use this provider.
 * Required constants: MTNMOMO_SUBSCRIPTION_KEY, MTNMOMO_API_USER,
 *                     MTNMOMO_API_KEY, MTNMOMO_CALLBACK_URL
 * Optional:           MTNMOMO_ENV      ('sandbox' | 'production', default 'sandbox')
 *                     MTNMOMO_CURRENCY (default 'UGX' — Uganda; use 'GHS' for Ghana, etc.)
 * Supported countries: Ghana, Uganda, Côte d'Ivoire, Cameroon, Zambia, Rwanda, and more.
 *
 * Flow: STK-style — sends a payment prompt to the customer's MTN phone.
 * Webhook endpoint: callback_mtnmomo.php
 */

function provider_flow(): string {
    return 'stk';
}

function _mtnmomo_base_url(): string {
    $env = defined('MTNMOMO_ENV') ? MTNMOMO_ENV : 'sandbox';
    return $env === 'production'
        ? 'https://proxy.momoapi.mtn.com'
        : 'https://sandbox.momodeveloper.mtn.com';
}

function _mtnmomo_get_token(): string|false {
    $user   = defined('MTNMOMO_API_USER') ? MTNMOMO_API_USER : '';
    $key    = defined('MTNMOMO_API_KEY')  ? MTNMOMO_API_KEY  : '';
    $subKey = defined('MTNMOMO_SUBSCRIPTION_KEY') ? MTNMOMO_SUBSCRIPTION_KEY : '';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _mtnmomo_base_url() . '/collection/token/',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERPWD        => $user . ':' . $key,
        CURLOPT_HTTPHEADER     => [
            'Ocp-Apim-Subscription-Key: ' . $subKey,
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
    $required = ['MTNMOMO_SUBSCRIPTION_KEY', 'MTNMOMO_API_USER', 'MTNMOMO_API_KEY', 'MTNMOMO_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'MTN MoMo is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
        }
    }

    $token = _mtnmomo_get_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Could not authenticate with MTN MoMo. Check API credentials.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $env        = defined('MTNMOMO_ENV') ? MTNMOMO_ENV : 'sandbox';
    $currency   = defined('MTNMOMO_CURRENCY') ? MTNMOMO_CURRENCY : 'UGX';
    $externalId = $reference . '-' . time();
    $requestId  = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

    // Strip leading + or country code for MTN (expects local format or MSISDN without +)
    $msisdn = preg_replace('/^\+/', '', $phone);

    $payload = json_encode([
        'amount'     => (string)(int)$amount,
        'currency'   => $currency,
        'externalId' => $externalId,
        'payer'      => ['partyIdType' => 'MSISDN', 'partyId' => $msisdn],
        'payerMessage' => 'Payment for ' . $reference,
        'payeeNote'    => $reference,
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _mtnmomo_base_url() . '/collection/v1_0/requesttopay',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '          . $token,
            'X-Reference-Id: '                . $requestId,
            'X-Callback-Url: '                . MTNMOMO_CALLBACK_URL,
            'X-Target-Environment: '          . $env,
            'Ocp-Apim-Subscription-Key: '     . MTNMOMO_SUBSCRIPTION_KEY,
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Connection error: ' . $curlErr, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    if ($httpCode === 202) {
        return ['success' => true, 'message' => 'MTN MoMo payment prompt sent! Approve on your phone.', 'reference' => $requestId, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $result = json_decode($response, true);
    $errMsg = $result['message'] ?? ('Unexpected error from MTN MoMo. HTTP ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
}

/**
 * Called from callback_mtnmomo.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $resultCode = 0;
    $status     = strtolower($raw['status'] ?? '');

    if ($status !== 'successful') {
        $resultCode = 1;
    }

    $phone = $raw['payer']['partyId'] ?? '';
    if ($phone && !str_starts_with($phone, '254')) {
        // Normalise to 254XXXXXXXXX if possible (Kenya)
        $stripped = preg_replace('/^(\+254|254|0)/', '', $phone);
        $phone    = '254' . $stripped;
    }

    $amount = isset($raw['amount']) ? (float)$raw['amount'] : 0.0;

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'mtnmomo',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ('Payment ' . ucfirst($status ?: 'failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $raw['financialTransactionId'] ?? ($raw['externalId'] ?? ''),
        'TransactionDate'    => date('Y-m-d H:i:s'),
        'Reference'          => $raw['externalId'] ?? '',
        'Currency'           => $raw['currency'] ?? '',
        'MoMoReason'         => $raw['reason'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
