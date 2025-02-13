<?php
session_start();
include('functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectToDatabase();

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Fetch user from the database
    $sql = "SELECT user_id, username, password_hash FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Store user_id and username in the session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            echo "Login successful. User ID: " . $user['user_id'];

            // Redirect to homepage or dashboard
            header("Location: index.html");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

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
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .modal button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

    </style>
</head>
<body>
    <h1>Login</h1>
    <form method="post">
        <input type="email" name="email" placeholder="Email"  required autocomplete="username"><br><br>
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password"><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
