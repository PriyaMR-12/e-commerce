<?php
session_start();
include('functions.php');

$conn = connectToDatabase();

$order_id = intval($_GET['order_id']); // Ensure it's an integer
$user_id = $_SESSION['user_id']; // Ensure user is logged in

if (!$order_id || !$user_id) {
    die('Invalid request.');
}

// Fetch order and item details
$query = "SELECT o.order_id, o.total_amount, o.status, o.created_at, 
               oi.product_id, oi.quantity, oi.price, p.name 
          FROM orders o
          JOIN order_items oi ON o.order_id = oi.order_id
          JOIN products p ON oi.product_id = p.product_id
         WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order_details = $result->fetch_all(MYSQLI_ASSOC);

if (empty($order_details)) {
    die("Invalid order ID or unauthorized access.");
}

$total_amount = $order_details[0]['total_amount']; // Extract total amount

?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        button {
            padding: 10px;
            background-color: #231698;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div style=" text-align: center; background-color: #333;padding: 20px ">
       <a href="index.html" style="text-decoration: none; color: white;">Home</a>
    </div>

    <h2>Order Confirmation</h2>
    <p>Order ID: <?php echo htmlspecialchars($order_details[0]['order_id']); ?></p>
    <p>Total Amount: $<?php echo number_format($total_amount, 2); ?></p>
    <p>Status: <?php echo htmlspecialchars($order_details[0]['status']); ?></p>
    <p>Order placed on: <?php echo htmlspecialchars($order_details[0]['created_at']); ?></p>

    <h3>Products in this order:</h3>
    <ul>
        <?php foreach ($order_details as $item): ?>
            <li>
                <?php echo htmlspecialchars($item['name']); ?> -
                Quantity: <?php echo $item['quantity']; ?>,
                Price: $<?php echo number_format($item['price'], 2); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php 
    // Simulated "Pay Now" button
    echo "<button onclick=\"simulatePayment()\">Pay Now: $" . number_format($total_amount, 2) . "</button>"; 
    ?>

    <script>
        function simulatePayment() {
            // Simulate successful payment
            alert("Payment Successful! Order #" + <?php echo $order_id; ?> + " has been processed.");

            // Optionally, update order status (you would use your actual database logic here)
            // This is a simplified example
            // <?php 
            //     $update_status_query = "UPDATE orders SET status = 'Paid' WHERE order_id = ?";
            //     $stmt = $conn->prepare($update_status_query);
            //     $stmt->bind_param("i", $order_id);
            //     $stmt->execute();
            //     $stmt->close();
            // ?>

            // Redirect to order history or a "Thank You" page
            window.location.href = "order_history.php"; 
        }
    </script>

</body>
</html>