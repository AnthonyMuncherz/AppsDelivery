<?php
require_once 'includes/db.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    } else {
        // Check if username already exists (potential SQL injection here if not using prepared statements properly later)
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error_message = "Username already taken.";
        } else {
            // Insert new user with plain text password (VERY INSECURE)
            // This is a major vulnerability for demonstration
            $insert_stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            try {
                $insert_stmt->execute([$username, $password]);
                $success_message = "Registration successful! You can now <a href='login.php'>login</a>.";
            } catch (PDOException $e) {
                $error_message = "Registration failed. Please try again.";
                 // In a real app, log the error: error_log("Registration failed: " . $e->getMessage());
            }
        }
    }
}

$page_title = "Register - QuickBite Delivery";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>QuickBite Delivery</h1>
    </header>
     <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="cart.php">Cart</a></li>
                 <?php if (isAdmin()): ?>
                    <li><a href="admin.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="active">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <h2>Register</h2>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php else: ?>
            <form action="register.php" method="post">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;">Disclaimer: This website is for educational purposes only and contains intentional security vulnerabilities.</p>
    </footer>
</body>
</html> 