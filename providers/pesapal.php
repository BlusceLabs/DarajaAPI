<?php
/**
 * providers/pesapal.php — PesaPal V3 hosted checkout provider
 * Set PAYMENT_PROVIDER = 'pesapal' in config.php to use this provider.
 * Required constants: PESAPAL_CONSUMER_KEY, PESAPAL_CONSUMER_SECRET,
 *                     PESAPAL_IPN_URL, PESAPAL_CALLBACK_URL
 * Optional:           PESAPAL_ENV ('sandbox' | 'production', default 'sandbox')
 */

function provider_flow(): string {
    return 'redirect';
}

function _pesapal_base_url(): string {
    $env = defined('PESAPAL_ENV') ? PESAPAL_ENV : 'sandbox';
    return $env === 'production'
        ? 'https://pay.pesapal.com/v3'
        : 'https://cybqa.pesapal.com/pesapalv3';
}

function _pesapal_token_cache_file(): string {
    $env = defined('PESAPAL_ENV') ? PESAPAL_ENV : 'sandbox';
    return sys_get_temp_dir() . '/pesapal_oauth_token_' . $env . '.json';
}

function _pesapal_get_cached_token(): string|false {
    $file = _pesapal_token_cache_file();
    if (!file_exists($file)) return false;
    $raw = @file_get_contents($file);
    if ($raw === false) return false;
    $data = json_decode($raw, true);
    if (!isset($data['token'], $data['expires_at'])) return false;
    if (time() >= $data['expires_at']) return false;
    return $data['token'];
}

function _pesapal_cache_token(string $token, int $ttl = 3600): void {
    $file    = _pesapal_token_cache_file();
    $payload = json_encode(['token' => $token, 'expires_at' => time() + $ttl - 60]);
    if (@file_put_contents($file, $payload, LOCK_EX) !== false) {
        @chmod($file, 0600);
    }
}

function _pesapal_get_token(): string|false {
    $cached = _pesapal_get_cached_token();
    if ($cached !== false) return $cached;

    $key    = defined('PESAPAL_CONSUMER_KEY')    ? PESAPAL_CONSUMER_KEY    : '';
    $secret = defined('PESAPAL_CONSUMER_SECRET') ? PESAPAL_CONSUMER_SECRET : '';

    $payload = json_encode(['consumer_key' => $key, 'consumer_secret' => $secret]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _pesapal_base_url() . '/api/Auth/RequestToken',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$response) return false;

    $data  = json_decode($response, true);
    $token = $data['token'] ?? false;
    if ($token) {
        $ttl = isset($data['expiryDate'])
            ? max(0, (int)(strtotime($data['expiryDate']) - time()))
            : 3600;
        _pesapal_cache_token($token, $ttl ?: 3600);
    }
    return $token;
}

function _pesapal_register_ipn(string $token): string|false {
    if (!defined('PESAPAL_IPN_URL')) return false;

    $payload = json_encode(['url' => PESAPAL_IPN_URL, 'ipn_notification_type' => 'GET']);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _pesapal_base_url() . '/api/URLSetup/RegisterIPN',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$response) return false;

    $data = json_decode($response, true);
    return $data['ipn_id'] ?? false;
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['PESAPAL_CONSUMER_KEY', 'PESAPAL_CONSUMER_SECRET', 'PESAPAL_IPN_URL', 'PESAPAL_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'PesaPal is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $token = _pesapal_get_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Could not authenticate with PesaPal. Check credentials.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $ipnId = _pesapal_register_ipn($token);
    if (!$ipnId) {
        return ['success' => false, 'message' => 'PesaPal IPN registration failed. Check PESAPAL_IPN_URL.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $payload = json_encode([
        'id'               => $reference . '-' . time(),
        'currency'         => 'KES',
        'amount'           => $amount,
        'description'      => 'Payment for ' . $reference,
        'callback_url'     => PESAPAL_CALLBACK_URL,
        'notification_id'  => $ipnId,
        'billing_address'  => [
            'phone_number' => $phone,
        ],
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _pesapal_base_url() . '/api/Transactions/SubmitOrderRequest',
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

    if ($httpCode === 200 && isset($result['redirect_url'])) {
        return ['success' => true, 'message' => 'Redirecting to PesaPal payment page…', 'reference' => $reference, 'redirect_url' => $result['redirect_url'], 'flow' => 'redirect'];
    }

    $errMsg = $result['error']['message'] ?? $result['message'] ?? 'Unexpected error from PesaPal.';
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_pesapal.php with the result of GetTransactionStatus.
 */
function provider_parse_callback(array $raw): array {
    $resultCode = 0;
    $status     = $raw['payment_status_description'] ?? '';
    if (strtolower($status) !== 'completed') {
        $resultCode = 1;
    }

    $amount = isset($raw['amount']) ? (float)$raw['amount'] : 0.0;
    $phone  = $raw['billing_address']['phone_number'] ?? ($raw['phone_number'] ?? '');

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'pesapal',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $raw['payment_status_description'] ?? 'Unknown',
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $raw['confirmation_code'] ?? ($raw['order_tracking_id'] ?? ''),
        'TransactionDate'    => $raw['created_date'] ?? date('Y-m-d H:i:s'),
        'Reference'          => $raw['merchant_reference'] ?? '',
        'OrderTrackingId'    => $raw['order_tracking_id'] ?? '',
        'PaymentMethod'      => $raw['payment_method'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    $resultCode = $entry['ResultCode'] ?? null;

    // Primary: phone is already in the log entry — require match AND success
    if ($entryPhone) {
        return $entryPhone === $phone && $resultCode === 0;
    }

    // Fallback: phone not yet stored (edge case where parse_callback couldn't
    // retrieve it). Do a live lookup but ALWAYS verify phone from the API
    // response — never confirm based on tracking ID alone.
    $trackingId = $entry['OrderTrackingId'] ?? null;
    if ($trackingId) {
        $status = pesapal_query_transaction_status($trackingId);
        if ($status && ($status['payment_status_code'] ?? null) === '1') {
            $statusPhone = $status['phone_number'] ?? '';
            if ($statusPhone) {
                $stripped    = preg_replace('/^\+?254|^0/', '', $statusPhone);
                $normalized  = '254' . ltrim($stripped, '0');
                return $normalized === $phone;
            }
        }
    }

    return false;
}
