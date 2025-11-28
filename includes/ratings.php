<?php
/**
 * Rating System Helper Functions
 */

require_once 'config/database.php';

/**
 * Submit or update a rating for a recipe
 */
function submitRating($recipeId, $userId, $rating, $comment = '') {
    $pdo = getDBConnection();
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Rating must be between 1 and 5 stars'];
    }
    
    // Check if recipe exists
    $stmt = $pdo->prepare("SELECT id, user_id FROM recipes WHERE id = ?");
    $stmt->execute([$recipeId]);
    $recipe = $stmt->fetch();
    
    if (!$recipe) {
        return ['success' => false, 'message' => 'Recipe not found'];
    }
    
    // Prevent users from rating their own recipes
    if ($recipe['user_id'] == $userId) {
        return ['success' => false, 'message' => 'You cannot rate your own recipe'];
    }
    
    try {
        // Check if user has already rated this recipe
        $stmt = $pdo->prepare("SELECT id FROM ratings WHERE recipe_id = ? AND user_id = ?");
        $stmt->execute([$recipeId, $userId]);
        $existingRating = $stmt->fetch();
        
        if ($existingRating) {
            // Update existing rating
            $stmt = $pdo->prepare("UPDATE ratings SET rating = ?, comment = ?, updated_at = CURRENT_TIMESTAMP WHERE recipe_id = ? AND user_id = ?");
            $stmt->execute([$rating, $comment, $recipeId, $userId]);
            $message = 'Your rating has been updated';
        } else {
            // Insert new rating
            $stmt = $pdo->prepare("INSERT INTO ratings (recipe_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$recipeId, $userId, $rating, $comment]);
            $message = 'Your rating has been submitted';
        }
        
        return ['success' => true, 'message' => $message];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to submit rating: ' . $e->getMessage()];
    }
}

/**
 * Get user's rating for a specific recipe
 */
function getUserRating($recipeId, $userId) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT rating, comment FROM ratings WHERE recipe_id = ? AND user_id = ?");
    $stmt->execute([$recipeId, $userId]);
    return $stmt->fetch();
}

/**
 * Get all ratings for a recipe with user information
 */
function getRecipeRatings($recipeId, $page = 1, $limit = 10) {
    $pdo = getDBConnection();
    $offset = ($page - 1) * $limit;
    
    // Get ratings with user info
    $sql = "SELECT r.rating, r.comment, r.created_at, u.first_name, u.last_name, u.username
            FROM ratings r
            JOIN users u ON r.user_id = u.id
            WHERE r.recipe_id = ?
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$recipeId, $limit, $offset]);
    $ratings = $stmt->fetchAll();
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM ratings WHERE recipe_id = ?");
    $countStmt->execute([$recipeId]);
    $total = $countStmt->fetch()['total'];
    
    return [
        'ratings' => $ratings,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

/**
 * Get rating statistics for a recipe
 */
function getRatingStats($recipeId) {
    $pdo = getDBConnection();
    
    $sql = "SELECT 
                COUNT(*) as total_ratings,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM ratings 
            WHERE recipe_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$recipeId]);
    $stats = $stmt->fetch();
    
    // Calculate percentages
    if ($stats['total_ratings'] > 0) {
        $stats['five_star_percent'] = round(($stats['five_star'] / $stats['total_ratings']) * 100);
        $stats['four_star_percent'] = round(($stats['four_star'] / $stats['total_ratings']) * 100);
        $stats['three_star_percent'] = round(($stats['three_star'] / $stats['total_ratings']) * 100);
        $stats['two_star_percent'] = round(($stats['two_star'] / $stats['total_ratings']) * 100);
        $stats['one_star_percent'] = round(($stats['one_star'] / $stats['total_ratings']) * 100);
    } else {
        $stats['five_star_percent'] = 0;
        $stats['four_star_percent'] = 0;
        $stats['three_star_percent'] = 0;
        $stats['two_star_percent'] = 0;
        $stats['one_star_percent'] = 0;
    }
    
    return $stats;
}

/**
 * Delete a rating (for admin or user who submitted it)
 */
function deleteRating($recipeId, $userId, $ratingUserId = null) {
    $pdo = getDBConnection();
    
    // If ratingUserId is provided, check if current user can delete it
    if ($ratingUserId && $ratingUserId != $userId) {
        // Only allow if current user is admin (you can implement admin check here)
        return ['success' => false, 'message' => 'You can only delete your own ratings'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM ratings WHERE recipe_id = ? AND user_id = ?");
        $stmt->execute([$recipeId, $ratingUserId ?? $userId]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Rating deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Rating not found'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete rating: ' . $e->getMessage()];
    }
}

/**
 * Generate star display HTML
 */
function displayStars($rating, $maxStars = 5, $showEmpty = true) {
    $html = '<div class="stars">';
    
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star filled">★</span>';
        } elseif ($showEmpty) {
            $html .= '<span class="star">☆</span>';
        }
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Generate interactive star rating HTML for forms
 */
function displayInteractiveStars($name, $currentRating = 0) {
    $html = '<div class="star-rating" data-rating="' . $currentRating . '">';
    
    for ($i = 1; $i <= 5; $i++) {
        $checked = ($i == $currentRating) ? 'checked' : '';
        $html .= '<input type="radio" name="' . $name . '" value="' . $i . '" id="star' . $i . '" ' . $checked . '>';
        $html .= '<label for="star' . $i . '" class="star-label">★</label>';
    }
    
    $html .= '</div>';
    return $html;
}
?>

