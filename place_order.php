<?php
session_start();
include('functions.php');

// Connect to the database
$conn = connectToDatabase();

// Check for connection error
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get user ID from session (ensure user is logged in)
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die('User not logged in.');
}

// Get product ID from POST request (ensure it's an integer)
$product_id = intval($_POST['product_id'] ?? 0);

// Retrieve product information
$query = "SELECT p.price 
          FROM products p 
          WHERE p.product_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

// Check for invalid product ID
if (empty($item)) {
    die('Invalid product ID.');
}

// Set total amount to the product's price
$total_amount = $item['price'];

// Insert into orders table
$query = "INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'Pending', NOW())";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("id", $user_id, $total_amount);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert into order_items table
$query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, 1, ?)";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("iii", $order_id, $product_id, $item['price']);
$stmt->execute();
$stmt->close();

// Clear the specific cart item (optional)
$query = "DELETE FROM cart_items WHERE product_id = ? AND cart_id = (SELECT cart_id FROM cart WHERE user_id = ?)";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$stmt->close();

// Retrieve notification message (improved with error handling)
$notification_query = "SELECT message FROM notifications WHERE user_id = ? AND notification_id = ?";
$notification_stmt = $conn->prepare($notification_query);
if ($notification_stmt) {
    $notification_stmt->bind_param("ii", $user_id, $order_id); 
    $notification_stmt->execute();
    $notification_result = $notification_stmt->get_result();

    if ($notification_row = $notification_result->fetch_assoc()) {
        $notification_message = $notification_row['message'];
    } else {
        $notification_message = "Order placed successfully!"; // Default message
    }
    $notification_stmt->close();
} else {
    // Handle error preparing notification statement
    die("Error preparing notification statement: " . $conn->error); 
}

// Output HTML with SweetAlert2 stylesheet
echo '<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> 
</head>
<body>';

// Include SweetAlert2 script
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

// Display notification using JavaScript (with SweetAlert2)
echo "<script>
Swal.fire({
    title: 'Success!',
    text: '$notification_message', 
    icon: 'success',
    confirmButtonText: 'OK'
}).then((result) => { 
    if (result.isConfirmed) {
        window.location.href = 'order_confirmation.php?order_id=" . $order_id . "'; 
    }
});
</script>";

echo '</body>
</html>';

// Close the database connection
$conn->close();
?>