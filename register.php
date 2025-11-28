<?php
require_once 'includes/auth.php';

$message = '';
$messageType = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        
        // Validate form data
        if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $message = 'All fields are required.';
            $messageType = 'error';
        } elseif ($password !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'error';
        } else {
            $result = registerUser($username, $email, $password, $firstName, $lastName);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            
            if ($result['success']) {
                // Auto-login after successful registration
                loginUser($username, $password);
                header('Location: index.php');
                exit();
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Recipe Rating CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Create Account</h1>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            
            <p class="auth-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
    
    <!-- Copyright Footer -->
    <footer class="footer">
        <div class="container">
        <div class="text-center my-2">
            <div>
                <span>© 2025 . Developed by </span>
                <a href="https://rivertheme.com" class="fw-bold text-decoration-none" target="_blank" rel="noopener">RiverTheme</a>
            </div>
        </div>
    </footer>
</body>
</html>

