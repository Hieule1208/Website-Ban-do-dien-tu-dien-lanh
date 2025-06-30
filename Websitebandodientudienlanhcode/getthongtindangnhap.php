<?php
session_start();

// Kiểm tra nếu form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Lấy dữ liệu từ form
        $email = $_POST['email'];
        $password = $_POST['password'];

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

        // Check for admin login
        if ($email === 'admin123@gmail.com' && $password === 'admin123') {
            $_SESSION['admin'] = true;
            header("Location: admin.php");
            exit();
        }

        // Truy vấn cơ sở dữ liệu
        $sql = "SELECT * FROM dangky WHERE taikhoan = ?"; // Sử dụng prepared statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email); // "s" là kiểu string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Xác minh mật khẩu đã băm
            if (password_verify($password, $row['matkhau'])) {
                // Đăng nhập thành công, lưu email vào session
                $_SESSION['email'] = $email; // Set the session variable
                header("Location: testtrangchu.php");
                exit();
            } else {
                echo "Mật khẩu không đúng.";
            }
        } else {
            echo "Tài khoản không tồn tại.";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Vui lòng nhập email và mật khẩu.";
    }
} else {
    echo "Phương thức không hợp lệ.";
}
?>