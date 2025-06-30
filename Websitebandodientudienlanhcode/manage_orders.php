<?php

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

// Handle order status update
if (isset($_GET['update']) && isset($_GET['status'])) {
    $orderId = intval($_GET['update']);
    $newStatus = $_GET['status'];
    $stmt = $conn->prepare("UPDATE donhang SET trangthai = ? WHERE iddonhang = ?");
    $stmt->bind_param("si", $newStatus, $orderId);
    if ($stmt->execute()) {
        echo "<script>alert('Order status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating order status: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Fetch all orders
$sql = "SELECT donhang.iddonhang, donhang.id_nguoidung, donhang.tongtien, donhang.trangthai, donhang.ngaytao, dangky.hovaten 
        FROM donhang 
        JOIN dangky ON donhang.id_nguoidung = dangky.idnguoidung 
        ORDER BY donhang.ngaytao DESC";
$result = $conn->query($sql);
$orders = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .update-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .update-button:hover {
            background-color: #218838;
        }
        .cancel-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .cancel-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h1>Quản lý đơn hàng </h1>

    <h2>Danh sách đơn hàng </h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Customer Name</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['iddonhang']; ?></td>
                    <td><?php echo $order['id_nguoidung']; ?></td>
                    <td><?php echo htmlspecialchars($order['hovaten']); ?></td>
                    <td><?php echo number_format($order['tongtien'], 2, ',', '.'); ?> VND</td>
                    <td><?php echo htmlspecialchars($order['trangthai']); ?></td>
                    <td><?php echo htmlspecialchars($order['ngaytao']); ?></td>
                    <td>
                        <?php if ($order['trangthai'] === 'cho_xac_nhan'): ?>
                            <button class="update-button" onclick="window.location.href='?update=<?php echo $order['iddonhang']; ?>&status=da_xac_nhan'">Confirm</button>
                            <button class="cancel-button" onclick="window.location.href='?update=<?php echo $order['iddonhang']; ?>&status=da_huy'">Cancel</button>
                        <?php elseif ($order['trangthai'] === 'da_xac_nhan'): ?>
                            <button class="update-button" onclick="window.location.href='?update=<?php echo $order['iddonhang']; ?>&status=dang_giao'">Ship</button>
                        <?php elseif ($order['trangthai'] === 'dang_giao'): ?>
                            <button class="update-button" onclick="window.location.href='?update=<?php echo $order['iddonhang']; ?>&status=da_giao'">Delivered</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
      <p>
        
        <a href="admin.php">Quản trị Admin</a>
    </p>
</body>
</html>
