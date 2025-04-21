<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function ensure_logged_in() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Check if current user is admin
function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Ensure admin access
function ensure_admin() {
    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied.';
        exit;
    }
}

// Get current user ID
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Log out user
function logout() {
    session_unset();
    session_destroy();
}

// CSRF token functions
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>