<?php
// config.php

// ------------------------------------------------------------------
// SAFARICOM DARAJA CREDENTIALS (SANDBOX)
// ------------------------------------------------------------------

// 1. Consumer Key and Secret (Get these from Daraja Portal -> My Apps)
define('CONSUMER_KEY', 'YOUR_CONSUMER_KEY_HERE'); 
define('CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET_HERE');

// 2. Business Shortcode
// For Sandbox, use 174379
define('BUSINESS_SHORTCODE', '174379'); 

// 3. Passkey
// For Sandbox, use this exact string:
define('PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');

// 4. Environment
// Use 'sandbox' for testing, 'production' for live
define('ENV', 'sandbox'); 

// ------------------------------------------------------------------
// TILL NUMBER vs PAYBILL CONFIGURATION
// ------------------------------------------------------------------

/* 
   CRUCIAL INSTRUCTION FOR TILL NUMBERS (BUY GOODS):

   1. Paybill (Default Sandbox):
      - Transaction Type: CustomerPayBillOnline
      - PartyB: Same as BusinessShortcode

   2. Buy Goods (Till Number):
      - Transaction Type: CustomerBuyGoodsOnline
      - BusinessShortcode: Your "Head Office" or "Store Number" (Not the Till Number)
      - PartyB: The actual Till Number displayed to the customer.
*/

// For Sandbox testing, we MUST use CustomerPayBillOnline because 174379 is a Paybill.
// When you go LIVE with a Till, change this to 'CustomerBuyGoodsOnline'
define('TRANSACTION_TYPE', 'CustomerPayBillOnline'); 

// In Sandbox/Paybill, PartyB is the Shortcode. 
// If using a Till, set this to the specific Till Number.
define('PARTY_B', BUSINESS_SHORTCODE); 

// 5. Callback URL
// This is where M-Pesa sends the receipt. 
// On Replit, this is your Repl's URL + /callback.php (even if we don't build the listener today)
define('CALLBACK_URL', 'https://YOUR-REPL-NAME.YOUR-USERNAME.repl.co/callback.php'); 
?>