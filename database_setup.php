<?php
$db_path = __DIR__ . '/db/delivery_app.sqlite';
$db_dir = dirname($db_path);

if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'user' NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        image TEXT
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cart_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        product_id INTEGER,
        quantity INTEGER NOT NULL DEFAULT 1,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        total_amount REAL NOT NULL,
        status TEXT DEFAULT 'pending',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

     $db->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER,
        product_id INTEGER,
        quantity INTEGER,
        price REAL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    // Add a default admin user (insecure password!)
    $admin_exists = $db->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($admin_exists == 0) {
        // Storing password in plain text - highly insecure
        $db->exec("INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin')");
    }

    // Add some dummy products
    $product_count = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($product_count == 0) {
         $db->exec("INSERT INTO products (name, description, price, image) VALUES
            ('Pizza Margherita', 'Classic pizza with tomatoes, mozzarella, and basil.', 9.99, 'images/pizza.jpg'),
            ('Cheeseburger', 'Juicy beef patty with cheese, lettuce, and tomato.', 7.50, 'images/burger.jpg'),
            ('Caesar Salad', 'Crisp romaine lettuce with Caesar dressing, croutons, and Parmesan cheese.', 6.25, 'images/salad.jpg'),
            ('Sushi Platter', 'Assortment of fresh sushi rolls.', 15.00, 'images/sushi.jpg')
        ");
    }


    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}
?> 