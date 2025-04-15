<?php
require_once 'includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php?message=Please+login+to+checkout");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Fetch cart items for the logged-in user
$cart_stmt = $db->prepare("
    SELECT ci.id as cart_item_id, ci.quantity, p.id as product_id, p.name, p.price
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
");
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// If cart is empty, redirect back to cart page
if (empty($cart_items)) {
    header("Location: cart.php?message=Your+cart+is+empty");
    exit();
}

// Calculate total
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Handle Order Placement (Simulated Payment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {

    // INTENTIONALLY VULNERABLE: Trusting the calculated total_price without re-verification
    // Could be manipulated if client-side calculation was used or if cart items were modified
    // between page load and submission.

    // Basic validation for dummy payment details (not actually used)
    $card_number = $_POST['card_number'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    if (empty($card_number) || empty($expiry_date) || empty($cvv)) {
        $error_message = "Please fill in all payment details (for simulation).";
    } else {
        try {
            $db->beginTransaction();

            // 1. Create Order
            $order_stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)");
            $order_stmt->execute([$user_id, $total_price, 'processing']); // Start as processing
            $order_id = $db->lastInsertId();

            // 2. Move Cart Items to Order Items
            $order_item_stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                 // Fetch current price again to avoid using potentially stale price from cart view
                 $product_price_stmt = $db->prepare("SELECT price FROM products WHERE id = ?");
                 $product_price_stmt->execute([$item['product_id']]);
                 $current_price = $product_price_stmt->fetchColumn();
                 // Still vulnerable if price changes between cart calculation and this point?
                 // Or if the re-fetched price is wrong somehow.
                 if ($current_price === false) throw new Exception("Product price not found during checkout.");

                 $order_item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $current_price]);
            }

            // 3. Clear Cart for the user
            $clear_cart_stmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $clear_cart_stmt->execute([$user_id]);

            $db->commit();

            $success_message = "Order placed successfully! Your order ID is: " . $order_id;
            // Redirect to a success page or index after a delay?
             header("Location: index.php?message=Order+placed+successfully!+Order+ID:+" . $order_id);
             exit();

        } catch (Exception $e) {
            $db->rollBack();
            $error_message = "Failed to place order. Please try again. Error: " . $e->getMessage();
            // Log the error: error_log("Order placement failed: " . $e->getMessage());
        }
    }
}

$page_title = "Checkout - QuickBite Delivery";
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
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <h2>Checkout</h2>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
            <a href="index.php" class="btn">Back to Home</a>
        <?php else: ?>
            <h3>Order Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>
                        <td><strong>$<?php echo number_format($total_price, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <h3>Simulated Payment</h3>
            <p>Please enter dummy payment details to complete the order.</p>
            <form action="checkout.php" method="post">
                 <input type="hidden" name="action" value="place_order">
                 <div>
                     <label for="card_number">Card Number:</label>
                     <input type="text" id="card_number" name="card_number" placeholder="e.g., 1111222233334444" required>
                 </div>
                 <div>
                     <label for="expiry_date">Expiry Date (MM/YY):</label>
                     <input type="text" id="expiry_date" name="expiry_date" placeholder="e.g., 12/25" required>
                 </div>
                 <div>
                     <label for="cvv">CVV:</label>
                     <input type="text" id="cvv" name="cvv" placeholder="e.g., 123" required>
                 </div>
                 <button type="submit" class="btn">Place Order</button>
            </form>
            <p><a href="cart.php">Back to Cart</a></p>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;">Disclaimer: This website is for educational purposes only and contains intentional security vulnerabilities.</p>
    </footer>
</body>
</html> 