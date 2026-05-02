<?php
/**
 * providers/paymob.php — Paymob hosted checkout provider
 * Set PAYMENT_PROVIDER = 'paymob' in config.php to use this provider.
 *
 * Required constants: PAYMOB_API_KEY, PAYMOB_INTEGRATION_ID, PAYMOB_IFRAME_ID
 * Optional:           PAYMOB_CURRENCY    (default 'EGP')
 *                     PAYMOB_HMAC_SECRET (for webhook signature verification)
 *                     PAYMOB_COUNTRY     (default 'EG' — ISO 3166-1 alpha-2)
 *
 * Supported countries: Egypt, Morocco, Pakistan, UAE, Saudi Arabia, Oman, Kuwait
 *
 * Flow: Auth token → Order → Payment key → iframe redirect
 * API docs:  https://docs.paymob.com
 * Dashboard: https://accept.paymob.com/portal2/en/settings
 * Webhook endpoint: callback_paymob.php
 */

function provider_flow(): string {
    return 'redirect';
}

function _paymob_post(string $url, array $data, string $authToken = ''): array {
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($authToken) {
        $headers[] = 'Authorization: Bearer ' . $authToken;
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp ?: '{}', true) ?: [];
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['PAYMOB_API_KEY', 'PAYMOB_INTEGRATION_ID', 'PAYMOB_IFRAME_ID'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Paymob is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $currency    = defined('PAYMOB_CURRENCY') ? PAYMOB_CURRENCY : 'EGP';
    $country     = defined('PAYMOB_COUNTRY')  ? PAYMOB_COUNTRY  : 'EG';
    $amountCents = (int)round($amount * 100); // Paymob uses smallest currency unit

    // Step 1 — Authentication
    $authResp = _paymob_post('https://accept.paymob.com/api/auth/tokens', ['api_key' => PAYMOB_API_KEY]);
    if (empty($authResp['token'])) {
        return ['success' => false, 'message' => 'Paymob auth failed: ' . ($authResp['detail'] ?? 'Unknown error'), 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }
    $authToken = $authResp['token'];

    // Step 2 — Create order
    $orderResp = _paymob_post('https://accept.paymob.com/api/ecommerce/orders', [
        'auth_token'        => $authToken,
        'delivery_needed'   => false,
        'amount_cents'      => $amountCents,
        'currency'          => $currency,
        'merchant_order_id' => $reference . '-' . time(),
        'items'             => [],
    ]);
    if (empty($orderResp['id'])) {
        return ['success' => false, 'message' => 'Paymob order creation failed.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }
    $orderId = $orderResp['id'];

    // Step 3 — Payment key
    $keyResp = _paymob_post('https://accept.paymob.com/api/acceptance/payment_keys', [
        'auth_token'     => $authToken,
        'amount_cents'   => $amountCents,
        'expiration'     => 3600,
        'order_id'       => $orderId,
        'currency'       => $currency,
        'integration_id' => (int)PAYMOB_INTEGRATION_ID,
        'billing_data'   => [
            'apartment'       => 'NA', 'email'           => 'customer@example.com',
            'floor'           => 'NA', 'first_name'      => 'Customer',
            'street'          => 'NA', 'building'        => 'NA',
            'phone_number'    => $phone ?: ('+' . $country . '000000000'),
            'shipping_method' => 'NA', 'postal_code'     => 'NA',
            'city'            => 'NA', 'country'         => $country,
            'last_name'       => substr($reference, 0, 10), 'state' => 'NA',
        ],
    ]);
    if (empty($keyResp['token'])) {
        return ['success' => false, 'message' => 'Paymob payment key generation failed.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $paymentToken = $keyResp['token'];
    $iframeId     = PAYMOB_IFRAME_ID;
    $redirectUrl  = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";

    return ['success' => true, 'message' => 'Redirecting to Paymob payment page…', 'reference' => $reference, 'redirect_url' => $redirectUrl, 'flow' => 'redirect'];
}

/**
 * Called from callback_paymob.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $obj        = $raw['obj'] ?? $raw;
    $success    = filter_var($obj['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $resultCode = $success ? 0 : 1;
    $amount     = isset($obj['amount_cents']) ? round((float)$obj['amount_cents'] / 100, 2) : 0.0;
    $phone      = $obj['phone_number'] ?? ($obj['billing_data']['phone_number'] ?? '');
    $transId    = (string)($obj['id'] ?? '');
    $orderRef   = $obj['merchant_order_id'] ?? ($obj['order']['merchant_order_id'] ?? '');

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'paymob',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $success ? 'Payment successful' : ($obj['data']['message'] ?? 'Payment failed'),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $transId,
        'TransactionDate'    => date('Y-m-d H:i:s', strtotime($obj['created_at'] ?? 'now')),
        'Reference'          => (string)$orderRef,
        'Currency'           => $obj['currency'] ?? '',
        'Channel'            => $obj['source_data']['type'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
