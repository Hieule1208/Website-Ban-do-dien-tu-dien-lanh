<?php
session_start();

// Kiểm tra nếu form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Lấy dữ liệu từ form
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Hash mật khẩu trước khi lưu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Tạo kết nối đến cơ sở dữ liệu
        $servername = "localhost";
        $username = "root";
        $db_password = "123456";
        $dbname = "quanlydodientu";

        // Kết nối đến MySQL
        $conn = new mysqli($servername, $username, $db_password, $dbname);

        // Kiểm tra kết nối
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Sử dụng prepared statements để tránh SQL injection
        $stmt = $conn->prepare("INSERT INTO dangky (taikhoan, matkhau) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashed_password); // Dùng hashed password

        if ($stmt->execute()) {
            // Nếu thêm thành công, chuyển hướng tới trang đăng nhập
            header("Location: trangdangnhap.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Đóng prepared statement và kết nối
        $stmt->close();
        $conn->close();
    } else {
        echo "Vui lòng nhập email và mật khẩu.";
    }
} else {
    echo "Phương thức không hợp lệ.";
}
?>

