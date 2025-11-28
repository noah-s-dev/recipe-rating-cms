<?php
require_once 'includes/auth.php';
require_once 'includes/recipes.php';

// Require login
requireLogin();

$recipeId = (int)($_GET['id'] ?? 0);
$user = getCurrentUser();

// Handle deletion
if ($recipeId > 0) {
    $result = deleteRecipe($recipeId, $user['id']);
    
    if ($result['success']) {
        header('Location: my_recipes.php?message=' . urlencode($result['message']) . '&type=success');
    } else {
        header('Location: recipe.php?id=' . $recipeId . '&message=' . urlencode($result['message']) . '&type=error');
    }
} else {
    header('Location: index.php');
}

exit();
?>

