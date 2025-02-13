<?php
// Database connection function
function connectToDatabase() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'ecommerce_db';

    $conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

// Sanitize user input to prevent XSS and SQL injection
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to a specific page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Display a success message
function displaySuccess($message) {
    echo "<div class='alert alert-success'>$message</div>";
}

// Display an error message
function displayError($message) {
    echo "<div class='alert alert-danger'>$message</div>";
}

// Fetch all products from the database
function getAllProducts() {
    $conn = connectToDatabase();
    $query = "SELECT * FROM products";
    $result = mysqli_query($conn, $query);

    $products = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    mysqli_close($conn);
    return $products;
}

function addToCart($userId, $productId) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM cart_item WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity
        $updateStmt = $conn->prepare("UPDATE cart_item SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $updateStmt->bind_param("ii", $userId, $productId);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // Add new product to cart
        $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (NULL, ?, 1)");
        $insertStmt->bind_param("i", $productId);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $stmt->close();
    $conn->close();
}

function getCartItems($userId) {
    $conn = connectToDatabase();
    $query = "SELECT ci.cart_item_id, p.product_id, p.product_name, p.price, ci.quantity 
              FROM cart_item ci
              JOIN products p ON ci.product_id = p.product_id
              WHERE ci.user_id = $userId"; 
    $result = mysqli_query($conn, $query);

    $cartItems = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cartItems[] = $row;
        }
    }

    mysqli_close($conn);
    return $cartItems;
}
?>
