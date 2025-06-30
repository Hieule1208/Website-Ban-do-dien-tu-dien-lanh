<?php
session_start();
$servername = "localhost";
$username = "root";
$passwordDB = "123456";
$dbname = "quanlydodientu";
$conn = new mysqli($servername, $username, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO danhmuc (tendanhmuc) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_category.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM danhmuc WHERE madanhmuc = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_category.php");
    exit;
}

// Handle edit
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("UPDATE danhmuc SET tendanhmuc = ? WHERE madanhmuc = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_category.php");
    exit;
}

// Fetch categories
$categories = [];
$result = $conn->query("SELECT madanhmuc, tendanhmuc FROM danhmuc ORDER BY madanhmuc DESC");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 40px;}
        .container { max-width: 600px; margin: auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 30px;}
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left;}
        th { background: #f0f0f0;}
        form.inline { display: inline; }
        input[type="text"] { padding: 6px; border-radius: 4px; border: 1px solid #ccc;}
        button { padding: 6px 14px; border: none; border-radius: 4px; background: #3498db; color: #fff; cursor: pointer;}
        button.delete { background: #e74c3c;}
        button.edit { background: #f39c12;}
        .add-form { margin-top: 20px;}
        a { text-decoration: none; color: #3498db;}
    </style>
</head>
<body>
<div class="container">
    <h1>Quản lý danh mục</h1>
    <form class="add-form" method="post">
        <input type="text" name="name" placeholder="Tên danh mục mới" required>
        <button type="submit" name="add">Thêm</button>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php echo $cat['madanhmuc']; ?></td>
                <td>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $cat['madanhmuc']): ?>
                        <form class="inline" method="post">
                            <input type="hidden" name="id" value="<?php echo $cat['madanhmuc']; ?>">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($cat['tendanhmuc']); ?>" required>
                            <button type="submit" name="edit" class="edit">Lưu</button>
                        </form>
                    <?php else: ?>
                        <?php echo htmlspecialchars($cat['tendanhmuc']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $cat['madanhmuc']): ?>
                        <a href="manage_category.php">Hủy</a>
                    <?php else: ?>
                        <a href="manage_category.php?edit=<?php echo $cat['madanhmuc']; ?>"><button type="button" class="edit">Sửa</button></a>
                        <a href="manage_category.php?delete=<?php echo $cat['madanhmuc']; ?>" onclick="return confirm('Xóa danh mục này?');"><button type="button" class="delete">Xóa</button></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>
        
        <a href="admin.php">Quản trị Admin</a>
    </p>
</div>
</body>
</html>
