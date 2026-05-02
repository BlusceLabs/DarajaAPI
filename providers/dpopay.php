<?php
/**
 * providers/dpopay.php — DPO Pay (Direct Pay Online) hosted checkout provider
 * Set PAYMENT_PROVIDER = 'dpopay' in config.php to use this provider.
 * Required constants: DPO_COMPANY_TOKEN, DPO_SERVICE_TYPE, DPO_REDIRECT_URL, DPO_BACK_URL
 * Optional:           DPO_ENV ('live' | 'sandbox', default 'sandbox')
 * Supported countries: 20+ African countries including Kenya, Tanzania, Uganda, Rwanda,
 *                      Zambia, Zimbabwe, South Africa, Ghana, Nigeria, Mozambique, and more.
 *
 * API: XML-based v6 at https://secure.3gdirectpay.com/API/v6/
 * Webhook endpoint: callback_dpopay.php (redirect callback from DPO)
 */

function provider_flow(): string {
    return 'redirect';
}

function _dpo_base_url(): string {
    $env = defined('DPO_ENV') ? DPO_ENV : 'sandbox';
    // DPO uses the same endpoint for sandbox and production; sandbox uses test tokens
    return 'https://secure.3gdirectpay.com';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['DPO_COMPANY_TOKEN', 'DPO_SERVICE_TYPE', 'DPO_REDIRECT_URL', 'DPO_BACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'DPO Pay is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $currency     = defined('DPO_CURRENCY')     ? DPO_CURRENCY     : 'KES';
    $country      = defined('DPO_COUNTRY_CODE') ? DPO_COUNTRY_CODE : 'KE';
    $expiry       = date('Y/m/d H:i', strtotime('+1 hour'));

    $xml = '<?xml version="1.0" encoding="utf-8"?>' .
        '<API3G>' .
        '<CompanyToken>'    . htmlspecialchars(DPO_COMPANY_TOKEN)  . '</CompanyToken>' .
        '<Request>createToken</Request>' .
        '<Transaction>' .
            '<PaymentAmount>'    . number_format($amount, 2, '.', '') . '</PaymentAmount>' .
            '<PaymentCurrency>'  . htmlspecialchars($currency)     . '</PaymentCurrency>' .
            '<CompanyRef>'       . htmlspecialchars($reference)    . '</CompanyRef>' .
            '<RedirectURL>'      . htmlspecialchars(DPO_REDIRECT_URL) . '</RedirectURL>' .
            '<BackURL>'          . htmlspecialchars(DPO_BACK_URL)  . '</BackURL>' .
            '<CompanyRefUnique>0</CompanyRefUnique>' .
            '<PTL>60</PTL>' .
        '</Transaction>' .
        '<Services>' .
            '<Service>' .
                '<ServiceType>'    . htmlspecialchars(DPO_SERVICE_TYPE) . '</ServiceType>' .
                '<ServiceDescription>Payment for ' . htmlspecialchars($reference) . '</ServiceDescription>' .
                '<ServiceDate>'    . date('Y/m/d H:i') . '</ServiceDate>' .
            '</Service>' .
        '</Services>' .
        '</API3G>';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _dpo_base_url() . '/API/v6/',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $xml,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/xml'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Connection error: ' . $curlErr, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    libxml_use_internal_errors(true);
    $xml    = simplexml_load_string($response ?: '');
    $result = $xml ? json_decode(json_encode($xml), true) : [];

    $resultCode = (string)($result['Result'] ?? '');
    $transToken = (string)($result['TransToken'] ?? '');

    if ($resultCode === '000' && $transToken) {
        $payUrl = _dpo_base_url() . '/payv2.php?ID=' . urlencode($transToken);
        return ['success' => true, 'message' => 'Redirecting to DPO Pay payment page…', 'reference' => $reference, 'redirect_url' => $payUrl, 'flow' => 'redirect'];
    }

    $errMsg = (string)($result['ResultExplanation'] ?? ('Unexpected error from DPO Pay. Code: ' . $resultCode));
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_dpopay.php after verifying the transaction with DPO.
 * $raw should be the verified transaction data from DPO's verifyToken response.
 */
function provider_parse_callback(array $raw): array {
    $resultCode = 0;
    $result     = (string)($raw['Result'] ?? '');

    if ($result !== '000') {
        $resultCode = 1;
    }

    $phone = $raw['CustomerPhone'] ?? ($raw['phone'] ?? '');
    if ($phone) {
        $stripped = preg_replace('/^(\+254|254|0)/', '', $phone);
        $phone    = '254' . $stripped;
    }

    $amount = isset($raw['TransactionAmount']) ? (float)$raw['TransactionAmount'] : 0.0;

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'dpopay',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ((string)($raw['ResultExplanation'] ?? 'Payment failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $raw['TransactionRef'] ?? ($raw['TransToken'] ?? ''),
        'TransactionDate'    => $raw['TransactionDate'] ?? date('Y-m-d H:i:s'),
        'Reference'          => $raw['CompanyRef'] ?? '',
        'TransToken'         => $raw['TransToken'] ?? '',
        'PaymentMethod'      => $raw['PaymentType'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
