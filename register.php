<?php
session_start();
include('functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectToDatabase();

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert user into the database
    $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        // Fetch the newly created user_id
        $userId = $stmt->insert_id;

        // Store user_id in the session
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;

        echo "Registration successful. User ID: " . $userId;

        // Redirect to homepage or dashboard
        header("Location: index.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
</head>
<body>
    </body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
            body{
    text-align: center;
}
input{
    padding: 15px;
}
button{
    padding: 15px;
    width: 100px;
}
  </style>
</head>
<body>
  <h1>Register</h1>
  <form method="post">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
  </form>
</body>
</html>