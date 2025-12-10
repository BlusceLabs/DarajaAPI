<?php
// stk_push.php
header("Access-Control-Allow-Origin: *"); // Allow your HTML file to access this
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once 'config.php';

// 1. Handle Preflight Request (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Get Data from Frontend
$content = file_get_contents("php://input");
$data = json_decode($content, true);

$phone = $data['phone'] ?? '';
$amount = $data['amount'] ?? '';

// Basic Validation
if (!$phone || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Phone and Amount required']);
    exit();
}

// Format Phone (Ensure it starts with 254)
// Removes 0 or +254 and forces 254
$phone = '254' . substr(preg_replace("/^(\+254|254|0)/", "", $phone), 0);

// ------------------------------------------------------------------
// STEP 1: GENERATE ACCESS TOKEN
// ------------------------------------------------------------------
$consumerKey = CONSUMER_KEY;
$consumerSecret = CONSUMER_SECRET;
$url = (ENV === 'sandbox') 
    ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' 
    : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$curl_response = curl_exec($curl);
$token_data = json_decode($curl_response);

if (!isset($token_data->access_token)) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate token', 'debug' => $curl_response]);
    exit();
}

$accessToken = $token_data->access_token;

// ------------------------------------------------------------------
// STEP 2: BUILD STK PUSH REQUEST
// ------------------------------------------------------------------
$timestamp = date('YmdHis');
$password = base64_encode(BUSINESS_SHORTCODE . PASSKEY . $timestamp);

$stk_url = (ENV === 'sandbox') 
    ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' 
    : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$curl_post_data = [
    'BusinessShortCode' => BUSINESS_SHORTCODE,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => TRANSACTION_TYPE, // Defined in config (PayBill or BuyGoods)
    'Amount' => $amount,
    'PartyA' => $phone, // Customer Phone
    'PartyB' => PARTY_B, // Paybill No OR Till No
    'PhoneNumber' => $phone,
    'CallBackURL' => CALLBACK_URL,
    'AccountReference' => 'TestPayment', // Any string (e.g., Order ID)
    'TransactionDesc' => 'Payment for Goods'
];

$data_string = json_encode($curl_post_data);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $stk_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

$stk_response = curl_exec($curl);
$response_data = json_decode($stk_response, true);

// ------------------------------------------------------------------
// STEP 3: RETURN RESPONSE TO FRONTEND
// ------------------------------------------------------------------
if (isset($response_data['ResponseCode']) && $response_data['ResponseCode'] == "0") {
    echo json_encode(['success' => true, 'message' => 'STK Push sent successfully! Check your phone.']);
} else {
    $errorMsg = $response_data['errorMessage'] ?? 'Unknown error';
    echo json_encode(['success' => false, 'message' => 'M-Pesa Error: ' . $errorMsg]);
}
?>