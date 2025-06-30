<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set PHP's default timezone


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "123456";
    $dbname = "quanlydodientu";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $basketData = isset($_POST['basketData']) ? json_decode($_POST['basketData'], true) : [];
    $userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : null;

    if (!$userEmail || empty($basketData)) {
        echo "Invalid order data.";
        $conn->close();
        exit();
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $product_price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;

    if ($product_id > 0 && $quantity > 0) {
        // Check if the product exists and has enough stock
        $stmt = $conn->prepare("SELECT soluong FROM sanpham WHERE mahang = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($available_quantity);

        if ($stmt->fetch()) { // Check if the product exists
            if ($available_quantity >= $quantity) {
                // Deduct the quantity from the stock
                $stmt->close();
                $stmt = $conn->prepare("UPDATE sanpham SET soluong = soluong - ? WHERE mahang = ?");
                $stmt->bind_param("ii", $quantity, $product_id);
                $stmt->execute();
                $stmt->close();

                // Insert the order into `donhang` table
                $stmt = $conn->prepare("INSERT INTO donhang (id_nguoidung, tongtien, trangthai, ngaytao) VALUES (?, ?, 'cho_xac_nhan', NOW())");
                $totalAmount = $quantity * $product_price;
                $userId = 1; // Replace with the actual user ID from your session or database
                $stmt->bind_param("id", $userId, $totalAmount);

                if ($stmt->execute()) {
                    $orderId = $stmt->insert_id;

                    // Insert order details into `chitietdonhang` table
                    $stmtDetail = $conn->prepare("INSERT INTO chitietdonhang (donhang_id, tenhang, soluong, gia) VALUES (?, ?, ?, ?)");
                    $stmtDetail->bind_param("isid", $orderId, $product['tenhang'], $quantity, $product_price);
                    $stmtDetail->execute();
                    $stmtDetail->close();

                    echo "Order placed successfully!";
                } else {
                    echo "Failed to place order.";
                }

                $stmt->close();
            } else {
                echo "Not enough stock available.";
            }
        } else {
            echo "Product does not exist.";
        }
        $stmt->close();
    } else {
        echo "Invalid product or quantity.";
    }

    $conn->close();
}
?>
