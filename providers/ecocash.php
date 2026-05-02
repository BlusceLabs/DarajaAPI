<?php
/**
 * providers/ecocash.php — Ecocash STK-push provider (Zimbabwe / Eswatini / Lesotho)
 * Set PAYMENT_PROVIDER = 'ecocash' in config.php to use this provider.
 *
 * Required constants: ECOCASH_MERCHANT_CODE, ECOCASH_MERCHANT_PIN,
 *                     ECOCASH_MERCHANT_NUMBER, ECOCASH_CALLBACK_URL
 * Optional:           ECOCASH_ENV      ('sandbox' | 'production', default 'sandbox')
 *                     ECOCASH_CURRENCY ('USD' | 'ZWL' | 'SZL' | 'LSL', default 'USD')
 *
 * Supported countries: Zimbabwe (USD/ZWL), Eswatini (SZL), Lesotho (LSL)
 *
 * API docs:  https://developer.econet.co.zw
 * Dashboard: https://merchants.ecocash.co.zw
 * Webhook endpoint: callback_ecocash.php
 */

function provider_flow(): string {
    return 'stk';
}

function _ecocash_base_url(): string {
    $env = defined('ECOCASH_ENV') ? ECOCASH_ENV : 'sandbox';
    return $env === 'production'
        ? 'https://api.econet.co.zw'
        : 'https://api.econet.co.zw'; // same host; sandbox uses test credentials
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['ECOCASH_MERCHANT_CODE', 'ECOCASH_MERCHANT_PIN', 'ECOCASH_MERCHANT_NUMBER', 'ECOCASH_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Ecocash is not fully configured. Missing: ' . $c, 'reference' => $reference, 'flow' => 'stk'];
        }
    }

    $currency   = defined('ECOCASH_CURRENCY') ? ECOCASH_CURRENCY : 'USD';
    $correlator = $reference . '-' . time();

    $payload = json_encode([
        'serverCorrelator'           => $correlator,
        'clientCorrelator'           => $correlator,
        'merchantCode'               => ECOCASH_MERCHANT_CODE,
        'merchantPin'                => ECOCASH_MERCHANT_PIN,
        'merchantNumber'             => ECOCASH_MERCHANT_NUMBER,
        'callBackURL'                => ECOCASH_CALLBACK_URL,
        'transactionOperationStatus' => 'Requested',
        'paymentAmount'              => [
            'chargedAmount' => number_format($amount, 2, '.', ''),
            'currency'      => $currency,
        ],
        'customerPhoneNumber'  => $phone,
        'transactionReference' => substr($reference, 0, 20),
        'remarks'              => 'Payment for ' . $reference,
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _ecocash_base_url() . '/mm/api/transaction/v1_1/merchantpayment',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Connection error: ' . $curlErr, 'reference' => $reference, 'flow' => 'stk'];
    }

    $result   = json_decode($response, true);
    $txStatus = strtoupper($result['transactionOperationStatus'] ?? '');

    if ($httpCode === 200 && in_array($txStatus, ['PENDING', 'COMPLETED', 'REQUESTED'], true)) {
        return ['success' => true, 'message' => 'Ecocash payment request sent. Check your phone.', 'reference' => $reference, 'flow' => 'stk'];
    }

    $errMsg = $result['Description'] ?? $result['message'] ?? ('Ecocash error. HTTP: ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'flow' => 'stk'];
}

/**
 * Called from callback_ecocash.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $status     = strtoupper($raw['transactionOperationStatus'] ?? '');
    $resultCode = $status === 'COMPLETED' ? 0 : 1;
    $amount     = (float)($raw['paymentAmount']['chargedAmount'] ?? 0);
    $phone      = $raw['customerPhoneNumber'] ?? '';
    $transId    = $raw['serverCorrelator'] ?? '';
    $ref        = $raw['transactionReference'] ?? '';

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'ecocash',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ('Transaction ' . strtolower($status ?: 'failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $transId,
        'TransactionDate'    => date('Y-m-d H:i:s'),
        'Reference'          => $ref,
        'Currency'           => $raw['paymentAmount']['currency'] ?? 'USD',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
