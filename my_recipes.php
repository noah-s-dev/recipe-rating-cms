<?php
require_once 'includes/auth.php';
require_once 'includes/recipes.php';

// Require login
requireLogin();

$user = getCurrentUser();
$page = (int)($_GET['page'] ?? 1);
$search = sanitizeInput($_GET['search'] ?? '');
$message = $_GET['message'] ?? '';
$messageType = $_GET['type'] ?? '';

// Get user's recipes
$result = getRecipes($page, 12, $search, $user['id']);
$recipes = $result['recipes'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recipes - Recipe Rating CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Recipe CMS</a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="my_recipes.php" class="nav-link active">My Recipes</a>
                <a href="add_recipe.php" class="nav-link">Add Recipe</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>My Recipes</h1>
            <a href="add_recipe.php" class="btn btn-primary">Add New Recipe</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search your recipes..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
                <?php if ($search): ?>
                    <a href="my_recipes.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if (empty($recipes)): ?>
            <div class="no-recipes">
                <?php if ($search): ?>
                    <p>No recipes found matching your search.</p>
                <?php else: ?>
                    <p>You haven't added any recipes yet.</p>
                    <a href="add_recipe.php" class="btn btn-primary">Add Your First Recipe</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="recipes-grid">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <?php if ($recipe['image_filename']): ?>
                            <div class="recipe-image">
                                <img src="uploads/<?php echo htmlspecialchars($recipe['image_filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="recipe-content">
                            <h3><a href="recipe.php?id=<?php echo $recipe['id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></h3>
                            
                            <?php if ($recipe['description']): ?>
                                <p class="recipe-description"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100)); ?><?php echo strlen($recipe['description']) > 100 ? '...' : ''; ?></p>
                            <?php endif; ?>
                            
                            <div class="recipe-meta">
                                <div class="recipe-rating">
                                    <div class="stars">
                                        <?php
                                        $avgRating = round($recipe['avg_rating'], 1);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $avgRating) {
                                                echo '<span class="star filled">★</span>';
                                            } else {
                                                echo '<span class="star">☆</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-text"><?php echo $avgRating; ?> (<?php echo $recipe['rating_count']; ?>)</span>
                                </div>
                                
                                <div class="recipe-stats">
                                    <?php if ($recipe['prep_time'] || $recipe['cook_time']): ?>
                                        <span class="time">
                                            <?php 
                                            $totalTime = ($recipe['prep_time'] ?? 0) + ($recipe['cook_time'] ?? 0);
                                            if ($totalTime > 0) echo formatTime($totalTime);
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($recipe['servings']): ?>
                                        <span class="servings"><?php echo $recipe['servings']; ?> servings</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="recipe-actions">
                                <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Are you sure you want to delete this recipe?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $i == $currentPage ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

