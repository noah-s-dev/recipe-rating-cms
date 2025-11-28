<?php
require_once 'includes/auth.php';

// Handle logout
if (isLoggedIn()) {
    logoutUser();
}

// Redirect to home page
header('Location: index.php');
exit();
?>

