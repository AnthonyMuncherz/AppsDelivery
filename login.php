<?php
require_once 'includes/db.php';

$error_message = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    } else {
        // Fetch user by username - directly comparing plain text password (VERY INSECURE)
        // This query is slightly better protected against SQLi due to prepare,
        // but the password comparison itself is the vulnerability.
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            // Passwords match - Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Regenerate session ID for basic security, though not fixing all session issues
            session_regenerate_id(true);

            header("Location: index.php?message=Login+successful");
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }
    }
}

$page_title = "Login - QuickBite Delivery";
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
            <li><a href="login.php" class="active">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Login</h2>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; // Potential XSS if error message contains user input later ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;">Disclaimer: This website is for educational purposes only and contains intentional security vulnerabilities.</p>
    </footer>
</body>
</html> 