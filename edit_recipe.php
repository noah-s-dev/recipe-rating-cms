<?php
require_once 'includes/auth.php';
require_once 'includes/recipes.php';

// Require login
requireLogin();

$message = '';
$messageType = '';
$recipeId = (int)($_GET['id'] ?? 0);

// Get recipe data
$recipe = getRecipeById($recipeId);
if (!$recipe) {
    header('Location: index.php');
    exit();
}

// Check if user owns the recipe
$user = getCurrentUser();
if ($recipe['user_id'] != $user['id']) {
    header('Location: recipe.php?id=' . $recipeId);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $prepTime = (int)($_POST['prep_time'] ?? 0);
        $cookTime = (int)($_POST['cook_time'] ?? 0);
        $servings = (int)($_POST['servings'] ?? 1);
        
        // Validate required fields
        if (empty($title) || empty($ingredients) || empty($instructions)) {
            $message = 'Title, ingredients, and instructions are required.';
            $messageType = 'error';
        } else {
            $imageFilename = null;
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadRecipeImage($_FILES['image']);
                if ($uploadResult['success']) {
                    $imageFilename = $uploadResult['filename'];
                    
                    // Delete old image if exists
                    if ($recipe['image_filename'] && file_exists("uploads/" . $recipe['image_filename'])) {
                        unlink("uploads/" . $recipe['image_filename']);
                    }
                } else {
                    $message = $uploadResult['message'];
                    $messageType = 'error';
                }
            }
            
            // Update recipe if no upload errors
            if ($messageType !== 'error') {
                $result = updateRecipe($recipeId, $user['id'], $title, $description, $ingredients, $instructions, $prepTime, $cookTime, $servings, $imageFilename);
                
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                
                if ($result['success']) {
                    // Refresh recipe data
                    $recipe = getRecipeById($recipeId);
                }
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
    <title>Edit Recipe - Recipe Rating CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Recipe CMS</a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="my_recipes.php" class="nav-link">My Recipes</a>
                <a href="add_recipe.php" class="nav-link">Add Recipe</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="recipe-form">
            <h1>Edit Recipe</h1>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="title">Recipe Title *</label>
                    <input type="text" id="title" name="title" required maxlength="200"
                           value="<?php echo htmlspecialchars($_POST['title'] ?? $recipe['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" maxlength="500"><?php echo htmlspecialchars($_POST['description'] ?? $recipe['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="prep_time">Prep Time (minutes)</label>
                        <input type="number" id="prep_time" name="prep_time" min="0" max="1440"
                               value="<?php echo htmlspecialchars($_POST['prep_time'] ?? $recipe['prep_time']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cook_time">Cook Time (minutes)</label>
                        <input type="number" id="cook_time" name="cook_time" min="0" max="1440"
                               value="<?php echo htmlspecialchars($_POST['cook_time'] ?? $recipe['cook_time']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="servings">Servings</label>
                        <input type="number" id="servings" name="servings" min="1" max="100"
                               value="<?php echo htmlspecialchars($_POST['servings'] ?? $recipe['servings']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="ingredients">Ingredients *</label>
                    <textarea id="ingredients" name="ingredients" rows="8" required 
                              placeholder="List ingredients, one per line or separated by commas"><?php echo htmlspecialchars($_POST['ingredients'] ?? $recipe['ingredients']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="instructions">Instructions *</label>
                    <textarea id="instructions" name="instructions" rows="10" required 
                              placeholder="Step-by-step cooking instructions"><?php echo htmlspecialchars($_POST['instructions'] ?? $recipe['instructions']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Recipe Image</label>
                    <?php if ($recipe['image_filename']): ?>
                        <div class="current-image">
                            <img src="uploads/<?php echo htmlspecialchars($recipe['image_filename']); ?>" alt="Current recipe image" style="max-width: 200px; height: auto;">
                            <p>Current image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF. Leave empty to keep current image.</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Recipe</button>
                    <a href="recipe.php?id=<?php echo $recipeId; ?>" class="btn btn-secondary">Cancel</a>
                    <a href="delete_recipe.php?id=<?php echo $recipeId; ?>" class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this recipe? This action cannot be undone.')">Delete Recipe</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

