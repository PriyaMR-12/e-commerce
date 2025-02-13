<?php
session_start();
include('functions.php');

$conn = connectToDatabase();

$user_id = $_SESSION['user_id']; 

if (!$user_id) {
    die('User not logged in.');
}

// Fetch order history for the current user
$query = "SELECT o.order_id, o.total_amount, o.status, o.created_at 
          FROM orders o
          WHERE o.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History</title> 
</head>
<body>
<div style=" text-align: center; background-color: #333;padding: 20px ">
       <a href="index.html" style="text-decoration: none; color: white;">Home</a>
    </div>
    <h2>Order History</h2>

    <?php if (empty($order_history)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_history as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>


</body>
</html>