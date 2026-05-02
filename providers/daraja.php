<?php
/**
 * providers/daraja.php — Safaricom Daraja API STK Push provider
 * Set PAYMENT_PROVIDER = 'daraja' in config.php to use this provider.
 * Required constants: DARAJA_CONSUMER_KEY, DARAJA_CONSUMER_SECRET,
 *                     DARAJA_SHORTCODE, DARAJA_PASSKEY, DARAJA_CALLBACK_URL
 * Optional:           DARAJA_ENV ('sandbox' | 'production', default 'sandbox')
 */

function provider_flow(): string {
    return 'stk';
}

function _daraja_base_url(): string {
    $env = defined('DARAJA_ENV') ? DARAJA_ENV : 'sandbox';
    return $env === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke';
}

function _daraja_token_cache_file(): string {
    $env = defined('DARAJA_ENV') ? DARAJA_ENV : 'sandbox';
    return sys_get_temp_dir() . '/daraja_oauth_token_' . $env . '.json';
}

function _daraja_get_cached_token(): string|false {
    $file = _daraja_token_cache_file();
    if (!file_exists($file)) return false;
    $raw = @file_get_contents($file);
    if ($raw === false) return false;
    $data = json_decode($raw, true);
    if (!isset($data['token'], $data['expires_at'])) return false;
    if (time() >= $data['expires_at']) return false;
    return $data['token'];
}

function _daraja_cache_token(string $token, int $ttl = 3600): void {
    $file    = _daraja_token_cache_file();
    $payload = json_encode(['token' => $token, 'expires_at' => time() + $ttl - 60]);
    if (@file_put_contents($file, $payload, LOCK_EX) !== false) {
        @chmod($file, 0600);
    }
}

function _daraja_get_token(): string|false {
    $cached = _daraja_get_cached_token();
    if ($cached !== false) return $cached;

    $key    = defined('DARAJA_CONSUMER_KEY')    ? DARAJA_CONSUMER_KEY    : '';
    $secret = defined('DARAJA_CONSUMER_SECRET') ? DARAJA_CONSUMER_SECRET : '';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _daraja_base_url() . '/oauth/v1/generate?grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERPWD        => $key . ':' . $secret,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$response) return false;

    $data  = json_decode($response, true);
    $token = $data['access_token'] ?? false;
    if ($token) _daraja_cache_token($token, (int)($data['expires_in'] ?? 3600));
    return $token;
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['DARAJA_CONSUMER_KEY', 'DARAJA_CONSUMER_SECRET', 'DARAJA_SHORTCODE', 'DARAJA_PASSKEY', 'DARAJA_CALLBACK_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Daraja is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
        }
    }

    $token = _daraja_get_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Could not authenticate with Daraja. Check DARAJA_CONSUMER_KEY and DARAJA_CONSUMER_SECRET.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $timestamp = date('YmdHis');
    $password  = base64_encode(DARAJA_SHORTCODE . DARAJA_PASSKEY . $timestamp);

    $payload = [
        'BusinessShortCode' => DARAJA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => (int)$amount,
        'PartyA'            => $phone,
        'PartyB'            => DARAJA_SHORTCODE,
        'PhoneNumber'       => $phone,
        'CallBackURL'       => DARAJA_CALLBACK_URL,
        'AccountReference'  => $reference,
        'TransactionDesc'   => 'Payment for ' . $reference,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => _daraja_base_url() . '/mpesa/stkpush/v1/processrequest',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
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

    if ($httpCode === 200 && isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
        return ['success' => true, 'message' => 'STK Push sent! Check your phone.', 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
    }

    $errMsg = $result['errorMessage'] ?? $result['ResultDesc'] ?? $result['ResponseDescription'] ?? 'Unexpected error from Daraja.';
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'stk'];
}

function provider_parse_callback(array $raw): array {
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'provider'  => 'daraja',
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
