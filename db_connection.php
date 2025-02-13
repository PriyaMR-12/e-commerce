<?php
// db_connect.php

$host = 'localhost'; // or your host
$username = 'root';  // your DB username
$password = '';      // your DB password
$dbname = 'ecommerce_db'; // your DB name

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully.";
}
?>
