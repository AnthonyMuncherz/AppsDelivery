<?php
require_once 'includes/db.php';

// Check if user is logged in and is an admin
if (!isAdmin()) {
    header("Location: index.php?message=Access+Denied");
    exit();
}

$error_message = '';
$order_id = $_GET['order_id'] ?? null;


if (!$order_id || !ctype_digit((string)$order_id)) {
    header("Location: admin.php?message=Invalid+Order+ID");
    exit();
}

// Fetch Order Details
try {
    $order_stmt = $db->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $order_stmt->execute([$order_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header("Location: admin.php?message=Order+Not+Found");
        exit();
    }

    // Fetch Order Items
    $items_stmt = $db->prepare("
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error fetching order details: " . $e->getMessage();
    $order = null;
    $order_items = [];
     // Log the error in a real app
}

$page_title = "Order Details (#" . htmlspecialchars($order_id) . ") - Admin Panel";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>QuickBite Delivery - Admin Panel</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin.php" class="active">Admin Panel</a></li>
            <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Order Details <small>(ID: <?php echo htmlspecialchars($order_id); ?>)</small></h2>
        <p><a href="admin.php">&laquo; Back to Orders List</a></p>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <h3>Order Information</h3>
            <p><strong>User:</strong> <?php echo htmlspecialchars($order['username']); ?> (ID: <?php echo $order['user_id']; ?>)</p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>

            <h3>Items in this Order</h3>
            <?php if (empty($order_items)): ?>
                <p>No items found for this order (this might indicate an error).</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price (at time of order)</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?> (ID: <?php echo $item['product_id']; ?>)</td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
         <?php elseif(!$error_message): ?>
             <p>Order not found.</p>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;"></p>
    </footer>
</body>
</html> 