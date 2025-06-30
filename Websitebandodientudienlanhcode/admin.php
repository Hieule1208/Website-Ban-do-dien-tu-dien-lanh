<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 30%;
            text-align: center;
            margin-bottom: 20px;
        }
        .card h2 {
            color: #333;
        }
        .card button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .card button:hover {
            background-color: #218838;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .logout button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quản trị viên Dashboard</h1>
        <p>Chào mừng đến với bảng điều khiển quản trị viên.</p>
    </div>
    <div class="container">
        <div class="card">
            <h2>Quản lý sản phẩm</h2>
            <p>Thêm,sửa hoặc xóa sản phẩm trong cửa hàng.</p>
            <button onclick="window.location.href='manage_products.php'">Đi đến sản phẩm </button>
        </div>
        <div class="card">
            <h2>Quản lý đơn hàng </h2>
            <p>Xem và quản lý đơn hàng.</p>
            <button onclick="window.location.href='manage_orders.php'">Đi đến đơn hàng</button>
        </div>
        <div class="card">
            <h2>Quản lý người dùng </h2>
            <p>Thêm,sửa hoặc xóa người dùng .</p>
            <button onclick="window.location.href='manage_users.php'">Đi đến người dùng </button>
        </div>
        <div class="card">
            <h2>Báo cáo </h2>
            <p>Phân tích và báo cáo.</p>
            <button onclick="window.location.href='view_reports.php'">Xem báo cáo </button>
        </div>
        <div class="card">
            <h2>Quản lý danh mục </h2>
            <p>Thêm,sửa hoặc xóa danh mục.</p>
            <button onclick="window.location.href='manage_category.php'">Đi đến danh mục</button>
        </div>
    </div>
    <div class="logout">
        <button onclick="window.location.href='admin_logout.php'">Logout</button>
    </div>
</body>
</html>
