<?php
/**
 * providers/evcplus.php — EVC Plus by Hormuud Telesom (Somalia)
 *
 * EVC Plus is Somalia's most widely used mobile money network, operated by
 * Hormuud Telesom. It covers Mogadishu and southern Somalia (USD / SOS).
 * Telesom Zaad (Somaliland, north) uses a compatible API structure.
 *
 * Required constants (define in config.php):
 *   EVCPLUS_MERCHANT_UID  — Merchant UID assigned by Hormuud (e.g. "M000001")
 *   EVCPLUS_API_USER_ID   — API User ID from the Hormuud merchant portal
 *   EVCPLUS_API_KEY       — API key / secret from the Hormuud merchant portal
 *
 * Optional constants:
 *   EVCPLUS_CURRENCY      — 'USD' (default) or 'SOS' (Somali Shilling)
 *   EVCPLUS_ENV           — 'production' (default) or 'sandbox'
 *
 * Phone format: international, e.g. 2526XXXXXXXX (drop leading 0, prepend 252)
 * Registration: contact Hormuud Telesom merchant services — https://hormuud.com
 */

function provider_flow(): string { return 'stk'; }

function provider_initiate(string $amount, string $phone, string $ref): array
{
    if (!defined('EVCPLUS_MERCHANT_UID') || !defined('EVCPLUS_API_USER_ID') || !defined('EVCPLUS_API_KEY')) {
        return ['success' => false, 'message' => 'EVC Plus is not configured. Set EVCPLUS_MERCHANT_UID, EVCPLUS_API_USER_ID and EVCPLUS_API_KEY in config.php.'];
    }

    $env      = (defined('EVCPLUS_ENV') && EVCPLUS_ENV === 'sandbox') ? 'sandbox' : 'production';
    $baseUrl  = $env === 'sandbox'
        ? 'https://sandbox.hormuud.com/api/TokenHandler.ashx'
        : 'https://api.waafi.com/asm';

    $currency = defined('EVCPLUS_CURRENCY') ? EVCPLUS_CURRENCY : 'USD';

    // Normalise phone — strip leading + or 0, ensure 252 prefix for Somalia
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) <= 9) {
        $phone = '252' . ltrim($phone, '0');
    }

    $requestId = 'EVP-' . strtoupper(bin2hex(random_bytes(6)));
    $timestamp = date('Y-m-d H:i:s');

    $body = [
        'schemaVersion' => '1.0',
        'requestId'     => $requestId,
        'timestamp'     => $timestamp,
        'channelName'   => 'WEB',
        'serviceName'   => 'API_PURCHASE',
        'serviceParams' => [
            'merchantUid'   => EVCPLUS_MERCHANT_UID,
            'apiUserId'     => EVCPLUS_API_USER_ID,
            'apiKey'        => EVCPLUS_API_KEY,
            'paymentMethod' => 'MWALLET_ACCOUNT',
            'payerInfo'     => [
                'accountNo' => $phone,
            ],
            'transactionInfo' => [
                'referenceId' => $ref,
                'invoiceId'   => $ref,
                'amount'      => (string)$amount,
                'currency'    => $currency,
                'description' => 'Payment ' . $ref,
            ],
        ],
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $baseUrl,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    ]);
    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        return ['success' => false, 'message' => 'EVC Plus connection error: ' . ($curlErr ?: 'empty response')];
    }

    $resp = json_decode($raw, true);
    if (!is_array($resp)) {
        return ['success' => false, 'message' => 'EVC Plus returned invalid JSON'];
    }

    $state     = strtoupper($resp['params']['state'] ?? '');
    $txId      = $resp['params']['issuerTransactionId'] ?? ($resp['params']['txId'] ?? $requestId);
    $desc      = $resp['params']['description'] ?? ($resp['responseCode'] ?? 'Unknown');

    if ($state === 'SUCCESS') {
        return [
            'success'        => true,
            'message'        => 'EVC Plus payment prompt sent. Please approve on your phone.',
            'transaction_id' => $txId,
            'request_id'     => $requestId,
        ];
    }

    return [
        'success' => false,
        'message' => 'EVC Plus: ' . $desc,
        'raw'     => $resp,
    ];
}

function provider_parse_callback(array $payload): array
{
    $state = strtoupper($payload['params']['state'] ?? ($payload['status'] ?? ''));
    return [
        'provider'       => 'evcplus',
        'transaction_id' => $payload['params']['issuerTransactionId'] ?? ($payload['txId'] ?? ($payload['transaction_id'] ?? '')),
        'reference'      => $payload['serviceParams']['transactionInfo']['referenceId'] ?? ($payload['reference'] ?? ''),
        'amount'         => $payload['serviceParams']['transactionInfo']['amount'] ?? ($payload['amount'] ?? ''),
        'currency'       => $payload['serviceParams']['transactionInfo']['currency'] ?? 'USD',
        'phone'          => $payload['serviceParams']['payerInfo']['accountNo'] ?? ($payload['phone'] ?? ''),
        'status'         => ($state === 'SUCCESS') ? 'success' : (($state === 'FAILED') ? 'failed' : 'pending'),
        'raw'            => $payload,
        'logged_at'      => date('c'),
    ];
}

function provider_check_status(string $ref): array
{
    // EVC Plus is a synchronous API — status is returned immediately in the initiation response.
    // There is no separate status-check endpoint in the public merchant API.
    return ['success' => false, 'message' => 'EVC Plus does not have a separate status-check endpoint. Status is returned synchronously at initiation.'];
}
