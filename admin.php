<?php
require_once 'includes/session_helper.php';

initSession();

// Check if user is logged in
if (!validateSession()) {
    header('Location: index.php');
    exit;
}

// If logged in, redirect to dashboard
header('Location: dashboard.php');
exit;
?>
