# Fruitables - Organic Fruits & Vegetables E-commerce

## Local Setup

1. Install Requirements:
   - PHP 7.4 or higher
   - MySQL 8.0 or higher (MySQL 8.0.32 recommended)
   - Apache (optional)

2. Database Setup:
   - Ensure MySQL server is running (check MySQL system preferences)
   - Create a new MySQL database named `fruitables_db`
   - The tables will be automatically created when you first run the application

3. Configuration:
   - Open `includes/config.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'fruitables_db');
     ```

4. Run the Application:
   ```bash
   # Navigate to project directory
   cd fruitables

   # Using PHP's built-in server
   php -S localhost:8000

   # OR place in Apache's web directory
   # macOS: /Applications/XAMPP/htdocs/
   # Windows: C:\xampp\htdocs\
   # Linux: /var/www/html/
   ```

5. Access the Website:
   - Open your browser and visit: `http://localhost:8000`
   - Admin login:
     - Email: admin@fruitables.com
     - Password: admin123

6. Troubleshooting:
   - Ensure MySQL server is running (green status in MySQL preferences)
   - Check database connection in config.php matches your MySQL settings
   - Verify PHP version: `php -v`
   - Check error logs if using Apache

## Features

### Customer Features
- User registration & login
- Profile management
- Order tracking
- Wishlist
- Secure checkout
- Order confirmation emails
- Newsletter subscription

### Admin Features
- Product management
- Order management
- Category management
- Inventory control
- Sales reports
- Customer management
- Coupon & promotion handling

## Security Notes
- Default admin credentials should be changed after first login
- Keep MySQL and PHP updated to latest stable versions
- Regular backups of database recommended