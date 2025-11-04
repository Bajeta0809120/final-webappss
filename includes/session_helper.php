<?php
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.gc_maxlifetime', 1800); // 30 minutes
        ini_set('session.cookie_lifetime', 0); // Until browser closes
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        // Start session
        session_start();
        
        // Initialize CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Regenerate session ID and maintain the session data
        session_regenerate_id(true);
        
        // Generate new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function validateSession() {
    initSession();

    // Check if session is valid
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
        return false;
    }

    // Check session lifetime
    if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > 1800)) {
        destroySession();
        return false;
    }

    // Verify user agent hasn't changed (prevent session hijacking)
    if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        destroySession();
        return false;
    }

    // Verify CSRF token exists
    if (!isset($_SESSION['csrf_token'])) {
        regenerateSession();
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();

    // Periodically regenerate session ID to prevent fixation attacks
    if (!isset($_SESSION['created']) || (time() - $_SESSION['created'] > 300)) {
        regenerateSession();
        $_SESSION['created'] = time();
    }

    return true;
}

function setSessionUser($userId, $role) {
    initSession();
    $_SESSION['is_logged_in'] = true;
    $_SESSION['admin_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity'] = time();
    $_SESSION['created'] = time();
}

function destroySession() {
    initSession();
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}
?>