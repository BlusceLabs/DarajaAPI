<?php
/**
 * providers/paystack.php — Paystack hosted checkout provider
 * Set PAYMENT_PROVIDER = 'paystack' in config.php to use this provider.
 * Required constants: PAYSTACK_SECRET_KEY, PAYSTACK_CALLBACK_URL
 * Optional:           PAYSTACK_CURRENCY (default 'KES')
 * Supported countries: Nigeria, Ghana, Kenya, South Africa, Côte d'Ivoire, Egypt
 *
 * Webhook endpoint: callback_paystack.php
 * Webhook event:    charge.success
 */

function provider_flow(): string {
    return 'redirect';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['PAYSTACK_SECRET_KEY', 'PAYSTACK_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Paystack is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $currency  = defined('PAYSTACK_CURRENCY') ? PAYSTACK_CURRENCY : 'KES';
    $amountKob = (int)round($amount * 100); // Paystack uses smallest currency unit (kobo/pesewas/cents)

    $payload = json_encode([
        'reference'    => $reference . '-' . time(),
        'amount'       => $amountKob,
        'currency'     => $currency,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'metadata'     => [
            'phone'          => $phone,
            'custom_fields'  => [
                ['display_name' => 'Phone', 'variable_name' => 'phone', 'value' => $phone],
            ],
        ],
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.paystack.co/transaction/initialize',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
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

    if ($httpCode === 200 && ($result['status'] ?? false) && isset($result['data']['authorization_url'])) {
        return ['success' => true, 'message' => 'Redirecting to Paystack payment page…', 'reference' => $reference, 'redirect_url' => $result['data']['authorization_url'], 'flow' => 'redirect'];
    }

    $errMsg = $result['message'] ?? 'Unexpected error from Paystack.';
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_paystack.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $resultCode = 0;
    $event      = $raw['event'] ?? '';
    $data       = $raw['data'] ?? [];

    if ($event !== 'charge.success' || ($data['status'] ?? '') !== 'success') {
        $resultCode = 1;
    }

    $phone     = '';
    $metaPhone = '';
    if (isset($data['metadata']['custom_fields']) && is_array($data['metadata']['custom_fields'])) {
        foreach ($data['metadata']['custom_fields'] as $field) {
            if (($field['variable_name'] ?? '') === 'phone') {
                $metaPhone = (string)($field['value'] ?? '');
                break;
            }
        }
    }
    if (!$metaPhone && isset($data['metadata']['phone'])) {
        $metaPhone = (string)$data['metadata']['phone'];
    }
    if ($metaPhone) {
        $stripped = preg_replace('/^(\+254|254|0)/', '', $metaPhone);
        $phone    = '254' . $stripped;
    }

    $amount = isset($data['amount']) ? round((float)$data['amount'] / 100, 2) : 0.0; // convert from kobo

    $ts = isset($data['paid_at']) ? date('Y-m-d H:i:s', strtotime($data['paid_at'])) : date('Y-m-d H:i:s');

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'paystack',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : 'Payment ' . ($data['status'] ?? 'failed'),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $data['reference'] ?? ($data['id'] ?? ''),
        'TransactionDate'    => $ts,
        'Reference'          => $data['reference'] ?? '',
        'Channel'            => $data['channel'] ?? '',
        'Currency'           => $data['currency'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
