<?php
require_once 'session_helper.php';

// Initialize session if not already started
initSession();

// Store success message in a temporary variable
$success_message = 'You have been successfully logged out';

// Destroy the current session
destroySession();

// Start a new session for the flash message
session_start();
$_SESSION['success'] = $success_message;
$_SESSION['logout'] = true;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Redirect to login page
header('Location: ../index.php');
exit;
?>