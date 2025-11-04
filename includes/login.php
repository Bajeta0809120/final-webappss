<?php
require_once 'session_helper.php';
require_once 'functions.php';
initSession();

// CSRF Protection
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['error'] = 'Invalid session. Please try again.';
    header('Location: ../index.php');
    exit;
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../index.php');
    exit;
}

// Rate limiting
if (isset($_SESSION['login_attempts']) && isset($_SESSION['last_attempt'])) {
    $time_passed = time() - $_SESSION['last_attempt'];
    
    // If 5 minutes have passed, reset the counter
    if ($time_passed > 300) {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
    }
    // If still within lockout period and has too many attempts
    elseif ($_SESSION['login_attempts'] >= 5) {
        $minutes_left = ceil((300 - $time_passed) / 60);
        $_SESSION['error'] = "Too many login attempts. Please try again in {$minutes_left} minute(s).";
        header('Location: ../index.php');
        exit;
    }
}

// Input validation
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Store username for form repopulation
$_SESSION['last_username'] = $username;

// Validate required fields
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Please enter both username and password';
    header('Location: ../index.php');
    exit;
}

try {
    // Log the attempt
    error_log("Login attempt - Username: " . $username);
    
    // Verify username format before database query
    if (!preg_match('/^[a-zA-Z0-9_-]*$/', $username)) {
        $_SESSION['error'] = 'Username can only contain letters, numbers, underscores, and hyphens';
        header('Location: ../index.php');
        exit;
    }
    
    // Verify password meets minimum requirements
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long';
        header('Location: ../index.php');
        exit;
    }
    
    // Attempt login
    $admin_data = validateLogin($username, $password);

    if ($admin_data) {
        error_log("Successful login for username: " . $username);
        // Reset login attempts on success
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
        unset($_SESSION['last_username']);

        // Regenerate session and CSRF token before setting variables
        regenerateSession();

        // Set session variables
        $_SESSION['admin_id'] = $admin_data['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $admin_data['role'] ?? 'admin';
        $_SESSION['is_logged_in'] = true;
        $_SESSION['is_admin'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['created'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT']; // Store user agent for additional security

        header('Location: ../admin.php');
        exit;
    } else {
        // Track failed login attempts
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
        $_SESSION['last_attempt'] = time();
        
        $_SESSION['error'] = 'Invalid username or password';
        header('Location: ../index.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred during login. Please try again later.';
    header('Location: ../index.php');
    exit;
}
?>