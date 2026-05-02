<?php
/**
 * providers/tinypesa.php — TinyPesa STK Push provider
 * Set PAYMENT_PROVIDER = 'tinypesa' in config.php to use this provider.
 * Required constants: TINYPESA_API_KEY, TINYPESA_URL
 */

function provider_flow(): string {
    return 'stk';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    if (!defined('TINYPESA_API_KEY') || !defined('TINYPESA_URL')) {
        return ['success' => false, 'message' => 'TinyPesa is not configured. Check config.php.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $body = http_build_query([
        'amount'     => (int)$amount,
        'msisdn'     => $phone,
        'account_no' => $reference,
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => TINYPESA_URL,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'ApiKey: '     . TINYPESA_API_KEY,
            'Content-Type: application/x-www-form-urlencoded',
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

    if ($httpCode === 200) {
        return ['success' => true, 'message' => 'STK Push sent! Check your phone.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $errMsg = $result['message'] ?? $result['detail'] ?? 'Unexpected error from TinyPesa.';
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
}

function provider_parse_callback(array $raw): array {
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'provider'  => 'tinypesa',
    ];

    $entry['currency'] = 'KES';

    if (isset($raw['Body']['stkCallback'])) {
        $cb = $raw['Body']['stkCallback'];
        $entry['MerchantRequestID'] = $cb['MerchantRequestID'] ?? null;
        $entry['CheckoutRequestID'] = $cb['CheckoutRequestID'] ?? null;
        $entry['ResultCode']        = $cb['ResultCode']        ?? null;
        $entry['ResultDesc']        = $cb['ResultDesc']        ?? null;

        if (isset($cb['CallbackMetadata']['Item']) && is_array($cb['CallbackMetadata']['Item'])) {
            foreach ($cb['CallbackMetadata']['Item'] as $item) {
                $entry[$item['Name']] = $item['Value'] ?? null;
            }
        }
    } else {
        $entry['ResultCode'] = null;
        $entry['ResultDesc'] = 'Unknown callback format';
        $entry['raw']        = $raw;
    }

    return $entry;
}

function provider_check_status(array $entry, string $phone): bool {
    return (string)($entry['PhoneNumber'] ?? '') === $phone && ($entry['ResultCode'] ?? null) === 0;
}
