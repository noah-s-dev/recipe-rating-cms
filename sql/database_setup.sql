-- Recipe Rating CMS Database Setup
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS recipe_rating_cms;
USE recipe_rating_cms;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Recipes table
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    prep_time INT, -- in minutes
    cook_time INT, -- in minutes
    servings INT,
    image_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ratings table
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_recipe_rating (user_id, recipe_id)
);

-- Create indexes for better performance
CREATE INDEX idx_recipes_user_id ON recipes(user_id);
CREATE INDEX idx_ratings_recipe_id ON ratings(recipe_id);
CREATE INDEX idx_ratings_user_id ON ratings(user_id);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES 
('admin', 'admin@example.com', '$2y$10$UYqdcAS32LR89F4UP7umiO2.hgjsJHwj.hXdjNInWy/5HY.aC1NsK', 'Admin', 'User');

-- Insert sample recipes
INSERT INTO recipes (user_id, title, description, ingredients, instructions, prep_time, cook_time, servings) VALUES 
(1, 'Classic Chocolate Chip Cookies', 'Delicious homemade chocolate chip cookies that are crispy on the outside and chewy on the inside.', 
'2 1/4 cups all-purpose flour\n1 tsp baking soda\n1 tsp salt\n1 cup butter, softened\n3/4 cup granulated sugar\n3/4 cup brown sugar\n2 large eggs\n2 tsp vanilla extract\n2 cups chocolate chips', 
'1. Preheat oven to 375°F (190°C).\n2. Mix flour, baking soda, and salt in a bowl.\n3. Cream butter and sugars until fluffy.\n4. Beat in eggs and vanilla.\n5. Gradually add flour mixture.\n6. Stir in chocolate chips.\n7. Drop rounded tablespoons onto ungreased baking sheets.\n8. Bake 9-11 minutes until golden brown.\n9. Cool on baking sheet for 2 minutes before removing.', 
15, 10, 24),

(1, 'Spaghetti Carbonara', 'Authentic Italian pasta dish with eggs, cheese, and pancetta.', 
'400g spaghetti\n200g pancetta or guanciale\n4 large eggs\n100g Pecorino Romano cheese, grated\n100g Parmigiano-Reggiano cheese, grated\nBlack pepper\nSalt', 
'1. Cook spaghetti in salted boiling water until al dente.\n2. Cut pancetta into small pieces and cook until crispy.\n3. Whisk eggs with grated cheeses and black pepper.\n4. Drain pasta, reserving 1 cup pasta water.\n5. Add hot pasta to pancetta pan.\n6. Remove from heat and quickly mix in egg mixture.\n7. Add pasta water gradually until creamy.\n8. Serve immediately with extra cheese and pepper.', 
10, 15, 4);

-- Insert sample ratings
INSERT INTO ratings (recipe_id, user_id, rating, comment) VALUES 
(1, 1, 5, 'Perfect cookies! My family loved them.'),
(2, 1, 4, 'Great carbonara recipe, very authentic.');

