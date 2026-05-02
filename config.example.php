<?php
// config.example.php
// Copy this file to config.php and fill in your own values.
// NEVER commit config.php to version control.

// ------------------------------------------------------------------
// SAFARICOM DARAJA CREDENTIALS
// Get these from: https://developer.safaricom.co.ke/ -> My Apps
// ------------------------------------------------------------------

define('CONSUMER_KEY',    'YOUR_CONSUMER_KEY_HERE');
define('CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET_HERE');

// Business Shortcode — Sandbox default is 174379
define('BUSINESS_SHORTCODE', '174379');

// Passkey — Sandbox default below; replace with your live passkey in production
define('PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');

// Environment: 'sandbox' for testing, 'production' for live
define('ENV', 'sandbox');

// ------------------------------------------------------------------
// PAYMENT TYPE
// ------------------------------------------------------------------
// Paybill  -> TransactionType: CustomerPayBillOnline  | PartyB: Shortcode
// Till No. -> TransactionType: CustomerBuyGoodsOnline | PartyB: Till Number

define('TRANSACTION_TYPE', 'CustomerPayBillOnline');
define('PARTY_B', BUSINESS_SHORTCODE);

// ------------------------------------------------------------------
// CALLBACK URL
// Must be a publicly accessible HTTPS URL.
// Safaricom will POST payment results here after the customer pays.
// Example: https://yourdomain.com/callback.php
// ------------------------------------------------------------------
define('CALLBACK_URL', 'https://YOUR-DOMAIN.com/callback.php');
