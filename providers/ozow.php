<?php
/**
 * providers/ozow.php — Ozow instant EFT provider (South Africa)
 * Set PAYMENT_PROVIDER = 'ozow' in config.php to use this provider.
 * Required constants: OZOW_SITE_CODE, OZOW_PRIVATE_KEY, OZOW_API_KEY,
 *                     OZOW_CANCEL_URL, OZOW_ERROR_URL, OZOW_SUCCESS_URL, OZOW_NOTIFY_URL
 * Supported countries: South Africa (ZAR only — instant EFT from any SA bank)
 *
 * Flow: Redirect — customer is sent to Ozow's hosted payment page.
 * Webhook endpoint: callback_ozow.php
 * Docs: https://ozow.com/integrations/
 */

function provider_flow(): string {
    return 'redirect';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['OZOW_SITE_CODE', 'OZOW_PRIVATE_KEY', 'OZOW_API_KEY',
                 'OZOW_CANCEL_URL', 'OZOW_ERROR_URL', 'OZOW_SUCCESS_URL', 'OZOW_NOTIFY_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Ozow is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $siteCode     = OZOW_SITE_CODE;
    $countryCode  = 'ZA';
    $currencyCode = 'ZAR';
    $amount       = number_format($amount, 2, '.', '');
    $transRef     = $reference . '-' . time();
    $bankRef      = $reference;
    $optional1    = $phone;
    $isTest       = (defined('OZOW_TEST') && OZOW_TEST) ? 'true' : 'false';

    // Build hash input: SiteCode+CountryCode+CurrencyCode+Amount+TransactionReference+BankReference+Optional1+CancelUrl+ErrorUrl+SuccessUrl+NotifyUrl+IsTest+PrivateKey
    $hashInput = strtolower(
        $siteCode . $countryCode . $currencyCode . $amount .
        $transRef . $bankRef . $optional1 .
        OZOW_CANCEL_URL . OZOW_ERROR_URL . OZOW_SUCCESS_URL . OZOW_NOTIFY_URL .
        $isTest . OZOW_PRIVATE_KEY
    );
    $hashCheck = hash('sha512', $hashInput);

    $payload = [
        'SiteCode'             => $siteCode,
        'CountryCode'          => $countryCode,
        'CurrencyCode'         => $currencyCode,
        'Amount'               => $amount,
        'TransactionReference' => $transRef,
        'BankReference'        => $bankRef,
        'Optional1'            => $optional1,
        'CancelUrl'            => OZOW_CANCEL_URL,
        'ErrorUrl'             => OZOW_ERROR_URL,
        'SuccessUrl'           => OZOW_SUCCESS_URL,
        'NotifyUrl'            => OZOW_NOTIFY_URL,
        'IsTest'               => $isTest,
        'HashCheck'            => $hashCheck,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.ozow.com/postpaymentrequest',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'ApiKey: '         . OZOW_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Connection error: ' . $curlErr, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $result = json_decode($response, true);

    if ($httpCode === 200 && isset($result['url'])) {
        return ['success' => true, 'message' => 'Redirecting to Ozow payment page…', 'reference' => $transRef, 'redirect_url' => $result['url'], 'flow' => 'redirect'];
    }

    $errMsg = $result['message'] ?? $result['errorMessage'] ?? ('Unexpected error from Ozow. HTTP ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_ozow.php with the POST data from Ozow's notify/success URL.
 * Hash verification is done in callback_ozow.php before this function is called.
 */
function provider_parse_callback(array $raw): array {
    $resultCode = 0;
    $status     = strtolower($raw['Status'] ?? '');

    if ($status !== 'complete') {
        $resultCode = 1;
    }

    $phone = $raw['Optional1'] ?? '';
    if ($phone) {
        $stripped = preg_replace('/^(\+27|27|0)/', '', $phone);
        $phone    = '27' . $stripped; // South Africa country code
    }

    $amount = isset($raw['Amount']) ? (float)$raw['Amount'] : 0.0;

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'ozow',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ('Payment ' . ucfirst($status ?: 'failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'currency'           => 'ZAR',
        'MpesaReceiptNumber' => $raw['TransactionId'] ?? '',
        'TransactionDate'    => date('Y-m-d H:i:s'),
        'Reference'          => $raw['TransactionReference'] ?? '',
        'BankRef'            => $raw['BankRef'] ?? '',
        'SubStatus'          => $raw['SubStatus'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
