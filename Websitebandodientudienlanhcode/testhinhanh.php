<?php
// get_image.php

// Database connection details
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "quanlydodientu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if mahang parameter is set, if not, set it to 1
if (isset($_GET['mahang'])) {
    $mahang = $_GET['mahang'];
} else {
    $mahang = 1; // Default to mahang = 1
}

// Prepare and execute the SQL query
$stmt = $conn->prepare("SELECT hinhanh FROM sanpham WHERE mahang = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $mahang); // "i" for integer
$stmt->execute();
$stmt->bind_result($imageData);

if ($stmt->fetch()) {
    // Set the appropriate Content-Type header
    header("Content-Type: image/jpeg"); // Adjust based on actual image type
    echo $imageData;
} else {
    // Image not found
    echo "Image not found.";
}

$stmt->close();
$conn->close();
?>