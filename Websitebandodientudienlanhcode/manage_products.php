<?php

error_reporting(E_ALL); // Bật hiển thị tất cả lỗi (chỉ dùng khi phát triển)
ini_set('display_errors', 1); // Hiển thị lỗi ra trình duyệt

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

// Handle delete product
if (isset($_GET['delete'])) {
    $productId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM sanpham WHERE mahang = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        echo "<script>alert('Product deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting product: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle add/edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['mahang']) ? intval($_POST['mahang']) : 0;
    $productName = $_POST['tenhang'];
    $productPrice = floatval($_POST['gia']);
    $productQuantity = intval($_POST['soluong']);
    $productDescription = $_POST['mota'];
    $productCategoryId = intval($_POST['madanhmuc']); // Lấy mã danh mục từ form

    // --- BẮT ĐẦU PHẦN XỬ LÝ HÌNH ẢNH (BLOB) ---
    $productImageBlob = null; // Khởi tạo biến BLOB rỗng
    $currentImageBlob = null; // Biến để lưu BLOB ảnh cũ nếu có

    // Lấy ảnh BLOB hiện tại nếu đang chỉnh sửa và không có ảnh mới được upload
    if ($productId > 0) {
        $stmt_fetch_image = $conn->prepare("SELECT hinhanh FROM sanpham WHERE mahang = ?");
        $stmt_fetch_image->bind_param("i", $productId);
        $stmt_fetch_image->execute();
        $stmt_fetch_image->bind_result($currentImageBlob); // Liên kết kết quả với biến
        $stmt_fetch_image->fetch(); // Lấy dữ liệu
        $stmt_fetch_image->close();
    }

    // Kiểm tra xem có file ảnh mới được upload hay không
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['hinhanh']['tmp_name'];
        $file_size = $_FILES['hinhanh']['size'];
        $file_type = $_FILES['hinhanh']['type']; // mime type của ảnh

        // Kiểm tra loại file và kích thước (tùy chỉnh nếu cần)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('Lỗi: Chỉ cho phép file ảnh JPG, JPEG, PNG, GIF, WebP.');</script>";
            $productImageBlob = false; // Đánh dấu lỗi
        } elseif ($file_size > $max_file_size) {
            echo "<script>alert('Lỗi: Kích thước file quá lớn (tối đa 5MB).');</script>";
            $productImageBlob = false; // Đánh dấu lỗi
        } else {
            // Đọc nội dung file ảnh vào biến BLOB
            $productImageBlob = file_get_contents($file_tmp_name);
            if ($productImageBlob === false) {
                 echo "<script>alert('Lỗi: Không thể đọc nội dung file ảnh.');</script>";
                 $productImageBlob = false;
            }
        }
    } else {
        // Nếu không có file mới được upload
        // Giữ lại ảnh BLOB hiện tại từ database
        $productImageBlob = $currentImageBlob;
    }

    if ($productImageBlob === false) {
        // Nếu có lỗi trong quá trình xử lý ảnh, dừng lại
        // Hoặc gán một giá trị BLOB mặc định nếu bạn muốn
        echo "<script>alert('Không thể lưu sản phẩm do lỗi ảnh.');</script>";
        // Có thể redirect hoặc exit() ở đây
    }
    // --- KẾT THÚC PHẦN XỬ LÝ HÌNH ẢNH (BLOB) ---


    if ($productId > 0) {
        // Update product
        $stmt = $conn->prepare("UPDATE sanpham SET tenhang = ?, gia = ?, soluong = ?, mota = ?, hinhanh = ?, madanhmuc = ? WHERE mahang = ?");
        if ($stmt === false) {
            echo "<script>alert('Lỗi chuẩn bị truy vấn UPDATE: " . $conn->error . "');</script>";
        } else {
            $stmt->bind_param("sdissii", 
                $productName,
                $productPrice,
                $productQuantity, 
                $productDescription,
                $productImageBlob,  // Changed from $null to actual BLOB data
                $productCategoryId,
                $productId
            );

            if ($stmt->execute()) {
                echo "<script>alert('Product updated successfully!');</script>";
            } else {
                echo "<script>alert('Error updating product: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    } else {
        // Add new product
        $stmt = $conn->prepare("INSERT INTO sanpham (tenhang, gia, soluong, mota, hinhanh, madanhmuc) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            echo "<script>alert('Lỗi chuẩn bị truy vấn INSERT: " . $conn->error . "');</script>";
        } else {
            $stmt->bind_param("sdissi", 
                $productName,
                $productPrice,
                $productQuantity,
                $productDescription,
                $productImageBlob,  // Changed from $null to actual BLOB data
                $productCategoryId
            );

            if ($stmt->execute()) {
                echo "<script>alert('Product added successfully!');</script>";
            } else {
                echo "<script>alert('Error adding product: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    }
}

// Fetch product data for editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $productId = intval($_GET['edit']);
    // Khi fetch để chỉnh sửa, chúng ta không cần lấy BLOB ra ngay
    // Chúng ta sẽ lấy BLOB khi thực sự cần hiển thị ảnh trong form chỉnh sửa
    $stmt = $conn->prepare("SELECT mahang, tenhang, gia, soluong, mota, madanhmuc FROM sanpham WHERE mahang = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch all products (không lấy BLOB trực tiếp ở đây để tránh quá tải bộ nhớ)
$result = $conn->query("SELECT mahang, tenhang, gia, soluong, mota, madanhmuc FROM sanpham"); // KHÔNG SELECT HINHANH ở đây
$products = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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
        .form-container input, .form-container textarea {
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
    <h1>Quản lý sản phẩm </h1>

    <div class="form-container">
        <h2><?php echo isset($editProduct) ? 'Edit Product' : 'Add Product'; ?></h2>
        <form method="post" enctype="multipart/form-data"> <input type="hidden" name="mahang" value="<?php echo $editProduct['mahang'] ?? ''; ?>">
            <label for="tenhang">Tên sản phẩm:</label>
            <input type="text" name="tenhang" id="tenhang" value="<?php echo htmlspecialchars($editProduct['tenhang'] ?? ''); ?>" required>
            <label for="gia">Giá:</label>
            <input type="number" name="gia" id="gia" value="<?php echo htmlspecialchars($editProduct['gia'] ?? ''); ?>" required>
            <label for="soluong">Số lượng:</label>
            <input type="number" name="soluong" id="soluong" value="<?php echo htmlspecialchars($editProduct['soluong'] ?? ''); ?>" required>
            <label for="mota">Mô tả:</label>
            <textarea name="mota" id="mota" required><?php echo htmlspecialchars($editProduct['mota'] ?? ''); ?></textarea>

            <label for="hinhanh">Hình ảnh:</label>
            <input type="file" name="hinhanh" id="hinhanh" accept="image/*"> 
            <?php if (isset($editProduct['mahang'])): // Hiển thị ảnh hiện tại nếu đang chỉnh sửa ?>
                <p>Ảnh hiện tại:</p>
                <img src="testhinhanh.php?mahang=<?php echo urlencode($editProduct['mahang']); ?>"
                     alt="Ảnh sản phẩm hiện tại"
                     style="max-width: 200px; height: auto; display: block; margin-top: 10px;">
            <?php endif; ?>

            <label for="madanhmuc">Mã danh mục:</label>
            <input type="number" name="madanhmuc" id="madanhmuc" value="<?php echo htmlspecialchars($editProduct['madanhmuc'] ?? ''); ?>" required>
            
            <button type="submit"><?php echo isset($editProduct) ? 'Update' : 'Save'; ?></button>
        </form>
          <p>
        
        <a href="admin.php">Quản trị Admin</a>
    </p>
    </div>

    <h2>Danh sách sản phẩm </h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Mô tả</th>
                <th>Hình ảnh</th>
                <th>Danh mục</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['mahang']; ?></td>
                    <td><?php echo htmlspecialchars($product['tenhang']); ?></td>
                    <td><?php echo number_format($product['gia'], 0, ',', '.'); ?> VND</td>
                    <td><?php echo $product['soluong']; ?></td>
                    <td><?php echo htmlspecialchars($product['mota']); ?></td>
                    <td>
                        <img src="testhinhanh.php?mahang=<?php echo urlencode($product['mahang']); ?>"
                             alt="<?php echo htmlspecialchars($product['tenhang']); ?>"
                             style="width: 50px; height: auto;">
                    </td>
                    <td><?php echo htmlspecialchars($product['madanhmuc'] ?? 'N/A'); ?></td>
                    <td>
                        <button class="delete-button" onclick="window.location.href='?delete=<?php echo $product['mahang']; ?>'">Delete</button>
                        <button onclick="window.location.href='?edit=<?php echo $product['mahang']; ?>'">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>