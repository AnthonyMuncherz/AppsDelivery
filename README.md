# QuickBite Delivery - PHP SQLite Web Application

This is a simple web application simulating a food delivery service called "QuickBite Delivery". It allows users to browse products, add them to a cart, checkout, view their order history, and includes a basic admin panel for viewing all orders. The application is built using PHP and uses an SQLite database.

## Features

*   **User Management:**
    *   User Registration
    *   User Login/Logout
    *   Session Management
*   **Product Catalog:**
    *   Displays products (Pizza, Burger, Salad, Sushi) from the database on the homepage.
    *   Product images (uses default if image file is missing).
*   **Shopping Cart:**
    *   Add items to the cart from the homepage.
    *   View cart contents.
    *   Update item quantities in the cart.
    *   Remove items from the cart.
    *   Calculate cart total.
*   **Checkout:**
    *   Simulated checkout process (requires dummy card details).
    *   Creates an order in the database.
    *   Moves cart items to order items.
    *   Clears the user's cart upon successful order placement.
*   **Order History:**
    *   Logged-in users can view their past orders (`my_orders.php`).
*   **Admin Panel (`admin.php`):**
    *   Accessible only to users with the 'admin' role.
    *   Displays a list of all customer orders.
    *   Allows sorting orders by ID, User, Total, Status, or Date (Note: Sorting implementation is insecure).
    *   Links to detailed view for each order (`admin_order_details.php`).
    *   Placeholders for future Product and User management functionality.

## Technology Stack

*   **Backend:** PHP
*   **Database:** SQLite
*   **Frontend:** HTML, CSS, Basic JavaScript (for minor UI enhancements like fade-in effects)

## Setup Instructions

1.  **Prerequisites:**
    *   A web server with PHP support (e.g., XAMPP, WAMP, MAMP, or a built-in PHP server).
    *   PHP PDO extension enabled, specifically `pdo_sqlite`.
    *   A web browser.

2.  **Get the Code:**
    *   Clone this repository or download the source code files.
    *   Place the files in your web server's document root (e.g., `htdocs` in XAMPP).

3.  **Set up the Database:**
    *   Ensure the web server has write permissions for the `db/` directory within the project folder (the script will attempt to create this directory if it doesn't exist).
    *   Open your web browser and navigate to the `database_setup.php` script. For example, if your project is in a folder named `test` within `htdocs`, go to `http://localhost/test/database_setup.php`.
    *   You should see a message "Database setup completed successfully." This creates the `db/delivery_app.sqlite` file and populates it with tables, a default admin user, and sample products.

4.  **Access the Application:**
    *   Navigate to the `index.php` file in your browser (e.g., `http://localhost/test/` or `http://localhost/test/index.php`).

5.  **Admin Login:**
    *   Use the following default credentials to log in as an administrator:
        *   **Username:** `admin`
        *   **Password:** `admin123`

## File Structure

```
├── .git/               # Git directory (if applicable)
├── css/
│   └── style.css       # Main stylesheet
├── db/                 # Database directory (created by setup)
│   └── delivery_app.sqlite # SQLite database file
├── includes/
│   └── db.php          # Database connection, session start, helper functions (isLoggedIn, isAdmin)
├── images/             # Product images (ensure these exist or add them)
│   └── pizza.jpg
│   └── burger.jpg
│   └── salad.jpg
│   └── sushi.jpg
│   └── default.png     # Default image placeholder
├── index.php           # Homepage / Product listing
├── login.php           # User login page
├── register.php        # User registration page
├── logout.php          # Handles user logout
├── cart.php            # Shopping cart page
├── checkout.php        # Checkout page
├── my_orders.php       # User's order history page
├── admin.php           # Admin panel dashboard (order listing)
├── admin_order_details.php # Admin view for specific order details
├── database_setup.php  # Script to initialize the SQLite database and tables
└── README.md           # This file
```

