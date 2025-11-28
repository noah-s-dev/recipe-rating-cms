<?php
require_once 'includes/auth.php';
require_once 'includes/recipes.php';

$page = (int)($_GET['page'] ?? 1);
$search = sanitizeInput($_GET['search'] ?? '');

// Get recipes
$result = getRecipes($page, 12, $search);
$recipes = $result['recipes'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
$totalRecipes = $result['total'];

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Rating CMS - Share and Rate Delicious Recipes</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Recipe CMS</a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Home</a>
                <?php if ($user): ?>
                    <a href="my_recipes.php" class="nav-link">My Recipes</a>
                    <a href="add_recipe.php" class="nav-link">Add Recipe</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h1>Discover Amazing Recipes</h1>
                <p>Share your favorite recipes and discover new ones from our community</p>
            </div>
            <?php if ($user): ?>
                <a href="add_recipe.php" class="btn btn-primary">Share Your Recipe</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Join Our Community</a>
            <?php endif; ?>
        </div>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search recipes by title, ingredients, or description..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($search): ?>
            <div class="search-results">
                <p>Found <?php echo $totalRecipes; ?> recipe<?php echo $totalRecipes != 1 ? 's' : ''; ?> 
                   matching "<?php echo htmlspecialchars($search); ?>"</p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($recipes)): ?>
            <div class="no-recipes">
                <?php if ($search): ?>
                    <h2>No recipes found</h2>
                    <p>Try adjusting your search terms or browse all recipes.</p>
                    <a href="index.php" class="btn btn-primary">View All Recipes</a>
                <?php else: ?>
                    <h2>No recipes yet</h2>
                    <p>Be the first to share a recipe with our community!</p>
                    <?php if ($user): ?>
                        <a href="add_recipe.php" class="btn btn-primary">Add First Recipe</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">Join to Share Recipes</a>
                    <?php endif; ?>
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
                                <p class="recipe-description">
                                    <?php echo htmlspecialchars(substr($recipe['description'], 0, 120)); ?>
                                    <?php echo strlen($recipe['description']) > 120 ? '...' : ''; ?>
                                </p>
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
                            
                            <div class="recipe-author">
                                <small>By <?php echo htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']); ?></small>
                                <small class="recipe-date"><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></small>
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

    <?php if (!$user): ?>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 3rem 0; margin-top: 3rem;">
            <div class="container">
                <h2>Join Our Recipe Community</h2>
                <p>Share your favorite recipes, discover new dishes, and connect with fellow food enthusiasts.</p>
                <div style="margin-top: 2rem;">
                    <a href="register.php" class="btn" style="background: white; color: #667eea; margin-right: 1rem;">Sign Up Free</a>
                    <a href="login.php" class="btn" style="background: transparent; border: 2px solid white; color: white;">Login</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Copyright Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center my-2">
                <div>
                    <span>© 2025 . Developed by </span>
                    <a href="https://rivertheme.com" class="fw-bold text-decoration-none" target="_blank" rel="noopener">RiverTheme</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

