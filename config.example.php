<?php
// config.example.php
// Copy this file to config.php and fill in your own values.
// NEVER commit config.php to version control.

// ------------------------------------------------------------------
// ACTIVE PROVIDER
// Choose one: 'tinypesa' | 'daraja' | 'pesapal' | 'flutterwave'
//             'paystack' | 'mtnmomo' | 'airtelmoney' | 'dpopay' | 'ozow'
// ------------------------------------------------------------------
define('PAYMENT_PROVIDER', 'tinypesa');

// ------------------------------------------------------------------
// TINYPESA  (STK Push — requires PAYMENT_PROVIDER = 'tinypesa')
// Get your API key from: https://tinypesa.com/dashboard
// ------------------------------------------------------------------
define('TINYPESA_API_KEY', 'YOUR_TINYPESA_API_KEY_HERE');
define('TINYPESA_URL',     'https://tinypesa.com/api/v1/express/initialize');

// ------------------------------------------------------------------
// SAFARICOM DARAJA  (STK Push — requires PAYMENT_PROVIDER = 'daraja')
// Create an app at: https://developer.safaricom.co.ke
// Set DARAJA_CALLBACK_URL to your public HTTPS callback URL.
// ------------------------------------------------------------------
// define('DARAJA_ENV',             'sandbox');   // 'sandbox' | 'production'
// define('DARAJA_CONSUMER_KEY',    'YOUR_DARAJA_CONSUMER_KEY');
// define('DARAJA_CONSUMER_SECRET', 'YOUR_DARAJA_CONSUMER_SECRET');
// define('DARAJA_SHORTCODE',       '174379');     // Paybill or Till number
// define('DARAJA_PASSKEY',         'YOUR_DARAJA_PASSKEY');
// define('DARAJA_CALLBACK_URL',    'https://yourdomain.com/callback.php');

// ------------------------------------------------------------------
// PESAPAL V3  (Hosted checkout — requires PAYMENT_PROVIDER = 'pesapal')
// Get credentials from: https://developer.pesapal.com
// PESAPAL_IPN_URL    — your public HTTPS IPN endpoint (callback_pesapal.php)
// PESAPAL_CALLBACK_URL — where PesaPal redirects the user after payment
// ------------------------------------------------------------------
// define('PESAPAL_ENV',             'sandbox');   // 'sandbox' | 'production'
// define('PESAPAL_CONSUMER_KEY',    'YOUR_PESAPAL_CONSUMER_KEY');
// define('PESAPAL_CONSUMER_SECRET', 'YOUR_PESAPAL_CONSUMER_SECRET');
// define('PESAPAL_IPN_URL',         'https://yourdomain.com/callback_pesapal.php');
// define('PESAPAL_CALLBACK_URL',    'https://yourdomain.com/');

// ------------------------------------------------------------------
// FLUTTERWAVE  (Hosted checkout — requires PAYMENT_PROVIDER = 'flutterwave')
// Get credentials from: https://developer.flutterwave.com
// FLW_SECRET_HASH — set this in Flutterwave dashboard under Webhooks
// FLW_REDIRECT_URL — where Flutterwave redirects after payment
// ------------------------------------------------------------------
// define('FLW_SECRET_KEY',    'YOUR_FLW_SECRET_KEY');
// define('FLW_PUBLIC_KEY',    'YOUR_FLW_PUBLIC_KEY');
// define('FLW_SECRET_HASH',   'YOUR_FLW_WEBHOOK_SECRET_HASH');
// define('FLW_REDIRECT_URL',  'https://yourdomain.com/');
// define('FLW_LOGO_URL',      '');   // optional — your logo URL for the checkout page

// ------------------------------------------------------------------
// PAYSTACK  (Hosted checkout — requires PAYMENT_PROVIDER = 'paystack')
// Get credentials from: https://dashboard.paystack.com/#/settings/developers
// Supported countries: Nigeria, Ghana, Kenya, South Africa, Côte d'Ivoire, Egypt
// Set webhook URL in Paystack dashboard → Settings → Webhooks → callback_paystack.php
// ------------------------------------------------------------------
// define('PAYSTACK_SECRET_KEY',   'YOUR_PAYSTACK_SECRET_KEY');
// define('PAYSTACK_CALLBACK_URL', 'https://yourdomain.com/');
// define('PAYSTACK_CURRENCY',     'KES');   // 'NGN' | 'GHS' | 'KES' | 'ZAR' | 'USD' | 'XOF' | 'EGP'

// ------------------------------------------------------------------
// MTN MOMO  (STK-style — requires PAYMENT_PROVIDER = 'mtnmomo')
// Get credentials from: https://momodeveloper.mtn.com
// Supported countries: Ghana, Uganda, Côte d'Ivoire, Cameroon, Zambia, Rwanda, and more.
// ------------------------------------------------------------------
// define('MTNMOMO_ENV',              'sandbox');   // 'sandbox' | 'production'
// define('MTNMOMO_SUBSCRIPTION_KEY', 'YOUR_MTN_SUBSCRIPTION_KEY');
// define('MTNMOMO_API_USER',         'YOUR_MTN_API_USER_UUID');
// define('MTNMOMO_API_KEY',          'YOUR_MTN_API_KEY');
// define('MTNMOMO_CALLBACK_URL',     'https://yourdomain.com/callback_mtnmomo.php');
// define('MTNMOMO_CURRENCY',         'UGX');   // 'GHS' | 'UGX' | 'XOF' | 'XAF' | 'ZMW' | 'RWF'

// ------------------------------------------------------------------
// AIRTEL MONEY  (STK-style — requires PAYMENT_PROVIDER = 'airtelmoney')
// Get credentials from: https://developers.airtel.africa
// Supported countries: Kenya, Uganda, Tanzania, Rwanda, Zambia, Madagascar,
//                      Malawi, DRC, Republic of Congo, Niger, Sierra Leone.
// ------------------------------------------------------------------
// define('AIRTEL_ENV',           'sandbox');   // 'sandbox' | 'production'
// define('AIRTEL_CLIENT_ID',     'YOUR_AIRTEL_CLIENT_ID');
// define('AIRTEL_CLIENT_SECRET', 'YOUR_AIRTEL_CLIENT_SECRET');
// define('AIRTEL_CALLBACK_URL',  'https://yourdomain.com/callback_airtelmoney.php');
// define('AIRTEL_COUNTRY',       'KE');    // 'KE' | 'UG' | 'TZ' | 'RW' | 'ZM' | 'MG' | 'MW' | 'CG' | 'CD' | 'NE' | 'SL'
// define('AIRTEL_CURRENCY',      'KES');   // 'KES' | 'UGX' | 'TZS' | 'RWF' | 'ZMW' | 'MGA' | 'MWK' | 'XAF' | 'CDF' | 'XOF' | 'SLL'

// ------------------------------------------------------------------
// DPO PAY  (Hosted checkout — requires PAYMENT_PROVIDER = 'dpopay')
// Get credentials from: https://merchants.dpopay.com
// Supported countries: 20+ African countries. DPO_SERVICE_TYPE is the numeric
// service ID assigned to your account in the DPO merchant portal.
// ------------------------------------------------------------------
// define('DPO_COMPANY_TOKEN', 'YOUR_DPO_COMPANY_TOKEN');
// define('DPO_SERVICE_TYPE',  '3854');      // your DPO service type ID
// define('DPO_REDIRECT_URL',  'https://yourdomain.com/callback_dpopay.php');
// define('DPO_BACK_URL',      'https://yourdomain.com/');
// define('DPO_CURRENCY',      'KES');       // currency code for your country
// define('DPO_COUNTRY_CODE',  'KE');        // ISO 3166-1 alpha-2 country code
// define('DPO_ENV',           'sandbox');   // 'sandbox' | 'live'

// ------------------------------------------------------------------
// OZOW  (Instant EFT — requires PAYMENT_PROVIDER = 'ozow')
// Get credentials from: https://ozow.com — South Africa only (ZAR)
// Set notify URL in Ozow dashboard to callback_ozow.php
// ------------------------------------------------------------------
// define('OZOW_SITE_CODE',    'YOUR_OZOW_SITE_CODE');
// define('OZOW_PRIVATE_KEY',  'YOUR_OZOW_PRIVATE_KEY');
// define('OZOW_API_KEY',      'YOUR_OZOW_API_KEY');
// define('OZOW_SUCCESS_URL',  'https://yourdomain.com/');
// define('OZOW_CANCEL_URL',   'https://yourdomain.com/');
// define('OZOW_ERROR_URL',    'https://yourdomain.com/');
// define('OZOW_NOTIFY_URL',   'https://yourdomain.com/callback_ozow.php');
// define('OZOW_TEST',         true);   // set to false for live payments
