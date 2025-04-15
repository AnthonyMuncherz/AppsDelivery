# Insecure Food Delivery PHP App

This is a simple PHP web application simulating a food delivery service. It is intentionally built with common security vulnerabilities for educational purposes, demonstrating concepts like:

*   SQL Injection
*   Cross-Site Scripting (XSS)
*   Insecure Password Storage
*   Session Management Issues
*   Insecure Direct Object References (IDOR)
*   Lack of Input Validation
*   Information Exposure
*   Broken Access Control

**DO NOT USE THIS CODE IN A PRODUCTION ENVIRONMENT.**

## Features

*   User registration and login
*   Admin role with basic management capabilities
*   Product listing
*   Shopping cart functionality
*   Simulated checkout process
*   Order history for users
*   Admin view for orders and products

## Requirements

*   PHP (tested with 7.x and 8.x)
*   PDO SQLite extension enabled in PHP
*   A web server (like Apache or Nginx) or PHP's built-in server

## Setup

1.  Clone or download this repository.
2.  Make sure the web server has write permissions to the `db/` directory (it needs to create the `database.sqlite` file).
3.  Navigate to `init_db.php` in your browser (e.g., `http://localhost/init_db.php`). This will create the `db/database.sqlite` file and set up the necessary tables and a default admin user.
    *   **Default Admin Credentials:** username: `admin`, password: `admin123`
4.  Delete or restrict access to `init_db.php` after setup.
5.  Access the main application through `index.php` (e.g., `http://localhost/`).

## Running with PHP Built-in Server

From the project root directory, run:

```bash
php -S localhost:8000
```

Then access the site at `http://localhost:8000`.

## Security Warning

This application is deliberately insecure. Explore the code and the running application to identify and understand the vulnerabilities listed above. 