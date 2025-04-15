<?php
require_once 'includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php?message=Please+login+to+view+your+cart");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Handle Cart Actions (Add, Update, Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    // Validate quantity (basic validation)
    if (!ctype_digit((string)$quantity) || $quantity < 1) {
        $quantity = 1;
    }


    if ($product_id === null) {
        $error_message = "Invalid product specified.";
    }
     elseif ($action === 'add') {
        // Check if product exists
        $product_stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $product_stmt->execute([$product_id]);
        if (!$product_stmt->fetch()) {
            $error_message = "Product not found.";
        } else {
            // Check if item already in cart
            $cart_stmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $cart_stmt->execute([$user_id, $product_id]);
            $cart_item = $cart_stmt->fetch();

            if ($cart_item) {
                // Update quantity
                $new_quantity = $cart_item['quantity'] + $quantity;
                $update_stmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $update_stmt->execute([$new_quantity, $cart_item['id']]);
                $success_message = "Item quantity updated in cart.";
            } else {
                // Add new item
                $insert_stmt = $db->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $insert_stmt->execute([$user_id, $product_id, $quantity]);
                $success_message = "Item added to cart.";
            }
        }
    } elseif ($action === 'update') {
         $cart_item_id = $_POST['cart_item_id'] ?? null; // Get cart item ID for update/remove
         if ($cart_item_id && ctype_digit((string)$cart_item_id)){
             $update_stmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
             $update_stmt->execute([$quantity, $cart_item_id, $user_id]); // Ensure user owns the cart item
             $success_message = "Cart updated.";
         } else {
             $error_message = "Invalid cart item for update.";
         }

    } elseif ($action === 'remove') {
        $cart_item_id = $_POST['cart_item_id'] ?? null; // Get cart item ID for update/remove
        if ($cart_item_id && ctype_digit((string)$cart_item_id)) {

            $delete_stmt = $db->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
            $delete_stmt->execute([$cart_item_id, $user_id]);
            $success_message = "Item removed from cart.";
         } else {
             $error_message = "Invalid cart item for removal.";
         }
    }

    // Redirect back to cart page to prevent form resubmission
    // Pass messages via query parameters (Potential for issues if messages get too long or complex)
    $redirect_url = "cart.php";
    if ($success_message) $redirect_url .= "?success=" . urlencode($success_message);
    if ($error_message) $redirect_url .= ($success_message ? '&' : '?') . "error=" . urlencode($error_message);
    header("Location: " . $redirect_url);
    exit();
}

// Retrieve success/error messages from GET parameters after redirect
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Fetch cart items for the logged-in user
// Using a JOIN to get product details
// Potential for information disclosure if not careful about columns selected
$stmt = $db->prepare("
    SELECT ci.id as cart_item_id, ci.quantity, p.id as product_id, p.name, p.price, p.image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

$page_title = "Shopping Cart - QuickBite Delivery";
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
                <li><a href="cart.php" class="active">Cart</a></li>
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
        <h2>Your Shopping Cart</h2>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty.</p>
            <a href="index.php" class="btn">Continue Shopping</a>
        <?php else: ?>
            <form action="cart.php" method="post"> <!-- Single form for updates/removals -->
                 <input type="hidden" name="action" id="cart_action" value="update"> <!-- Default action -->
                <div class="table-responsive"> <!-- Added responsive wrapper -->
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                         <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                             <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: auto;">
                                         <?php else: ?>
                                            <img src="images/default.png" alt="Default Image" style="width: 50px; height: auto;">
                                         <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                         <!-- Input for quantity update -->
                                         <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                         <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px;" onchange="document.getElementById('cart_action').value='update'; this.form.submit();">
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <!-- Remove button specific to this item -->
                                         <button type="submit" name="action" value="remove" onclick="document.getElementById('cart_action').value='remove'; document.querySelector('input[name=cart_item_id]').value='<?php echo $item['cart_item_id']; ?>';" class="btn" style="background-color: #ffc107; color: #333;">Remove</button>
                                         <!-- We need JS to set the correct cart_item_id for removal -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div> <!-- Close responsive wrapper -->
             </form> <!-- End Form -->

            <div class="cart-total">
                <strong>Total: $<?php echo number_format($total_price, 2); ?></strong>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                 <a href="index.php" class="btn" style="margin-right: 10px; background-color: #6c757d;">Continue Shopping</a>
                <a href="checkout.php" class="btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;"></p>
    </footer>

</body>
</html> 