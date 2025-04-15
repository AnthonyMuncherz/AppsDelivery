<?php
require_once 'includes/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php?message=Please log in to view your orders.');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "My Orders - QuickBite Delivery";

// Fetch orders for the logged-in user
$stmt_orders = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC");
$stmt_orders->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_orders->execute();
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

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
                <li><a href="my_orders.php" class="active">My Orders</a></li>
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
        <h2>My Past Orders</h2>

        <?php if (empty($orders)): ?>
            <p>You haven't placed any orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-summary">
                    <h3>Order #<?php echo htmlspecialchars($order['id']); ?></h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($order['order_date']))); ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>

                    <h4>Items:</h4>
                    <ul>
                        <?php
                        // Fetch items for this order
                        $stmt_items = $db->prepare("
                            SELECT oi.quantity, oi.price, p.name
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = :order_id
                        ");
                        $stmt_items->bindParam(':order_id', $order['id'], PDO::PARAM_INT);
                        $stmt_items->execute();
                        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($items as $item):
                        ?>
                            <li>
                                <?php echo htmlspecialchars($item['quantity']); ?> x
                                <?php echo htmlspecialchars($item['name']); ?>
                                (@ $<?php echo number_format($item['price'], 2); ?> each)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;">Disclaimer: This website is for educational purposes only and contains intentional security vulnerabilities.</p>
    </footer>
</body>
</html> 