# 🍳 Recipe Rating CMS

A modern, responsive web application for sharing, rating, and discovering delicious recipes. Built with PHP and MySQL, featuring a clean user interface and robust rating system.

## 🚀 Project Overview

Recipe Rating CMS is a comprehensive content management system designed for food enthusiasts to share their favorite recipes, rate others' creations, and build a community around culinary experiences. The system provides an intuitive interface for recipe management, user authentication, and social interaction through ratings and comments.

## ✨ Key Features

- **User Authentication System** - Secure login/registration with password hashing
- **Recipe Management** - Create, edit, and delete recipes with rich content
- **Rating System** - 5-star rating system with user comments
- **Search Functionality** - Find recipes by title, ingredients, or description
- **Responsive Design** - Mobile-friendly interface that works on all devices
- **Image Uploads** - Support for recipe images to enhance visual appeal
- **User Profiles** - Personal recipe collections and rating history
- **Admin Panel** - Administrative user with full system access
- **CSRF Protection** - Security measures to prevent cross-site request forgery
- **Clean URL Structure** - SEO-friendly URLs for better search engine visibility

## 👥 User Roles

### Regular Users
- Register and create accounts
- Add, edit, and delete their own recipes
- Rate and comment on other users' recipes
- Search and browse recipes
- View recipe details and ratings

### Admin Users
- All regular user privileges
- Access to system administration features
- Manage user accounts and content
- System monitoring and maintenance

## 🏗️ Project Structure

```
recipe-rating-cms/
├── .htaccess                 # Apache configuration
├── index.php                 # Homepage and recipe listing
├── login.php                 # User authentication
├── register.php              # User registration
├── recipe.php                # Individual recipe display
├── add_recipe.php            # Recipe creation form
├── edit_recipe.php           # Recipe editing form
├── delete_recipe.php         # Recipe deletion
├── my_recipes.php            # User's recipe collection
├── submit_rating.php         # Rating submission handler
├── logout.php                # User logout
├── README.md                 # Project documentation
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── auth.php              # Authentication functions
│   ├── recipes.php           # Recipe management functions
│   └── ratings.php           # Rating system functions
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   └── js/
│       └── rating.js         # Rating system JavaScript
└── sql/
    └── database_setup.sql    # Database initialization
```

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Web Server**: Apache (XAMPP/WAMP/LAMP)
- **Security**: bcrypt password hashing, CSRF protection
- **Styling**: Custom CSS with responsive design
- **Database Access**: PDO with prepared statements

## ⚙️ Setup Instructions

### Prerequisites
- XAMPP, WAMP, or LAMP stack installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server

### Installation Steps

1. **Clone/Download the Project**
   ```bash
   # Place the project in your web server's document root
   # For XAMPP: C:\xampp\htdocs\recipe-rating-cms\
   # For WAMP: C:\wamp\www\recipe-rating-cms\
   # For LAMP: /var/www/html/recipe-rating-cms/
   ```

2. **Database Setup**
   - Start your MySQL service
   - Open phpMyAdmin or MySQL command line
   - Import `sql/database_setup.sql` to create the database and tables
   - The script will create:
     - `recipe_rating_cms` database
     - Users, recipes, and ratings tables
     - Sample admin user (username: `admin`, password: `admin123`)

3. **Configuration**
   - Edit `config/database.php` if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'recipe_rating_cms');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Web Server Configuration**
   - Ensure Apache mod_rewrite is enabled
   - The `.htaccess` file is pre-configured for the project

5. **Access the Application**
   - Open your browser and navigate to:
     - `http://localhost/recipe-rating-cms/`

## 📖 Usage

### Getting Started
1. **Register an Account** - Create your user profile
2. **Add Your First Recipe** - Share your favorite dishes
3. **Explore Recipes** - Browse and search the community
4. **Rate and Comment** - Share your thoughts on recipes
5. **Build Your Collection** - Create your personal recipe library

### Admin Access
- Login with admin credentials (username: `admin`, password: `admin123`)
- Access administrative features and system management

## 🎯 Intended Use

This Recipe Rating CMS is designed for:
- **Personal Use** - Manage your own recipe collection
- **Community Building** - Create recipe sharing communities
- **Educational Purposes** - Learn about web development and CMS systems
- **Small to Medium Scale** - Suitable for family, friends, or small communities
- **Demo and Learning** - Perfect for understanding modern web application development

## 🔒 Security Features

- **Password Hashing** - bcrypt encryption for user passwords
- **CSRF Protection** - Prevents cross-site request forgery attacks
- **SQL Injection Prevention** - Prepared statements for all database queries
- **Input Sanitization** - Clean user inputs to prevent XSS attacks
- **Session Management** - Secure user session handling

## 🌐 Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome Mobile)

## 📱 Responsive Design

The application is fully responsive and optimized for:
- Desktop computers
- Tablets
- Mobile phones
- All screen sizes and orientations

## 🔧 Customization

The system is designed to be easily customizable:
- Modify CSS styles in `assets/css/style.css`
- Add new features in the `includes/` directory
- Extend functionality through additional PHP files
- Customize the database schema as needed

## 📄 License

**License for RiverTheme**

RiverTheme makes this project available for demo, instructional, and personal use. You can ask for or buy a license from [RiverTheme.com](https://RiverTheme.com) if you want a pro website, sophisticated features, or expert setup and assistance. A Pro license is needed for production deployments, customizations, and commercial use.

**Disclaimer**

The free version is offered "as is" with no warranty and might not function on all devices or browsers. It might also have some coding or security flaws. For additional information or to get a Pro license, please get in touch with [RiverTheme.com](https://RiverTheme.com).

---

**© 2025 RiverTheme. All rights reserved.**