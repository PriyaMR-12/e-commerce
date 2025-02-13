<?php
session_start();
include('functions.php');

$conn = connectToDatabase();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$userId = $_SESSION['user_id']; 
$productId = $_POST['product_id'];

// Check if the product exists in the products table
$checkProductQuery = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($checkProductQuery);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Error: Product not found."; 
    exit;
}

// Check if the product is already in the cart
$checkCartQuery = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($checkCartQuery);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Product already in cart, update quantity
    $updateQuery = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
} else {
    // Add product to cart
    $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
}

mysqli_close($conn);

echo "success"; 
?>