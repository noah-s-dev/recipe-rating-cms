<?php
/**
 * Authentication Helper Functions
 */

require_once 'config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Register a new user
 */
function registerUser($username, $email, $password, $firstName, $lastName) {
    $pdo = getDBConnection();
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    // Validate input
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash, $firstName, $lastName]);
        
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Login user
 */
function loginUser($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, first_name, last_name FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['logged_in'] = true;
        
        return ['success' => true, 'message' => 'Login successful'];
    } else {
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name']
        ];
    }
    return null;
}

/**
 * Require login (redirect if not logged in)
 */
function requireLogin($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit();
    }
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>

