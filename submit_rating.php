<?php
require_once 'includes/auth.php';
require_once 'includes/ratings.php';

// Set JSON response header
header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to rate recipes']);
    exit();
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request token']);
    exit();
}

$recipeId = (int)($_POST['recipe_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$user = getCurrentUser();

// Validate input
if ($recipeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipe ID']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5 stars']);
    exit();
}

// Submit rating
$result = submitRating($recipeId, $user['id'], $rating, $comment);

// Return JSON response
echo json_encode($result);
?>

