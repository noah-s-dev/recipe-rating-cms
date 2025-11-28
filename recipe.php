<?php
require_once 'includes/auth.php';
require_once 'includes/recipes.php';
require_once 'includes/ratings.php';

$recipeId = (int)($_GET['id'] ?? 0);
$message = $_GET['message'] ?? '';
$messageType = $_GET['type'] ?? '';

// Get recipe data
$recipe = getRecipeById($recipeId);
if (!$recipe) {
    header('Location: index.php');
    exit();
}

$user = getCurrentUser();
$isOwner = $user && $user['id'] == $recipe['user_id'];

// Get user's existing rating if logged in
$userRating = null;
if ($user && !$isOwner) {
    $userRating = getUserRating($recipeId, $user['id']);
}

// Get rating statistics
$ratingStats = getRatingStats($recipeId);

// Get recent ratings
$ratingsData = getRecipeRatings($recipeId, 1, 5);
$recentRatings = $ratingsData['ratings'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Recipe Rating CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Recipe CMS</a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
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
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="recipe-detail">
            <div class="recipe-header">
                <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                
                <div class="recipe-meta">
                    <div class="recipe-author">
                        By <?php echo htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']); ?>
                        <span class="recipe-date"><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></span>
                    </div>
                    
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
                        <span class="rating-text">
                            <?php echo $avgRating; ?>/5 (<?php echo $recipe['rating_count']; ?> rating<?php echo $recipe['rating_count'] != 1 ? 's' : ''; ?>)
                        </span>
                    </div>
                    
                    <?php if ($isOwner): ?>
                        <div class="recipe-actions">
                            <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">Edit Recipe</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($recipe['image_filename']): ?>
                <div class="recipe-image">
                    <img src="uploads/<?php echo htmlspecialchars($recipe['image_filename']); ?>" 
                         alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                </div>
            <?php endif; ?>
            
            <?php if ($recipe['description']): ?>
                <div class="recipe-description">
                    <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="recipe-info">
                <?php if ($recipe['prep_time'] || $recipe['cook_time'] || $recipe['servings']): ?>
                    <div class="recipe-stats">
                        <?php if ($recipe['prep_time']): ?>
                            <div class="stat">
                                <strong>Prep Time:</strong> <?php echo formatTime($recipe['prep_time']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($recipe['cook_time']): ?>
                            <div class="stat">
                                <strong>Cook Time:</strong> <?php echo formatTime($recipe['cook_time']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($recipe['prep_time'] && $recipe['cook_time']): ?>
                            <div class="stat">
                                <strong>Total Time:</strong> <?php echo formatTime($recipe['prep_time'] + $recipe['cook_time']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($recipe['servings']): ?>
                            <div class="stat">
                                <strong>Servings:</strong> <?php echo $recipe['servings']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="recipe-content">
                <div class="ingredients-section">
                    <h2>Ingredients</h2>
                    <div class="ingredients">
                        <?php echo formatIngredients($recipe['ingredients']); ?>
                    </div>
                </div>
                
                <div class="instructions-section">
                    <h2>Instructions</h2>
                    <div class="instructions">
                        <?php echo formatInstructions($recipe['instructions']); ?>
                    </div>
                </div>
            </div>
            
            <!-- Rating section -->
            <div class="rating-section">
                <h2>Rate This Recipe</h2>
                
                <?php if ($user && !$isOwner): ?>
                    <div class="rating-form">
                        <form id="ratingForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipeId; ?>">
                            
                            <div class="form-group">
                                <label>Your Rating:</label>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" 
                                               <?php echo ($userRating && $userRating['rating'] == $i) ? 'checked' : ''; ?>>
                                        <label for="star<?php echo $i; ?>" class="star-label">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="comment">Comment (optional):</label>
                                <textarea id="comment" name="comment" rows="3" maxlength="500"><?php echo $userRating ? htmlspecialchars($userRating['comment']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <?php echo $userRating ? 'Update Rating' : 'Submit Rating'; ?>
                            </button>
                        </form>
                        
                        <div id="ratingMessage" class="message" style="display: none;"></div>
                    </div>
                <?php elseif ($isOwner): ?>
                    <p>You cannot rate your own recipe.</p>
                <?php else: ?>
                    <p><a href="login.php">Login</a> to rate this recipe.</p>
                <?php endif; ?>
                
                <!-- Rating statistics -->
                <?php if ($ratingStats['total_ratings'] > 0): ?>
                    <div class="rating-stats">
                        <h3>Rating Breakdown</h3>
                        <div class="rating-breakdown">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="rating-bar">
                                    <span class="rating-label"><?php echo $i; ?> star</span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: <?php echo $ratingStats[$i == 1 ? 'one_star_percent' : ($i == 2 ? 'two_star_percent' : ($i == 3 ? 'three_star_percent' : ($i == 4 ? 'four_star_percent' : 'five_star_percent')))]; ?>%"></div>
                                    </div>
                                    <span class="rating-count"><?php echo $ratingStats[$i == 1 ? 'one_star' : ($i == 2 ? 'two_star' : ($i == 3 ? 'three_star' : ($i == 4 ? 'four_star' : 'five_star')))]; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Recent ratings -->
                <?php if (!empty($recentRatings)): ?>
                    <div class="recent-ratings">
                        <h3>Recent Reviews</h3>
                        <?php foreach ($recentRatings as $rating): ?>
                            <div class="rating-item">
                                <div class="rating-header">
                                    <div class="rating-user">
                                        <?php echo htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']); ?>
                                    </div>
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $rating['rating'] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="rating-date">
                                        <?php echo date('M j, Y', strtotime($rating['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if ($rating['comment']): ?>
                                    <div class="rating-comment">
                                        <?php echo htmlspecialchars($rating['comment']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($ratingsData['total'] > 5): ?>
                            <p><a href="ratings.php?recipe_id=<?php echo $recipeId; ?>">View all <?php echo $ratingsData['total']; ?> reviews</a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/rating.js"></script>
    
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

