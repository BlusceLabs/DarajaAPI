<?php
// config.example.php
// Copy this file to config.php and fill in your own values.
// NEVER commit config.php to version control.

// ------------------------------------------------------------------
// ACTIVE PROVIDER
// Choose one: 'tinypesa' | 'daraja'   | 'pesapal'     | 'flutterwave' | 'paystack'
//             'mtnmomo' | 'airtelmoney' | 'dpopay'   | 'ozow'        | 'cinetpay'
//             'paymob'  | 'ecocash'  | 'orangemoney' | 'cellulant'
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

// ------------------------------------------------------------------
// CINETPAY  (Hosted checkout — requires PAYMENT_PROVIDER = 'cinetpay')
// 15+ Francophone African countries: CI, SN, CM, ML, BF, TG, GN, CD, CG,
// MG, KM, CF, TD, GA, GQ. Currencies: XOF, XAF, CDF, GNF, KMF, MGA.
// Get credentials from: https://cinetpay.com/dashboard
// Set notify URL to: callback_cinetpay.php
// ------------------------------------------------------------------
// define('CINETPAY_API_KEY',    'YOUR_CINETPAY_API_KEY');
// define('CINETPAY_SITE_ID',    'YOUR_CINETPAY_SITE_ID');
// define('CINETPAY_NOTIFY_URL', 'https://yourdomain.com/callback_cinetpay.php');
// define('CINETPAY_RETURN_URL', 'https://yourdomain.com/');
// define('CINETPAY_CURRENCY',   'XOF');      // XOF | XAF | CDF | GNF | KMF | MGA
// define('CINETPAY_CHANNELS',   'ALL');      // ALL | MOBILE_MONEY | CREDIT_CARD | WALLET

// ------------------------------------------------------------------
// PAYMOB  (Hosted checkout — requires PAYMENT_PROVIDER = 'paymob')
// Egypt, Morocco, Pakistan, UAE, Saudi Arabia, Oman, Kuwait.
// Get credentials from: https://accept.paymob.com/portal2/en/settings
// Webhook: Developers → Transaction Processed URL → callback_paymob.php
// ------------------------------------------------------------------
// define('PAYMOB_API_KEY',        'YOUR_PAYMOB_API_KEY');
// define('PAYMOB_INTEGRATION_ID', 'YOUR_INTEGRATION_ID');   // numeric
// define('PAYMOB_IFRAME_ID',      'YOUR_IFRAME_ID');         // numeric
// define('PAYMOB_CURRENCY',       'EGP');    // EGP | MAD | PKR | AED | SAR | OMR
// define('PAYMOB_COUNTRY',        'EG');     // ISO 3166-1 alpha-2
// define('PAYMOB_HMAC_SECRET',    'YOUR_HMAC_SECRET');       // for webhook verification

// ------------------------------------------------------------------
// ECOCASH  (STK Push — requires PAYMENT_PROVIDER = 'ecocash')
// Zimbabwe (USD/ZWL), Eswatini (SZL), Lesotho (LSL).
// Apply for Merchant API at: https://developer.econet.co.zw
// Callback URL set to: callback_ecocash.php
// ------------------------------------------------------------------
// define('ECOCASH_MERCHANT_CODE',   'YOUR_MERCHANT_CODE');
// define('ECOCASH_MERCHANT_PIN',    'YOUR_MERCHANT_PIN');
// define('ECOCASH_MERCHANT_NUMBER', 'YOUR_MERCHANT_NUMBER');  // e.g. 0776000000
// define('ECOCASH_CALLBACK_URL',    'https://yourdomain.com/callback_ecocash.php');
// define('ECOCASH_CURRENCY',        'USD');  // USD | ZWL | SZL | LSL
// define('ECOCASH_ENV',             'sandbox'); // 'sandbox' | 'production'

// ------------------------------------------------------------------
// ORANGE MONEY  (Hosted checkout — requires PAYMENT_PROVIDER = 'orangemoney')
// Côte d'Ivoire (ci), Senegal (sn), Mali (ml), Burkina Faso (bf),
// Guinea (gn), Guinea-Bissau (gw), Cameroon (cm), Madagascar (mg),
// Sierra Leone (sle), Liberia (lr), Morocco (ma), Tunisia (tn), Jordan (jo).
// Register at: https://developer.orange.com/myapps
// Set notify URL to: callback_orangemoney.php
// ------------------------------------------------------------------
// define('ORANGE_CLIENT_ID',     'YOUR_ORANGE_CLIENT_ID');
// define('ORANGE_CLIENT_SECRET', 'YOUR_ORANGE_CLIENT_SECRET');
// define('ORANGE_MERCHANT_KEY',  'YOUR_ORANGE_MERCHANT_KEY');
// define('ORANGE_NOTIFY_URL',    'https://yourdomain.com/callback_orangemoney.php');
// define('ORANGE_RETURN_URL',    'https://yourdomain.com/');
// define('ORANGE_COUNTRY',       'ci');   // 2-letter country code (see list above)
// define('ORANGE_CURRENCY',      'XOF');  // XOF | XAF | GNF | SLL | MAD | TND | JOD

// ------------------------------------------------------------------
// CELLULANT / TINGG  (Hosted checkout — requires PAYMENT_PROVIDER = 'cellulant')
// 18+ African countries: KE, UG, TZ, RW, NG, GH, ZM, ZW, MW, MZ, ET,
// CI, CM, SN, ZA, CD, MG, BW, AO. Mobile money + cards + bank transfer.
// Register at: https://app.tingg.africa
// Set callback URL to: callback_cellulant.php
// ------------------------------------------------------------------
// define('CELLULANT_API_KEY',       'YOUR_CELLULANT_API_KEY');
// define('CELLULANT_CLIENT_ID',     'YOUR_CELLULANT_CLIENT_ID');
// define('CELLULANT_CLIENT_SECRET', 'YOUR_CELLULANT_CLIENT_SECRET');
// define('CELLULANT_SERVICE_CODE',  'YOUR_SERVICE_CODE');   // from Tingg dashboard
// define('CELLULANT_CALLBACK_URL',  'https://yourdomain.com/callback_cellulant.php');
// define('CELLULANT_CURRENCY',      'KES');  // currency for your country
// define('CELLULANT_COUNTRY',       'KE');   // ISO 3166-1 alpha-2
