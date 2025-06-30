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

// Fetch sales data
$sql = "SELECT donhang.iddonhang, donhang.ngaytao, donhang.tongtien, dangky.hovaten 
        FROM donhang 
        JOIN dangky ON donhang.id_nguoidung = dangky.idnguoidung 
        ORDER BY donhang.ngaytao DESC";
$result = $conn->query($sql);
$sales = $result->fetch_all(MYSQLI_ASSOC);

// Fetch successful deliveries
$sql = "SELECT donhang.iddonhang, donhang.ngaytao, donhang.tongtien, dangky.hovaten, donhang.trangthai 
        FROM donhang 
        JOIN dangky ON donhang.id_nguoidung = dangky.idnguoidung 
        WHERE donhang.trangthai = 'da_giao'
        ORDER BY donhang.ngaytao DESC";
$result = $conn->query($sql);
$successfulOrders = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total revenue
$totalRevenue = array_sum(array_column($successfulOrders, 'tongtien'));

// Get filter values
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Monthly statistics
$monthlyStats = "SELECT 
    MONTH(ngaytao) as month,
    YEAR(ngaytao) as year,
    COUNT(*) as total_orders,
    SUM(tongtien) as total_revenue
    FROM donhang 
    WHERE trangthai = 'da_giao'
    AND YEAR(ngaytao) = ?
    GROUP BY YEAR(ngaytao), MONTH(ngaytao)
    ORDER BY year DESC, month DESC";

$stmt = $conn->prepare($monthlyStats);
$stmt->bind_param("i", $year);
$stmt->execute();
$monthlyResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Yearly statistics
$yearlyStats = "SELECT 
    YEAR(ngaytao) as year,
    COUNT(*) as total_orders,
    SUM(tongtien) as total_revenue
    FROM donhang 
    WHERE trangthai = 'da_giao'
    GROUP BY YEAR(ngaytao)
    ORDER BY year DESC";

$yearlyResults = $conn->query($yearlyStats)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
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
        .summary-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .total-revenue {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
        .success-header {
            color: #28a745;
            margin-top: 30px;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stats-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-form {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-form select {
            padding: 5px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Sales Reports</h1>

    <div class="filter-form">
        <form method="GET">
            <select name="month">
                <?php for($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $month ? 'selected' : ''; ?>>
                        Tháng <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year">
                <?php 
                $currentYear = date('Y');
                for($i = $currentYear; $i >= $currentYear - 5; $i--): 
                ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $year ? 'selected' : ''; ?>>
                        Năm <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button type="submit">Lọc</button>
        </form>
    </div>

    <div class="summary-box">
        <h3>Tổng doanh thu từ đơn hàng thành công:</h3>
        <div class="total-revenue">
            <?php echo number_format($totalRevenue, 0, ',', '.'); ?> VND
        </div>
    </div>

    <div class="stats-container">
        <div class="stats-box">
            <h3>Thống kê theo tháng (<?php echo $month; ?>/<?php echo $year; ?>)</h3>
            <?php
            $monthlyData = array_filter($monthlyResults, function($item) use ($month, $year) {
                return $item['month'] == $month && $item['year'] == $year;
            });
            $monthlyData = reset($monthlyData);
            ?>
            <p>Số đơn hàng: <?php echo $monthlyData ? $monthlyData['total_orders'] : 0; ?></p>
            <p>Doanh thu: <?php echo $monthlyData ? number_format($monthlyData['total_revenue'], 0, ',', '.') : 0; ?> VND</p>
        </div>

        <div class="stats-box">
            <h3>Thống kê theo năm (<?php echo $year; ?>)</h3>
            <?php
            $yearlyData = array_filter($yearlyResults, function($item) use ($year) {
                return $item['year'] == $year;
            });
            $yearlyData = reset($yearlyData);
            ?>
            <p>Số đơn hàng: <?php echo $yearlyData ? $yearlyData['total_orders'] : 0; ?></p>
            <p>Doanh thu: <?php echo $yearlyData ? number_format($yearlyData['total_revenue'], 0, ',', '.') : 0; ?> VND</p>
        </div>
    </div>

    <h2 class="success-header">Đơn hàng giao thành công</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($successfulOrders as $order): ?>
                <tr>
                    <td><?php echo $order['iddonhang']; ?></td>
                    <td><?php echo htmlspecialchars($order['hovaten']); ?></td>
                    <td><?php echo htmlspecialchars($order['ngaytao']); ?></td>
                    <td><?php echo number_format($order['tongtien'], 0, ',', '.'); ?> VND</td>
                    <td><span style="color: #28a745;">Đã giao</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        <p>
        
        <a href="admin.php">Quản trị Admin</a>
    </p>
</body>
</html>