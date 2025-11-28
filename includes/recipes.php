<?php
/**
 * Recipe Management Helper Functions
 */

require_once 'config/database.php';

/**
 * Get all recipes with pagination and search
 */
function getRecipes($page = 1, $limit = 12, $search = '', $userId = null) {
    $pdo = getDBConnection();
    $offset = ($page - 1) * $limit;
    
    $whereClause = '';
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " WHERE (r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ?)";
        $params = ["%$search%", "%$search%", "%$search%"];
    }
    
    if ($userId) {
        $whereClause .= empty($whereClause) ? " WHERE r.user_id = ?" : " AND r.user_id = ?";
        $params[] = $userId;
    }
    
    // Get recipes with user info and average rating
    $sql = "SELECT r.*, u.username, u.first_name, u.last_name,
                   COALESCE(AVG(rt.rating), 0) as avg_rating,
                   COUNT(rt.id) as rating_count
            FROM recipes r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN ratings rt ON r.id = rt.recipe_id
            $whereClause
            GROUP BY r.id
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(DISTINCT r.id) as total FROM recipes r JOIN users u ON r.user_id = u.id $whereClause";
    $countParams = array_slice($params, 0, -2); // Remove limit and offset
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch()['total'];
    
    return [
        'recipes' => $recipes,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

/**
 * Get single recipe by ID
 */
function getRecipeById($id) {
    $pdo = getDBConnection();
    
    $sql = "SELECT r.*, u.username, u.first_name, u.last_name,
                   COALESCE(AVG(rt.rating), 0) as avg_rating,
                   COUNT(rt.id) as rating_count
            FROM recipes r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN ratings rt ON r.id = rt.recipe_id
            WHERE r.id = ?
            GROUP BY r.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Create new recipe
 */
function createRecipe($userId, $title, $description, $ingredients, $instructions, $prepTime, $cookTime, $servings, $imageFilename = null) {
    $pdo = getDBConnection();
    
    try {
        $sql = "INSERT INTO recipes (user_id, title, description, ingredients, instructions, prep_time, cook_time, servings, image_filename) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $title, $description, $ingredients, $instructions, $prepTime, $cookTime, $servings, $imageFilename]);
        
        return ['success' => true, 'recipe_id' => $pdo->lastInsertId(), 'message' => 'Recipe created successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create recipe: ' . $e->getMessage()];
    }
}

/**
 * Update recipe
 */
function updateRecipe($id, $userId, $title, $description, $ingredients, $instructions, $prepTime, $cookTime, $servings, $imageFilename = null) {
    $pdo = getDBConnection();
    
    // Check if user owns the recipe
    $stmt = $pdo->prepare("SELECT user_id FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    
    if (!$recipe || $recipe['user_id'] != $userId) {
        return ['success' => false, 'message' => 'You can only edit your own recipes'];
    }
    
    try {
        $sql = "UPDATE recipes SET title = ?, description = ?, ingredients = ?, instructions = ?, 
                prep_time = ?, cook_time = ?, servings = ?";
        $params = [$title, $description, $ingredients, $instructions, $prepTime, $cookTime, $servings];
        
        if ($imageFilename !== null) {
            $sql .= ", image_filename = ?";
            $params[] = $imageFilename;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Recipe updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update recipe: ' . $e->getMessage()];
    }
}

/**
 * Delete recipe
 */
function deleteRecipe($id, $userId) {
    $pdo = getDBConnection();
    
    // Check if user owns the recipe
    $stmt = $pdo->prepare("SELECT user_id, image_filename FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    
    if (!$recipe || $recipe['user_id'] != $userId) {
        return ['success' => false, 'message' => 'You can only delete your own recipes'];
    }
    
    try {
        // Delete recipe (ratings will be deleted automatically due to foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($recipe['image_filename'] && file_exists("uploads/" . $recipe['image_filename'])) {
            unlink("uploads/" . $recipe['image_filename']);
        }
        
        return ['success' => true, 'message' => 'Recipe deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete recipe: ' . $e->getMessage()];
    }
}

/**
 * Handle image upload
 */
function uploadRecipeImage($file) {
    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to save uploaded file'];
    }
}

/**
 * Format time for display
 */
function formatTime($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    } else {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'min' : '');
    }
}

/**
 * Format ingredients for display
 */
function formatIngredients($ingredients) {
    return nl2br(htmlspecialchars($ingredients));
}

/**
 * Format instructions for display
 */
function formatInstructions($instructions) {
    return nl2br(htmlspecialchars($instructions));
}
?>

