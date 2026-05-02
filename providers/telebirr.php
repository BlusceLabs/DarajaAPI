<?php
/**
 * providers/telebirr.php — Telebirr by Ethiotelecom (Ethiopia)
 *
 * Telebirr is Ethiopia's largest mobile wallet, operated by Ethiotelecom,
 * with 40M+ users. Payments are USSD-push / in-app — the customer approves
 * on their Telebirr app or phone. The API requires RSA signing of the payload.
 *
 * Required constants (define in config.php):
 *   TELEBIRR_APP_ID       — Application ID from the Telebirr developer portal
 *   TELEBIRR_APP_KEY      — Application key (API key)
 *   TELEBIRR_SHORT_CODE   — Merchant short code assigned by Ethiotelecom
 *   TELEBIRR_PUBLIC_KEY   — Telebirr RSA public key (PEM, from portal)
 *   TELEBIRR_NOTIFY_URL   — Webhook URL (https://yourdomain.com/callback_telebirr.php)
 *
 * Optional constants:
 *   TELEBIRR_REDIRECT_URL — Where to send customers after payment (browser flow)
 *   TELEBIRR_ENV          — 'production' (default) or 'sandbox'
 *
 * Country: Ethiopia (ET) — Currency: ETB (Ethiopian Birr)
 * Registration: https://developer.ethiotelecom.et
 */

function provider_flow(): string { return 'redirect'; }

function provider_initiate(string $amount, string $phone, string $ref): array
{
    foreach (['TELEBIRR_APP_ID', 'TELEBIRR_APP_KEY', 'TELEBIRR_SHORT_CODE', 'TELEBIRR_PUBLIC_KEY', 'TELEBIRR_NOTIFY_URL'] as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'Telebirr is not configured. ' . $c . ' is missing from config.php.'];
        }
    }

    $env     = (defined('TELEBIRR_ENV') && TELEBIRR_ENV === 'sandbox') ? 'sandbox' : 'production';
    $baseUrl = $env === 'sandbox'
        ? 'https://developerstore.ethiotelecom.et/apiaccess/payment/v1'
        : 'https://developerstore.ethiotelecom.et/apiaccess/payment/v1';

    $nonce     = bin2hex(random_bytes(8));
    $timestamp = (string)(int)(microtime(true) * 1000);
    $redirectUrl = defined('TELEBIRR_REDIRECT_URL') ? TELEBIRR_REDIRECT_URL : TELEBIRR_NOTIFY_URL;

    // Assemble the clear-text sign string (alphabetical field order required by Telebirr)
    $signStr = implode('&', [
        'appid='        . TELEBIRR_APP_ID,
        'merch_code='   . TELEBIRR_SHORT_CODE,
        'nonce='        . $nonce,
        'notifyUrl='    . TELEBIRR_NOTIFY_URL,
        'outbizno='     . $ref,
        'redirectUrl='  . $redirectUrl,
        'subject='      . 'Payment ' . $ref,
        'timestamp='    . $timestamp,
        'totalamount='  . number_format((float)$amount, 2, '.', ''),
    ]);

    // RSA-OAEP encrypt sign string with Telebirr's public key
    $pubKey = TELEBIRR_PUBLIC_KEY;
    if (strpos($pubKey, '-----BEGIN') === false) {
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($pubKey, 64, "\n") . "-----END PUBLIC KEY-----\n";
    }
    $encrypted = '';
    if (!openssl_public_encrypt($signStr, $encrypted, $pubKey, OPENSSL_PKCS1_OAEP_PADDING)) {
        return ['success' => false, 'message' => 'Telebirr: Failed to encrypt payment request — check TELEBIRR_PUBLIC_KEY.'];
    }
    $sign = base64_encode($encrypted);

    $body = [
        'appid'        => TELEBIRR_APP_ID,
        'merch_code'   => TELEBIRR_SHORT_CODE,
        'nonce'        => $nonce,
        'notifyUrl'    => TELEBIRR_NOTIFY_URL,
        'outbizno'     => $ref,
        'redirectUrl'  => $redirectUrl,
        'subject'      => 'Payment ' . $ref,
        'timestamp'    => $timestamp,
        'totalamount'  => number_format((float)$amount, 2, '.', ''),
        'sign'         => $sign,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $baseUrl . '/transaction/create',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_HTTPHEADER     => [
            'X-APP-Key: ' . TELEBIRR_APP_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        return ['success' => false, 'message' => 'Telebirr connection error: ' . ($curlErr ?: 'empty response')];
    }

    $resp = json_decode($raw, true);
    if (!is_array($resp)) {
        return ['success' => false, 'message' => 'Telebirr returned invalid JSON (HTTP ' . $httpCode . ')'];
    }

    // Successful response contains toPayUrl
    $toPayUrl = $resp['data']['toPayUrl'] ?? ($resp['toPayUrl'] ?? '');
    if ($toPayUrl) {
        return [
            'success'      => true,
            'redirect_url' => $toPayUrl,
            'biz_no'       => $resp['data']['bizNo'] ?? ($resp['bizNo'] ?? ''),
            'reference'    => $ref,
        ];
    }

    $errMsg = $resp['message'] ?? ($resp['msg'] ?? ('Telebirr error code: ' . ($resp['code'] ?? $httpCode)));
    return ['success' => false, 'message' => $errMsg, 'raw' => $resp];
}

function provider_parse_callback(array $payload): array
{
    $tradeStatus = strtolower($payload['tradeStatus'] ?? ($payload['trade_status'] ?? ($payload['status'] ?? 'unknown')));
    return [
        'provider'       => 'telebirr',
        'transaction_id' => $payload['tradeNo'] ?? ($payload['biz_no'] ?? ($payload['transaction_id'] ?? '')),
        'reference'      => $payload['outBizNo'] ?? ($payload['outbizno'] ?? ($payload['reference'] ?? '')),
        'amount'         => $payload['totalAmount'] ?? ($payload['totalamount'] ?? ($payload['amount'] ?? '')),
        'currency'       => 'ETB',
        'phone'          => $payload['msisdn'] ?? ($payload['phone'] ?? ''),
        'status'         => ($tradeStatus === 'success' || $tradeStatus === 'trade_success') ? 'success'
                          : (($tradeStatus === 'wait' || $tradeStatus === 'pending') ? 'pending' : 'failed'),
        'raw'            => $payload,
        'logged_at'      => date('c'),
    ];
}

function provider_check_status(string $ref): array
{
    return ['success' => false, 'message' => 'Telebirr status polling is not yet implemented. Use the webhook callback (callback_telebirr.php).'];
}
