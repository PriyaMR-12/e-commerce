<?php
session_start();  // Start the session to access session data
include('functions.php');

$conn = connectToDatabase();

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Debugging: Print session variable to verify if 'user_id' exists
// echo 'User ID: ' . $_SESSION['user_id'];
// if (isset($_SESSION['user_id'])) {
//     echo "User ID: " . $_SESSION['user_id'];  // Debugging line
// } else {
//     echo "No user logged in. Cart will be empty.";  // Error message if not logged in
// }
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Get user_id from session if set

if ($userId) {
    // Query for logged-in users
    $sql = "
    SELECT 
        ci.quantity, 
        p.name, 
        p.price, 
        p.image, 
        ci.product_id, 
        (ci.quantity * p.price) AS total_price 
    FROM 
        cart_items ci 
    JOIN 
        products p ON ci.product_id = p.product_id 
    JOIN 
        cart c ON ci.cart_id = c.cart_id 
    WHERE 
        c.user_id = ?;
";


$stmt = $conn->prepare($sql); 

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        die("Error fetching cart items: " . $stmt->error); // Debugging error if query fails
    }

    $result = $stmt->get_result();

    // Check if the query returned any results
    // if ($result->num_rows > 0) {
    //     echo 'Cart items found for user: ' . $userId; // Debugging message
    // } else {
    //     echo 'No cart items found for user: ' . $userId; // Debugging message
    // }
} else {
    // Query for non-logged-in users (if you want to show a general cart)
    echo "No user logged in. Cart will be empty."; // Debugging message
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        img {
            width: 100px;
            height: auto;
        }

        .total-price {
            font-weight: bold;
            margin-top: 10px;
        }
        header {
            background-color: #333;
            color:white;
            padding: 20px;
           text-align: center;
        }
        nav a {
            color:white;
            margin: 0 15px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<header>
        <h1>Your cart</h1>
        <nav>
          <a href="./index.html">Home</a>         
        </nav>
    </header>
    <br><br>
    <?php if (isset($result) && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th></th>
        </tr>

        <?php 
        $totalAmount = 0; 
        while ($row = $result->fetch_assoc()):
            $totalAmount += $row['total_price'];
        ?>
            <tr>
                <td>
                    <img src="<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>"> 
                    <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>Rs. <?php echo number_format($row['total_price'], 2); ?></td>
                <td>
                    <form action="place_order.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>"> 
                        <input type="hidden" name="quantity" value="<?php echo $row['quantity']; ?>">
                        <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                        <button type="submit">Place Order</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>


</body>
</html>

<?php
// Close the prepared statement and database connection
if (isset($stmt)) {
    $stmt->close();
    $conn->close();
  }
  ?>
