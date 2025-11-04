<?php
session_start();

// Clear login attempt counters
unset($_SESSION['login_attempts']);
unset($_SESSION['last_attempt']);

// Set success message
$_SESSION['success'] = 'Login attempts have been reset. You can try logging in again.';

// Redirect back to login page
header('Location: ../index.php');
exit;
?>