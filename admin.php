<?php
require_once 'includes/db.php';

// Check if user is logged in and is an admin
if (!isAdmin()) {
    // Redirect non-admins or non-logged-in users to the home page or login page
    // Sending a generic message is slightly better than revealing admin page exists
    header("Location: index.php?message=Access+Denied");
    exit();
}

$error_message = '';
$success_message = '';

// --- Handle Admin Actions --- 
// Potential areas for vulnerabilities: SQL Injection, Missing Authorization, CSRF

// Example: View Orders
// INTENTIONALLY VULNERABLE: Directly embedding user-provided sorting parameters without proper validation/sanitization
$sort_column = $_GET['sort'] ?? 'order_date'; // Example: Allow sorting via GET param
$sort_order = $_GET['order'] ?? 'DESC';

// Basic check to prevent obviously bad column names, but not foolproof against complex attacks
$allowed_sort_columns = ['id', 'user_id', 'total_amount', 'status', 'order_date', 'username'];
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'order_date'; // Default back
}
$allowed_sort_orders = ['ASC', 'DESC'];
if (!in_array(strtoupper($sort_order), $allowed_sort_orders)) {
    $sort_order = 'DESC'; // Default back
}

// Build the SQL query string - Directly embedding variables is dangerous!
// $order_sql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY " . $sort_column . " " . $sort_order;
// Using prepare for the main query but potentially vulnerable sorting
$order_sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, u.username
             FROM orders o
             JOIN users u ON o.user_id = u.id
             ORDER BY " . $sort_column . " " . $sort_order; // $sort_column and $sort_order are embedded!

try {
    $order_stmt = $db->query($order_sql); // Using query() because ORDER BY cannot be parameterized easily with PDO
    $orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching orders: " . $e->getMessage();
    $orders = [];
    // Log the error in a real app
}


// TODO: Add functionality for managing products (Add/Edit/Delete)
// This would be another place to introduce vulnerabilities like:
// - SQL Injection in product forms
// - Cross-Site Scripting (XSS) in product names/descriptions if not sanitized on display
// - Insecure File Uploads for product images
// - Missing CSRF protection on forms

// TODO: Add functionality for managing users (View/Edit/Delete)
// Vulnerabilities: Exposing sensitive data, insecure password handling, etc.

$page_title = "Admin Panel - QuickBite Delivery";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Simple styling for sort links */
        th a { color: #333; text-decoration: underline; }
        th a:hover { color: #dc3545; }
    </style>
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
        <h2>Admin Dashboard</h2>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <section id="orders">
            <h3>Manage Orders</h3>
            <?php if (empty($orders) && !$error_message): ?>
                <p>No orders found.</p>
            <?php elseif (!empty($orders)): ?>
                <table>
                    <thead>
                        <tr>
                             <!-- Vulnerable Sorting Links -->
                            <th><a href="admin.php?sort=id&order=<?php echo ($sort_column == 'id' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Order ID</a></th>
                            <th><a href="admin.php?sort=username&order=<?php echo ($sort_column == 'username' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">User</a></th>
                            <th><a href="admin.php?sort=total_amount&order=<?php echo ($sort_column == 'total_amount' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Total</a></th>
                            <th><a href="admin.php?sort=status&order=<?php echo ($sort_column == 'status' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Status</a></th>
                            <th><a href="admin.php?sort=order_date&order=<?php echo ($sort_column == 'order_date' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Date</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?> (ID: <?php echo $order['user_id']; ?>)</td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                     <?php echo htmlspecialchars($order['status']); ?>
                                     <!-- Basic status update form (needs implementation and CSRF protection) -->
                                     <!-- <form action="admin.php" method="post" style="display:inline;">
                                         <input type="hidden" name="action" value="update_status">
                                         <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                         <select name="new_status" onchange="this.form.submit()">
                                             <option value="pending" <?php if($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                             <option value="processing" <?php if($order['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                             <option value="shipped" <?php if($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                             <option value="delivered" <?php if($order['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                             <option value="cancelled" <?php if($order['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                         </select>
                                     </form> -->
                                </td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td>
                                    <a href="admin_order_details.php?order_id=<?php echo $order['id']; ?>" class="btn" style="background-color: #17a2b8;">Details</a>
                                     <!-- Delete order button (needs implementation, CSRF, authorization) -->
                                    <!-- <form action="admin.php" method="post" style="display:inline;">
                                         <input type="hidden" name="action" value="delete_order">
                                         <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                         <button type="submit" class="btn" style="background-color: #dc3545;" onclick="return confirm('Are you sure?');">Delete</button>
                                     </form> -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <hr style="margin: 30px 0;">

        <section id="products">
            <h3>Manage Products</h3>
            <p><i>Product management (Add/Edit/Delete) functionality to be added here.</i></p>
            <!-- Placeholder for product management table/forms -->
             <a href="#" class="btn">Add New Product</a>
        </section>

         <hr style="margin: 30px 0;">

        <section id="users">
            <h3>Manage Users</h3>
            <p><i>User management (View/Edit Roles/Delete) functionality to be added here.</i></p>
            <!-- Placeholder for user management table/forms -->
        </section>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> QuickBite Delivery. All rights reserved.</p>
         <p style="font-size: 0.8em; color: #aaa;">Disclaimer: This website is for educational purposes only and contains intentional security vulnerabilities.</p>
    </footer>
</body>
</html> 