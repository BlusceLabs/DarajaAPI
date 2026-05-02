<?php
// config.example.php
// Copy this file to config.php and fill in your own values.
// NEVER commit config.php to version control.

// ------------------------------------------------------------------
// ACTIVE PROVIDER
// Choose one: 'tinypesa' | 'daraja' | 'pesapal' | 'flutterwave'
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
