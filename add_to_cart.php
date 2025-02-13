<?php
session_start();
include('functions.php'); 
// Ensure JSON response
header('Content-Type: application/json'); 



// Debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate user session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'User not logged in!']);
    exit;
}

// Validate product data
if (!isset($_POST['product_id']) || !filter_var($_POST['product_id'], FILTER_VALIDATE_INT) || intval($_POST['product_id']) <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid product data!']);
    exit;
}

$productStmt = null;
$checkStmt = null;
$updateStmt = null;
$insertItemStmt = null;

try {
    // Establish database connection
    $conn = connectToDatabase();
if (!$conn) {
    throw new Exception("Database connection failed: " . mysqli_connect_error());
}

    $userId = $_SESSION['user_id'];
    $productId = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) && $_POST['quantity'] > 0 ? (int) $_POST['quantity'] : 1;

    // Get Cart ID (or create a new cart if it doesn't exist)
    $cartId = getOrCreateCartId($conn, $userId); 

    // Check if the product exists 
    $productSql = "SELECT product_id FROM products WHERE product_id = ?";
    $productStmt = $conn->prepare($productSql);
    $productStmt->bind_param("i", $productId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();

    if ($productResult->num_rows === 0) {
        throw new Exception("Product not found!");
    }

    // Check if product is already in the cart and update or insert
    $checkSql = "SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $cartId, $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update quantity
        $updateSql = "UPDATE cart_items SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iii", $quantity, $cartId, $productId);
        if (!$updateStmt->execute()) {
            throw new Exception("Error updating cart: " . $updateStmt->error . " - " . $conn->error); 
        }
        $message = "Product quantity updated in cart!";
    } else {
        // Add new product to cart
        $insertItemSql = "INSERT INTO cart_items (cart_id, product_id, quantity, user_id) VALUES (?, ?, ?, ?)";
        $insertItemStmt = $conn->prepare($insertItemSql);
        $insertItemStmt->bind_param("iiii", $cartId, $productId, $quantity, $userId); 
        if (!$insertItemStmt->execute()) {
            throw new Exception("Error adding product to cart: " . $insertItemStmt->error . " - " . $conn->error); 
        }
        $message = "Product added to cart!";
    }

    echo json_encode(['success' => true, 'message' => $message]); 

} catch (Exception $e) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]); 
} finally {
    // Close all prepared statements and database connection
    foreach ([$productStmt, $checkStmt, $updateStmt, $insertItemStmt] as $stmt) {
        if ($stmt) {
            $stmt->close();
        }
    }
    if ($conn) {
        $conn->close();
    }
}
function getOrCreateCartId($conn, $userId) {
    $cartSql = "SELECT cart_id FROM cart WHERE user_id = ?";
    $cartStmt = $conn->prepare($cartSql);
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();

    if ($cartResult->num_rows > 0) {
        $row = $cartResult->fetch_assoc();
        return $row['cart_id'];
    } else {
        $insertCartSql = "INSERT INTO cart (user_id) VALUES (?)";
        $insertCartStmt = $conn->prepare($insertCartSql);
        $insertCartStmt->bind_param("i", $userId);
        if (!$insertCartStmt->execute()) {
            throw new Exception("Error creating cart: " . $insertCartStmt->error . " - " . $conn->error); 
        }
        $cartId = $conn->insert_id;
        $insertCartStmt->close();
        return $cartId;
    }
}