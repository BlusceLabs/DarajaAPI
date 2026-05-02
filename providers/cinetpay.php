<?php
/**
 * providers/cinetpay.php — CinetPay hosted checkout provider
 * Set PAYMENT_PROVIDER = 'cinetpay' in config.php to use this provider.
 *
 * Required constants: CINETPAY_API_KEY, CINETPAY_SITE_ID,
 *                     CINETPAY_NOTIFY_URL, CINETPAY_RETURN_URL
 * Optional:           CINETPAY_CURRENCY (default 'XOF')
 *                     CINETPAY_CHANNELS (default 'ALL' — 'MOBILE_MONEY'|'CREDIT_CARD'|'WALLET')
 *
 * Supported countries: Côte d'Ivoire, Senegal, Cameroon, Mali, Burkina Faso, Togo,
 *                      Guinea, DRC, Republic of Congo, Madagascar, Comoros, CAR,
 *                      Chad, Gabon, Equatorial Guinea (15+ Francophone African countries)
 *
 * API docs:  https://docs.cinetpay.com
 * Dashboard: https://cinetpay.com/dashboard
 * Webhook endpoint: callback_cinetpay.php
 */

function provider_flow(): string {
    return 'redirect';
}

function provider_initiate(string $phone, float $amount, string $reference): array {
    $required = ['CINETPAY_API_KEY', 'CINETPAY_SITE_ID', 'CINETPAY_NOTIFY_URL', 'CINETPAY_RETURN_URL'];
    foreach ($required as $c) {
        if (!defined($c)) {
            return ['success' => false, 'message' => 'CinetPay is not fully configured. Missing: ' . $c, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
        }
    }

    $currency  = defined('CINETPAY_CURRENCY') ? CINETPAY_CURRENCY : 'XOF';
    $channels  = defined('CINETPAY_CHANNELS') ? CINETPAY_CHANNELS : 'ALL';
    $transId   = $reference . '-' . time();
    // CinetPay amounts are whole units (no fractional currency in XOF/XAF)
    $amountInt = (int)round($amount);

    $payload = json_encode([
        'apikey'                => CINETPAY_API_KEY,
        'site_id'               => CINETPAY_SITE_ID,
        'transaction_id'        => $transId,
        'amount'                => $amountInt,
        'currency'              => $currency,
        'description'           => 'Payment for ' . $reference,
        'return_url'            => CINETPAY_RETURN_URL,
        'notify_url'            => CINETPAY_NOTIFY_URL,
        'customer_phone_number' => $phone,
        'channels'              => $channels,
        'metadata'              => $reference,
        'lang'                  => 'en',
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api-checkout.cinetpay.com/v2/payment',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Connection error: ' . $curlErr, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
    }

    $result = json_decode($response, true);
    $code   = (string)($result['code'] ?? '');
    $payUrl = $result['data']['payment_url'] ?? '';

    if (($code === '201' || $httpCode === 201 || $httpCode === 200) && $payUrl) {
        return ['success' => true, 'message' => 'Redirecting to CinetPay checkout…', 'reference' => $reference, 'redirect_url' => $payUrl, 'flow' => 'redirect'];
    }

    $errMsg = $result['message'] ?? ('Unexpected response from CinetPay. Code: ' . $code);
    return ['success' => false, 'message' => $errMsg, 'reference' => $reference, 'redirect_url' => null, 'flow' => 'redirect'];
}

/**
 * Called from callback_cinetpay.php with the decoded webhook payload.
 */
function provider_parse_callback(array $raw): array {
    $status     = strtoupper($raw['cpm_trans_status'] ?? ($raw['status'] ?? ''));
    $resultCode = ($status === 'ACCEPTED' || $status === 'SUCCESS') ? 0 : 1;
    $amount     = isset($raw['cpm_amount']) ? (float)$raw['cpm_amount'] : 0.0;
    $phone      = $raw['customer_phone_number'] ?? ($raw['phone'] ?? '');
    $transId    = $raw['cpm_trans_id'] ?? ($raw['transaction_id'] ?? '');
    $ref        = $raw['cpm_custom'] ?? ($raw['metadata'] ?? '');

    return [
        'timestamp'          => date('Y-m-d H:i:s'),
        'provider'           => 'cinetpay',
        'ResultCode'         => $resultCode,
        'ResultDesc'         => $resultCode === 0 ? 'Payment successful' : ('Payment ' . strtolower($status ?: 'failed')),
        'PhoneNumber'        => $phone,
        'Amount'             => $amount,
        'MpesaReceiptNumber' => $transId,
        'TransactionDate'    => $raw['cpm_paydate'] ?? date('Y-m-d H:i:s'),
        'Reference'          => $ref,
        'Currency'           => $raw['cpm_currency'] ?? $raw['currency'] ?? '',
        'Country'            => $raw['cpm_site_id'] ?? '',
    ];
}

function provider_check_status(array $entry, string $phone): bool {
    $entryPhone = (string)($entry['PhoneNumber'] ?? '');
    return $entryPhone === $phone && ($entry['ResultCode'] ?? null) === 0;
}
