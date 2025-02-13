<?php
require_once 'db_connection.php'; // Database connection file

$userId = $_GET['user_id']; // Pass user ID via query string

$query = "SELECT c.quantity, c.added_at, p.name, p.price, p.image_url 
          FROM cart c
          JOIN products p ON c.product_id = p.id
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

if (empty($cartItems)) {
    echo "<p>Your cart is empty.</p>";
} else {
    foreach ($cartItems as $item) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px; display: flex; align-items: center;'>
                <img src='{$item['image_url']}' alt='Product Image' style='width: 100px; height: 100px; margin-right: 20px;'>
                <div>
                    <p><strong>Product:</strong> {$item['name']}</p>
                    <p><strong>Quantity:</strong> {$item['quantity']}</p>
                    <p><strong>Price:</strong> {$item['price']}</p>
                </div>
              </div>";
    }
}
?>
