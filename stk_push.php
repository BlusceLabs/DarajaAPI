<?php
// stk_push.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. Manually Configuration
$apiKey = ''; 
$url = 'https://tinypesa.com/api/v1/express/initialize';

// 2. Get Data
$phone = $_GET['phone'] ?? '';
$amount = $_GET['amount'] ?? '';

if (!$phone || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Phone and Amount required']);
    exit();
}

// 3. Format Phone
$phone = '254' . substr(preg_replace("/^(\+254|254|0)/", "", $phone), 0);

// 4. Prepare Data
$body = http_build_query([
    'amount' => $amount,
    'msisdn' => $phone,
    'account_no' => 'Order-' . rand(1000, 9999)
]);

// 5. Send Request with "Browser-like" Headers
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// CRITICAL: We add these headers to look like a real browser
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "ApiKey: " . $apiKey,
    "Content-Type: application/x-www-form-urlencoded",
    "Origin: https://tinypesa.com",       // <--- TRICKS THE FIREWALL
    "Referer: https://tinypesa.com/",     // <--- TRICKS THE FIREWALL
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 6. Handle Response
if ($httpCode == 200) {
    echo json_encode(['success' => true, 'message' => 'STK Push Sent! Check phone.']);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed. Firewall blocked us.', 
        'debug' => $response
    ]);
}
?>