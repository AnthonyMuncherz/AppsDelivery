<?php
require_once 'includes/db.php';

// Fetch products from the database
$stmt = $db->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Home - QuickBite Delivery";
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
            <li><a href="index.php" class="active">Home</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="cart.php">Cart</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <h2>Our Menu</h2>

        <?php if (isset($_GET['message'])): ?>
            <div class="message info"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card fade-in">
                    <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                         <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <img src="images/default.png" alt="Default Image"> <!-- Provide a default image -->
                    <?php endif; ?>
                    <div class="product-info">
                        <div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                        </div>
                        <form action="cart.php" method="post">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" style="width: 60px; display: inline-block; margin-right: 5px;">
                            <button type="submit" class="btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;">Disclaimer: This website is for educational purposes only and contains intentional security vulnerabilities.</p>
    </footer>

    <!-- JavaScript for fade-in animation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            // Trigger fade-in after a short delay to ensure rendering
            setTimeout(() => {
                 elements.forEach((el, index) => {
                    // Optional: Add a slight delay for each subsequent card
                    setTimeout(() => {
                        el.classList.add('visible');
                    }, index * 100); // Stagger the animation slightly
                });
            }, 100);
        });
    </script>
</body>
</html> 