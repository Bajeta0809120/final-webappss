<?php
require_once 'session_helper.php';
require_once 'functions.php';
initSession();

// CSRF Protection
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
    header('Location: ../signup.php');
    exit;
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../signup.php');
    exit;
}

// Input validation
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validate required fields
if (empty($username) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = 'Please fill in all fields';
    header('Location: ../signup.php');
    exit;
}

// Validate username format
if (!preg_match('/^[a-zA-Z0-9_-]*$/', $username)) {
    $_SESSION['error'] = 'Username can only contain letters, numbers, underscores, and hyphens';
    header('Location: ../signup.php');
    exit;
}

// Validate password strength
$passwordValidation = validatePassword($password);
if ($passwordValidation !== true) {
    $_SESSION['error'] = $passwordValidation;
    header('Location: ../signup.php');
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match';
    header('Location: ../signup.php');
    exit;
}

try {
    // Store form data in session for repopulation
    $_SESSION['form_data'] = [
        'username' => $username
    ];

    // Create new admin account with empty email
    if (createAdminAccount($username, $password, '')) {
        // Clear form data on success
        unset($_SESSION['form_data']);

        // Set success message and redirect
        $_SESSION['success'] = 'Account created successfully! You can now login.';

        header('Location: ../index.php');
    } else {
        throw new Exception('Failed to create account. Please try again.');
    }
} catch (Exception $e) {
    error_log("Admin creation error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../signup.php');
}
exit;
