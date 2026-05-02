<?php
// config.php
header("Access-Control-Allow-Origin: *");

// ------------------------------------------------------------------
// TINYPESA CONFIGURATION
// ------------------------------------------------------------------

// Paste your TinyPesa API Key here (from your Dashboard)
define('TINYPESA_API_KEY', 'qfDwAHBhP1s51gKdxIKFi-ZjXFoYpqwJMZPOZE-EDNoaIb7MvQ'); 

// The TinyPesa Endpoint
define('TINYPESA_URL', 'https://tinypesa.com/api/v1/express/initialize');
?>