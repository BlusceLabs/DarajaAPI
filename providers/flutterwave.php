<?php
/**
 * providers/flutterwave.php — Flutterwave hosted checkout provider
 * Set PAYMENT_PROVIDER = 'flutterwave' in config.php to use this provider.
 * Required constants: FLW_SECRET_KEY, FLW_PUBLIC_KEY, FLW_REDIRECT_URL
 * Optional:           FLW_SECRET_HASH (for webhook verification), FLW_LOGO_URL
 */

function provider_flow(): string {
    return 'redirect';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['FLW_SECRET_KEY', 'FLW_PUBLIC_KEY', 'FLW_REDIRECT_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Flutterwave is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $txRef = $reference . '-' . time();

    $payload = [
        'tx_ref'          => $txRef,
        'amount'          => $amount,
        'currency'        => 'KES',
        'redirect_url'    => FLW_REDIRECT_URL,
        'customer'        => [
            'phone_number' => $phone,
        ],
        'customizations'  => [
            'title' => 'Payment',
            'description' => 'Payment for ' . $reference,
        ],
        'payment_options' => 'mpesa,card',
    ];

    if (defined('FLW_LOGO_URL') && FLW_LOGO_URL) {
        $payload['customizations']['logo'] = FLW_LOGO_URL;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.flutterwave.com/v3/payments',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . FLW_SECRET_KEY,
            'Content-Type: application/json',
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

    if ($httpCode === 200 && ($result['status'] ?? '') === 'success' && isset($result['data']['link'])) {
        return ['success' => true, 'message' => 'Redirecting to Flutterwave payment page…', 'reference' => $reference, 'redirect_url' => $result['data']['link'], 'flow' => 'redirect'];
    }

    $errMsg = $result['message'] ?? 'Unexpected error from Flutterwave.';
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_flutterwave.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $resultCode = 0;
    $eventType  = $raw['event'] ?? '';
    $data       = $raw['data'] ?? [];

    if ($eventType !== 'charge.completed' || ($data['status'] ?? '') !== 'successful') {
        $resultCode = 1;
    }

    $phone  = $data['customer']['phone_number'] ?? '';
    $amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;

    // Normalise phone to 254XXXXXXXXX
    if ($phone) {
        $stripped = preg_replace('/^(\+254|254|0)/', '', $phone);
        $phone    = '254' . $stripped;
    }

    $ts = isset($data['created_at']) ? date('Y-m-d H:i:s', strtotime($data['created_at'])) : date('Y-m-d H:i:s');

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'flutterwave',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : 'Payment ' . ($data['status'] ?? 'failed'),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'currency'           => $data['currency'] ?? (defined('FLW_CURRENCY') ? FLW_CURRENCY : 'KES'),
        'MpesaReceiptNumber' => $data['flw_ref'] ?? ($data['id'] ?? ''),
        'TransactionDate'    => $ts,
        'Reference'          => $data['tx_ref'] ?? '',
        'FlwRef'             => $data['flw_ref'] ?? '',
        'TxRef'              => $data['tx_ref'] ?? '',
        'PaymentType'        => $data['payment_type'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
