<?php
/**
 * callback_dpopay.php — DPO Pay redirect callback / IPN handler
 *
 * DPO redirects the customer to RedirectURL with GET params after payment.
 * This file handles both the customer redirect and DPO's server-side callback.
 *
 * Set DPO_REDIRECT_URL = 'https://yourdomain.com/callback_dpopay.php' in config.php.
 * DPO GET params include: TransactionToken, CompanyRef, TransactionApproval, etc.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/providers/dpopay.php';

header('Content-Type: application/json');

// ── 1. Read token from GET params ────────────────────────────────────────────
$transToken = $_GET['TransactionToken'] ?? $_POST['TransactionToken'] ?? '';

if (!$transToken) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing TransactionToken']);
    exit;
}

if (!defined('DPO_COMPANY_TOKEN')) {
    http_response_code(500);
    echo json_encode(['error' => 'DPO not configured']);
    exit;
}

// ── 2. Verify transaction with DPO ───────────────────────────────────────────
$xml = '<?xml version="1.0" encoding="utf-8"?>' .
    '<API3G>' .
    '<CompanyToken>'  . htmlspecialchars(DPO_COMPANY_TOKEN) . '</CompanyToken>' .
    '<Request>verifyToken</Request>' .
    '<TransactionToken>' . htmlspecialchars($transToken) . '</TransactionToken>' .
    '</API3G>';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => 'https://secure.3gdirectpay.com/API/v6/',
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $xml,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/xml'],
]);

$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr || !$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not verify with DPO']);
    exit;
}

libxml_use_internal_errors(true);
$xmlObj = simplexml_load_string($response);
$raw    = $xmlObj ? json_decode(json_encode($xmlObj), true) : [];

// Merge GET params for phone lookup
$raw['TransToken']  = $transToken;
$raw['CompanyRef']  = $_GET['CompanyRef'] ?? ($raw['CompanyRef'] ?? '');
$raw['CustomerPhone'] = $_GET['CustomerPhone'] ?? ($raw['CustomerPhone'] ?? '');

// ── 3. Normalise and log ─────────────────────────────────────────────────────
$entry   = provider_parse_callback($raw);
$logFile = __DIR__ . '/mpesa_log.json';
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'ok', 'result' => $raw['Result'] ?? '']);
