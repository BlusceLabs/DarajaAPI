<?php
// auth_guard.php — Include at the top of every protected admin page.
// Starts a session and redirects to login.php if the admin is not authenticated.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in'])) {
    $loginUrl = '/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/admin.php');
    header('Location: ' . $loginUrl);
    exit;
}
