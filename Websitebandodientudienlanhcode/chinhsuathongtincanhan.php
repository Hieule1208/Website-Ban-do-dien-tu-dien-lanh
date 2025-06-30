<?php
session_start(); // Start the session

// Initialize variables
$email = '';
$password = '';
$orders = []; // Initialize orders as an empty array to avoid warnings
$userId = $_SESSION['userId'] ?? null; // Assuming you store user ID in session after login
$taikhoan = $_SESSION['taikhoan'] ?? null; // Store taikhoan in session after login
$hovaten = $_SESSION['hovaten'] ?? ''; // Assuming you store the full name in session

$servername = "localhost"; // Database server
$username = "root"; // Database username
$passwordDB = "123456"; // Database password
$dbname = "quanlydodientu"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $passwordDB, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info from database if logged in
if (isset($_SESSION['taikhoan'])) {
    $taikhoan = $_SESSION['taikhoan'];
    $stmt = $conn->prepare("SELECT email, hovaten, sodienthoai, gioitinh, ngaysinh, diachi FROM dangky WHERE taikhoan = ?");
    $stmt->bind_param("s", $taikhoan);
    $stmt->execute();
    $stmt->bind_result($email, $hovaten, $sodienthoai, $gioitinh, $ngaysinh, $diachi);
    if ($stmt->fetch()) {
        // Variables are now set for the form
    }
    $stmt->close();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Get data from the login form
        $taikhoan = $_POST['email'];
        $password = $_POST['password'];

        // Check user credentials
        $stmt = $conn->prepare("SELECT idnguoidung, matkhau FROM dangky WHERE taikhoan = ?");
        $stmt->bind_param("s", $taikhoan);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();
            if (password_verify($password, $hashedPassword)) {
                // Store user ID and taikhoan in session
                $_SESSION['userId'] = $userId;
                $_SESSION['taikhoan'] = $taikhoan; // Store taikhoan in session
            } else {
                echo "<script>alert('Mật khẩu không đúng!');</script>";
            }
        } else {
            echo "<script>alert('Tài khoản không tồn tại!');</script>";
        }
        $stmt->close();
    }

    if (isset($_POST['basketData'])) {
        $basketItems = json_decode($_POST['basketData'], true);
        foreach ($basketItems as $item) {
            $orders[] = [
                'product' => $item['name'],
                'price' => number_format($item['price'], 0, ',', '.') . ' VND',
                'quantity' => $item['quantity']
            ];
        }

        // Insert order into `donhang` table
        $stmt = $conn->prepare("INSERT INTO donhang (id_nguoidung, ngaytao, tongtien) VALUES (?, NOW(), ?)");
        $totalAmount = array_reduce($basketItems, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
        $stmt->bind_param("id", $userId, $totalAmount); // Use userId instead of email

        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;

            // Insert order details into `chitietdonhang` table
            $stmtDetail = $conn->prepare("INSERT INTO chitietdonhang (iddonhang, mahang, soluong, gia) VALUES (?, ?, ?, ?)");
            foreach ($basketItems as $item) {
                $stmtDetail->bind_param("isid", $orderId, $item['id'], $item['quantity'], $item['price']); // Use $item['id'] for mahang
                $stmtDetail->execute();
            }

            echo "<script>alert('Đơn hàng đã được lưu thành công!');</script>";
        } else {
            echo "<script>alert('Lỗi khi lưu đơn hàng: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }

    if (isset($_POST['save'])) {
        // Get the updated user data from the form
        $email = $_POST['email'];
        $password = $_POST['password'];
        $sodienthoai = $_POST['sodienthoai'];
        $ngaysinh = $_POST['ngaysinh'];
        $diachi = $_POST['diachi'];
        $gioitinh = $_POST['gioitinh'];
        $hovaten = $_POST['fullname'];
    
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        // Ensure the session has the 'taikhoan' set from login
        if (isset($_SESSION['taikhoan'])) {
            $taikhoan = $_SESSION['taikhoan']; // Get the taikhoan from the session
    
            // Update the user information in the database
            $sql = "UPDATE dangky SET email = ?, matkhau = ?, sodienthoai = ?, ngaysinh = ?, diachi = ?, gioitinh = ?, hovaten = ? WHERE taikhoan = ?";
            $stmt = $conn->prepare($sql);
    
            // Bind the parameters: 7 fields to update, 1 for the WHERE condition (taikhoan)
            $stmt->bind_param("ssssssss", $email, $hashedPassword, $sodienthoai, $ngaysinh, $diachi, $gioitinh, $hovaten, $taikhoan);
    
            // Execute the query
            if ($stmt->execute()) {
                echo "<script>alert('Thông tin đã được lưu thành công!');</script>";
            } else {
                echo "<script>alert('Lỗi khi lưu thông tin: " . $stmt->error . "');</script>";
            }
    
            $stmt->close();
        } else {
            // If taikhoan is not set in the session, the user may not be logged in
            echo "<script>alert('Vui lòng đăng nhập để cập nhật thông tin.');</script>";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Người Dùng</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h1 {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        .container {
            max-width: 700px;
            width: 100%;
            position: relative;
            padding-top: 60px; /* Prevent overlap with fixed home icon */
        }

        button {
            background: #ff6f61;
            color: white;
            border: none;
            padding: 12px 25px;
            margin: 10px;
            cursor: pointer;
            border-radius: 25px;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: #ff483b;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .thongtin, .donhang {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            transform: scale(0.95);
            opacity: 0;
        }

        .thongtin.active, .donhang.active {
            transform: scale(1);
            opacity: 1;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            display: flex;
            align-items: center;
            margin: 15px 0;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        label {
            font-weight: bold;
            color: #333;
            width: 30%;
            text-align: right;
            margin-right: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        input[type="password"] {
            width: 70%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 5px rgba(255, 111, 97, 0.5);
        }

        .gender {
            margin: 0 10px;
            display: flex;
            align-items: center;
        }

        .gender input {
            margin-left: 5px;
        }

        .save-btn {
            background: #28a745;
            width: 100%;
            padding: 12px;
            font-size: 1.2em;
        }

        .save-btn:hover {
            background: #218838;
        }

        .donhang h2 {
            color: #ff6f61;
            margin-bottom: 20px;
        }

        .home-icon {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .home-icon:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="testtrangchu.php" class="home-icon">
            <img src="img/home.png" alt="Home" style="width: 40px; height: auto;">
        </a>
        <h1>Hồ sơ của bạn</h1>
        
        <button onclick="toggleInfo()">Hiện thông tin</button>
        <button onclick="toggleOrders()">Đơn hàng</button>

        <div class="thongtin">
            <form method="post">
                <ul class="hienthongtin">
                    <li>
                        <label>Email:</label>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </li>
                    <li>
                        <label>Mật khẩu:</label>
                        <input type="password" name="password" required value="">
                    </li>
                    <li>
                        <label>Họ và tên:</label>
                        <input type="text" name="fullname" required value="<?php echo htmlspecialchars($hovaten ?? ''); ?>">
                    </li>
                    <li>
                        <label>Số điện thoại:</label>
                        <input type="number" name="sodienthoai" required value="<?php echo htmlspecialchars($sodienthoai ?? ''); ?>">
                    </li>
                    <li>
                        <label>Giới tính:</label>
                        <div>
                            <span class="gender">Nam<input type="radio" name="gioitinh" value="Nam" required <?php if (($gioitinh ?? '') === 'Nam') echo 'checked'; ?>></span>
                            <span class="gender">Nữ<input type="radio" name="gioitinh" value="Nữ" required <?php if (($gioitinh ?? '') === 'Nữ') echo 'checked'; ?>></span>
                            <span class="gender">Khác<input type="radio" name="gioitinh" value="Khác" required <?php if (($gioitinh ?? '') === 'Khác') echo 'checked'; ?>></span>
                        </div>
                    </li>
                    <li>
                        <label>Ngày sinh:</label>
                        <input type="date" name="ngaysinh" required value="<?php echo htmlspecialchars($ngaysinh ?? ''); ?>">
                    </li>
                    <li>
                        <label>Địa chỉ:</label>
                        <input type="text" name="diachi" required value="<?php echo htmlspecialchars($diachi ?? ''); ?>">
                    </li>
                </ul>
                <button type="submit" name="save" class="save-btn">Lưu</button>
            </form>
        </div>

        <div class="donhang">
            <h2>Đơn hàng của bạn</h2>
            <ul>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <li><?php echo htmlspecialchars($order['product']) . ' - ' . htmlspecialchars($order['price']); ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Không có đơn hàng nào.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <script>
        function toggleInfo() {
            const infoSection = document.querySelector('.thongtin');
            infoSection.classList.toggle('active');
        }

        function toggleOrders() {
            const orderSection = document.querySelector('.donhang');
            orderSection.classList.toggle('active');
        }

        // Populate form fields with session values
        window.onload = function() {
            const email = "<?php echo htmlspecialchars($email); ?>";
            const hovaten = "<?php echo htmlspecialchars($hovaten); ?>";

            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="fullname"]').value = hovaten;
        };
    </script>
</body>
</html>