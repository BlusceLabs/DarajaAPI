<?php
/**
 * providers/cellulant.php — Cellulant Tingg hosted checkout provider
 * Set PAYMENT_PROVIDER = 'cellulant' in config.php to use this provider.
 *
 * Required constants: CELLULANT_API_KEY, CELLULANT_CLIENT_ID, CELLULANT_CLIENT_SECRET,
 *                     CELLULANT_SERVICE_CODE, CELLULANT_CALLBACK_URL
 * Optional:           CELLULANT_CURRENCY (default 'KES')
 *                     CELLULANT_COUNTRY  (default 'KE')
 *
 * Supported countries (18+): Kenya, Uganda, Tanzania, Rwanda, Nigeria, Ghana,
 *   Zambia, Zimbabwe, Malawi, Mozambique, Ethiopia, Côte d'Ivoire, Cameroon,
 *   Senegal, South Africa, DRC, Madagascar, Botswana, Angola
 *
 * Covers mobile money, card, and bank transfer in a single checkout.
 *
 * API docs:  https://developer.tingg.africa
 * Dashboard: https://app.tingg.africa
 * Webhook endpoint: callback_cellulant.php
 */

function provider_flow(): string {
    return 'redirect';
}

function _cellulant_access_token(): string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://auth.tingg.africa/oauth/token',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'grant_type'    => 'client_credentials',
            'client_id'     => CELLULANT_CLIENT_ID,
            'client_secret' => CELLULANT_CLIENT_SECRET,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp ?: '{}', true) ?: [];
    return $data['access_token'] ?? '';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['CELLULANT_API_KEY', 'CELLULANT_CLIENT_ID', 'CELLULANT_CLIENT_SECRET', 'CELLULANT_SERVICE_CODE', 'CELLULANT_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Cellulant is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $currency = defined('CELLULANT_CURRENCY') ? CELLULANT_CURRENCY : 'KES';
    $country  = defined('CELLULANT_COUNTRY')  ? CELLULANT_COUNTRY  : 'KE';
    $txId     = $reference . '-' . time();
    $dueDate  = date('Y-m-d H:i:s', strtotime('+2 hours'));

    $token = _cellulant_access_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Cellulant: failed to get access token. Check CELLULANT_CLIENT_ID / CELLULANT_CLIENT_SECRET.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $payload = json_encode([
        'merchantTransactionID' => $txId,
        'requestAmount'         => $amount,
        'currencyCode'          => $currency,
        'accountNumber'         => $phone ?: '000',
        'serviceCode'           => CELLULANT_SERVICE_CODE,
        'dueDate'               => $dueDate,
        'requestDescription'    => 'Payment for ' . $reference,
        'countryCode'           => $country,
        'languageCode'          => 'EN',
        'payerClientCode'       => $phone ?: '000',
        'callbackUrl'           => CELLULANT_CALLBACK_URL,
        'successRedirectUrl'    => CELLULANT_CALLBACK_URL,
        'failRedirectUrl'       => CELLULANT_CALLBACK_URL,
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://online.tingg.africa/v2/express/checkout',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'API-Key: ' . CELLULANT_API_KEY,
            'Authorization: Bearer ' . $token,
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

    $result      = json_decode($response, true);
    $checkoutUrl = $result['data']['checkoutURL'] ?? '';

    if ($checkoutUrl) {
        return ['success' => true, 'message' => 'Redirecting to Cellulant Tingg checkout…', 'reference' => $reference, 'redirect_url' => $checkoutUrl, 'flow' => 'redirect'];
    }

    $errMsg = $result['status'] ?? $result['message'] ?? ('Unexpected error from Cellulant. HTTP: ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_cellulant.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $statusCode = (int)($raw['requestStatusCode'] ?? ($raw['statusCode'] ?? 1));
    // Tingg status 178 = PAID, 200 = generic success
    $resultCode = ($statusCode === 178 || $statusCode === 200) ? 0 : 1;
    $amount     = isset($raw['requestAmount']) ? (float)$raw['requestAmount'] : 0.0;
    $phone      = $raw['accountNumber'] ?? ($raw['payerClientCode'] ?? '');
    $txId       = $raw['merchantTransactionID'] ?? ($raw['checkoutRequestID'] ?? '');
    $ref        = $raw['merchantTransactionID'] ?? '';

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'cellulant',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ($raw['requestStatusDescription'] ?? 'Payment failed'),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $txId,
        'TransactionDate'    => date('Y-m-d H:i:s'),
        'Reference'          => $ref,
        'Currency'           => $raw['currencyCode'] ?? '',
        'Country'            => $raw['countryCode'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
