<?php
session_start();
include('functions.php'); 
$conn = connectToDatabase();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        .card { margin-bottom: 20px; }
        img { height: 400px; }
        header {
    background-color: #333;
    color: white;
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
        <nav>
            <a href="./index.html">Home</a>
            <a href="./cart.php">Cart</a>
        </nav>
    </header>
    <h1>Available Products</h1>

    <div class="container">
        <div class="row">
            <?php
            // Fetch products from the database
            $sql = "SELECT product_id, name,description, price, image FROM products WHERE category_id = 2 ";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
            <div class="col-md-4">
                <div class="card" style="width: 18rem;">
                    <img src="<?php echo $row['image']; ?>" 
                         alt="<?php echo $row['name']; ?>" 
                         loading="lazy" 
                         class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['name']; ?></h5>
                        <h5 class="card-title"><?php echo $row['description']; ?></h5>
                        <p class="card-text">Price: Rs.<?php echo number_format($row['price'], 2); ?></p>
                        <button class="btn btn-primary add-to-cart" 
            data-id="<?php echo htmlspecialchars($row['product_id'], ENT_QUOTES, 'UTF-8'); ?>">
        Add to Cart
    </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No products found.</p>"; 
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <script>
$('.add-to-cart').click(function() {
    var productId = $(this).data('id');

    $.ajax({
        url: "add_to_cart.php",
        type: "POST",
        data: { product_id: productId },
        dataType: 'json', // Specify that you expect a JSON response
        success: function(response) {
            if (response.success) {
                alert(response.message); 
            } else {
                alert(response.message || "Error adding to cart."); 
            }
        },
        error: function(xhr, status, error) {
            if (xhr.status === 500) { // Handle 500 Internal Server Error
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    alert("Server Error: " + errorResponse.message);
                } catch (parseError) {
                    alert("Server Error: " + xhr.responseText); 
                }
            } else {
                alert("AJAX Error: " + status + " - " + error); 
            }
        }
    });
});
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>