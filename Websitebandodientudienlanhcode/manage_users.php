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

// Handle delete user
if (isset($_GET['delete'])) {
    $idnguoidung = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM dangky WHERE idnguoidung = ?");
    $stmt->bind_param("i", $idnguoidung);
    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idnguoidung = isset($_POST['idnguoidung']) ? intval($_POST['idnguoidung']) : 0;
    $taikhoan = $_POST['taikhoan'];
    $matkhau = password_hash($_POST['matkhau'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $sodienthoai = $_POST['sodienthoai'];
    $ngaysinh = $_POST['ngaysinh'];
    $diachi = $_POST['diachi'];
    $gioitinh = $_POST['gioitinh'];
    $hovaten = $_POST['hovaten'];

    if ($idnguoidung > 0) {
        // Update user
        $stmt = $conn->prepare("UPDATE dangky SET taikhoan = ?, matkhau = ?, email = ?, sodienthoai = ?, ngaysinh = ?, diachi = ?, gioitinh = ?, hovaten = ? WHERE idnguoidung = ?");
        $stmt->bind_param("ssssssssi", $taikhoan, $matkhau, $email, $sodienthoai, $ngaysinh, $diachi, $gioitinh, $hovaten, $idnguoidung);
        if ($stmt->execute()) {
            echo "<script>alert('User updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating user: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM dangky WHERE taikhoan = ?");
        $stmt->bind_param("s", $taikhoan);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo "<script>alert('Username already exists! Please choose a different username.');</script>";
        } else {
            // Add new user
            $stmt = $conn->prepare("INSERT INTO dangky (taikhoan, matkhau, email, sodienthoai, ngaysinh, diachi, gioitinh, hovaten) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $taikhoan, $matkhau, $email, $sodienthoai, $ngaysinh, $diachi, $gioitinh, $hovaten);
            if ($stmt->execute()) {
                echo "<script>alert('User added successfully!');</script>";
            } else {
                echo "<script>alert('Error adding user: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    }
}

// Fetch user data for editing
$editUser = null;
if (isset($_GET['edit'])) {
    $idnguoidung = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM dangky WHERE idnguoidung = ?");
    $stmt->bind_param("i", $idnguoidung);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch all users
$result = $conn->query("SELECT * FROM dangky");
$users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h1>Quản lý khách hànghàng</h1>

    <div class="form-container">
        <h2><?php echo isset($editUser) ? 'Edit User' : 'Add User'; ?></h2>
        <form method="post">
            <input type="hidden" name="idnguoidung" id="idnguoidung" value="<?php echo $editUser['idnguoidung'] ?? ''; ?>">
            <label for="taikhoan">Tài khoản:</label>
            <input type="text" name="taikhoan" id="taikhoan" value="<?php echo htmlspecialchars($editUser['taikhoan'] ?? ''); ?>" required>
            <label for="matkhau">Mật khẩu:</label>
            <input type="password" name="matkhau" id="matkhau" <?php echo isset($editUser) ? '' : 'required'; ?>>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>" required>
            <label for="sodienthoai">Số điện thoại:</label>
            <input type="text" name="sodienthoai" id="sodienthoai" value="<?php echo htmlspecialchars($editUser['sodienthoai'] ?? ''); ?>" required>
            <label for="ngaysinh">Ngày sinh:</label>
            <input type="date" name="ngaysinh" id="ngaysinh" value="<?php echo htmlspecialchars($editUser['ngaysinh'] ?? ''); ?>" required>
            <label for="diachi">Địa chỉ:</label>
            <input type="text" name="diachi" id="diachi" value="<?php echo htmlspecialchars($editUser['diachi'] ?? ''); ?>" required>
            <label for="gioitinh">Giới tính:</label>
            <select name="gioitinh" id="gioitinh" required>
                <option value="Nam" <?php echo (isset($editUser) && $editUser['gioitinh'] === 'Nam') ? 'selected' : ''; ?>>Nam</option>
                <option value="Nữ" <?php echo (isset($editUser) && $editUser['gioitinh'] === 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                <option value="Khác" <?php echo (isset($editUser) && $editUser['gioitinh'] === 'Khác') ? 'selected' : ''; ?>>Khác</option>
            </select>
            <label for="hovaten">Họ và tên:</label>
            <input type="text" name="hovaten" id="hovaten" value="<?php echo htmlspecialchars($editUser['hovaten'] ?? ''); ?>" required>
            <button type="submit"><?php echo isset($editUser) ? 'Update' : 'Save'; ?></button>
        </form>
          <p>
        
        <a href="admin.php">Quản trị Admin</a>
    </p>
    </div>

    <h2>Danh sách thông tin khách hàng  </h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tài khoản</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Ngày sinh</th>
                <th>Địa chỉ</th>
                <th>Giới tính</th>
                <th>Họ và tên</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['idnguoidung']; ?></td>
                    <td><?php echo htmlspecialchars($user['taikhoan']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['sodienthoai']); ?></td>
                    <td><?php echo htmlspecialchars($user['ngaysinh']); ?></td>
                    <td><?php echo htmlspecialchars($user['diachi']); ?></td>
                    <td><?php echo htmlspecialchars($user['gioitinh']); ?></td>
                    <td><?php echo htmlspecialchars($user['hovaten']); ?></td>
                    <td>
                        <button class="delete-button" onclick="window.location.href='?delete=<?php echo $user['idnguoidung']; ?>'">Delete</button>
                        <button onclick="window.location.href='?edit=<?php echo $user['idnguoidung']; ?>'">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>