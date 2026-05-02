<?php
/**
 * providers/orangemoney.php — Orange Money hosted checkout provider
 * Set PAYMENT_PROVIDER = 'orangemoney' in config.php to use this provider.
 *
 * Required constants: ORANGE_CLIENT_ID, ORANGE_CLIENT_SECRET,
 *                     ORANGE_MERCHANT_KEY, ORANGE_NOTIFY_URL, ORANGE_RETURN_URL
 * Optional:           ORANGE_COUNTRY  (2-letter code, default 'ci')
 *                     ORANGE_CURRENCY (default 'XOF')
 *
 * Supported countries: Côte d'Ivoire (ci), Senegal (sn), Mali (ml),
 *   Burkina Faso (bf), Guinea (gn), Guinea-Bissau (gw), Cameroon (cm),
 *   Madagascar (mg), Sierra Leone (sle), Liberia (lr),
 *   Morocco (ma — Maroc Telecom), Tunisia (tn), Jordan (jo)
 *
 * API docs:  https://developer.orange.com/apis/orange-money-webpay
 * Dashboard: https://developer.orange.com/myapps
 * Webhook endpoint: callback_orangemoney.php
 */

function provider_flow(): string {
    return 'redirect';
}

function _orange_access_token(): string {
    $credentials = base64_encode(ORANGE_CLIENT_ID . ':' . ORANGE_CLIENT_SECRET);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.orange.com/oauth/v3/token',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp ?: '{}', true) ?: [];
    return $data['access_token'] ?? '';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['ORANGE_CLIENT_ID', 'ORANGE_CLIENT_SECRET', 'ORANGE_MERCHANT_KEY', 'ORANGE_NOTIFY_URL', 'ORANGE_RETURN_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Orange Money is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $country   = defined('ORANGE_COUNTRY')  ? strtolower(ORANGE_COUNTRY)  : 'ci';
    $currency  = defined('ORANGE_CURRENCY') ? ORANGE_CURRENCY : 'XOF';
    $orderId   = $reference . '-' . time();
    $amountInt = (int)round($amount);

    $token = _orange_access_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Orange Money: failed to get access token. Check ORANGE_CLIENT_ID / ORANGE_CLIENT_SECRET.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $payload = json_encode([
        'merchant_key' => ORANGE_MERCHANT_KEY,
        'currency'     => $currency,
        'order_id'     => $orderId,
        'amount'       => $amountInt,
        'return_url'   => ORANGE_RETURN_URL,
        'cancel_url'   => ORANGE_RETURN_URL,
        'notif_url'    => ORANGE_NOTIFY_URL,
        'reference'    => $reference,
        'lang'         => 'en',
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => "https://api.orange.com/orange-money-webpay/{$country}/v1/webpayment",
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
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

    $result = json_decode($response, true);
    $payUrl = $result['payment_url'] ?? '';

    if ($httpCode === 200 && $payUrl) {
        return ['success' => true, 'message' => 'Redirecting to Orange Money payment page…', 'reference' => $reference, 'redirect_url' => $payUrl, 'flow' => 'redirect'];
    }

    $errMsg = $result['message'] ?? $result['error'] ?? ('Unexpected error from Orange Money. HTTP: ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_orangemoney.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $status     = strtoupper($raw['status'] ?? ($raw['transaction_status'] ?? ''));
    $resultCode = ($status === 'SUCCESS' || $status === 'INITIATED') ? 0 : 1;
    $amount     = isset($raw['amount']) ? (float)$raw['amount'] : 0.0;
    $phone      = $raw['msisdn'] ?? ($raw['customer_msisdn'] ?? '');
    $transId    = $raw['txnid'] ?? ($raw['transaction_id'] ?? '');
    $ref        = $raw['reference'] ?? ($raw['order_id'] ?? '');

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'orangemoney',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ('Payment ' . strtolower($status ?: 'failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $transId,
        'TransactionDate'    => date('Y-m-d H:i:s'),
        'Reference'          => $ref,
        'Currency'           => $raw['currency'] ?? '',
        'Country'            => strtoupper(defined('ORANGE_COUNTRY') ? ORANGE_COUNTRY : ''),
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
