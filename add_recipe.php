<?php
require_once 'includes/auth.php';
require_once 'includes/recipes.php';

// Require login
requireLogin();

$message = '';
$messageType = '';

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
                } else {
                    $message = $uploadResult['message'];
                    $messageType = 'error';
                }
            }
            
            // Create recipe if no upload errors
            if ($messageType !== 'error') {
                $user = getCurrentUser();
                $result = createRecipe($user['id'], $title, $description, $ingredients, $instructions, $prepTime, $cookTime, $servings, $imageFilename);
                
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                
                if ($result['success']) {
                    header('Location: recipe.php?id=' . $result['recipe_id']);
                    exit();
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
    <title>Add Recipe - Recipe Rating CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Recipe CMS</a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="my_recipes.php" class="nav-link">My Recipes</a>
                <a href="add_recipe.php" class="nav-link active">Add Recipe</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="recipe-form">
            <h1>Add New Recipe</h1>
            
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
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" maxlength="500"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="prep_time">Prep Time (minutes)</label>
                        <input type="number" id="prep_time" name="prep_time" min="0" max="1440"
                               value="<?php echo htmlspecialchars($_POST['prep_time'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cook_time">Cook Time (minutes)</label>
                        <input type="number" id="cook_time" name="cook_time" min="0" max="1440"
                               value="<?php echo htmlspecialchars($_POST['cook_time'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="servings">Servings</label>
                        <input type="number" id="servings" name="servings" min="1" max="100"
                               value="<?php echo htmlspecialchars($_POST['servings'] ?? '4'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="ingredients">Ingredients *</label>
                    <textarea id="ingredients" name="ingredients" rows="8" required 
                              placeholder="List ingredients, one per line or separated by commas"><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="instructions">Instructions *</label>
                    <textarea id="instructions" name="instructions" rows="10" required 
                              placeholder="Step-by-step cooking instructions"><?php echo htmlspecialchars($_POST['instructions'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Recipe Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Recipe</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

