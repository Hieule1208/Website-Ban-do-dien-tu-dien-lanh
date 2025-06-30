<?php
// Database connection
$conn = new mysqli("localhost", "root", "123456", "quanlydodientu");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM sanpham WHERE mahang = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $productImage = "testhinhanh.php?mahang=" . urlencode($product['mahang']);
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .header {
            background: linear-gradient(90deg, #007BFF, #00C4B4);
            color: white;
            padding: 25px;
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .main-header:hover {
            background: rgba(255, 255, 255, 1);
        }
        .category img, .basket img, .logo img {
            width: 40px;
            transition: transform 0.3s ease;
        }
        .category img:hover, .basket img:hover, .logo img:hover {
            transform: scale(1.2);
        }
        .search-bar {
            position: relative;
        }
        .search-bar input {
            width: 400px;
            padding: 12px;
            border-radius: 25px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .search-bar input:focus {
            width: 420px;
            outline: none;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        .basket {
            position: relative;
        }
        .basket-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 250px;
            padding: 15px;
            z-index: 1;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        .basket:hover .basket-content {
            display: block;
            transform: translateY(0);
        }
        .basket-content ul li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 5px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .basket-content ul li:hover {
            background: #f1f3f5;
        }
        .basket-total {
            font-weight: 600;
            color: #007BFF;
        }
        .product-details {
            display: flex;
            flex-wrap: wrap;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 50px auto;
            max-width: 900px;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .product-image img {
            max-width: 90%; /* Reduce the width slightly */
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .product-image img:hover {
            transform: scale(1.05);
        }
        .product-info h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 15px;
        }
        .product-info .price {
            color: #e74c3c;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .product-info button {
            background: linear-gradient(90deg, #28a745, #2ecc71);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .product-info button:hover {
            background: linear-gradient(90deg, #218838, #27ae60);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .add-to-cart {
            background: linear-gradient(90deg, #007BFF, #00C4B4);
        }
        .add-to-cart:hover {
            background: linear-gradient(90deg, #0056b3, #0097a7);
        }
        .product-description {
            margin: 30px auto;
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        #cartModal {
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translate(-50%, -60%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }
        #searchResults {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        #searchResults li:hover {
            background: #e9ecef;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="main-header">
        <div class="category">
            <a href="testtrangchu.php"><img src="img/home.png" alt="Home"></a>
        </div>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." onkeyup="searchProducts()">
            <ul id="searchResults"></ul>
        </div>
        <div class="basket">
            <img src="img/anhgiohang.png" alt="Giỏ hàng">
            <div class="basket-content" id="basketContent">
                <h4>Nội dung giỏ hàng</h4>
                <ul id="basketItems"></ul>
                <div class="basket-total">
                    <strong>Tổng cộng: </strong><span id="basketTotal">0 VND</span>
                </div>
                <button onclick="showCartModal()" style="background: #007BFF; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; margin-top: 10px;">Xem giỏ hàng</button>
            </div>
        </div>
        <div class="logo">
            <a href="chinhsuathongtincanhan.php"><img src="img/thongtinkh.png" alt="User Logo"></a>
        </div>
    </div>
    <div class="header">Chi Tiết Sản Phẩm</div>
    <div class="product-details">
        <div class="product-image">
            <img src="<?php echo htmlspecialchars($productImage); ?>" alt="Product Image">
        </div>
        <div class="product-info">
            <h2><?php echo htmlspecialchars($product['tenhang']); ?></h2>
            <p class="price"><?php echo htmlspecialchars($product['gia']); ?> VND</p>
            <p><strong>Số lượng còn lại:</strong> <?php echo htmlspecialchars($product['soluong']); ?></p>
            <form method="post" action="process_order.php">
                <label for="quantity">Số lượng:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['soluong']); ?>" required>
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product['gia']); ?>">
                <button type="button" class="add-to-cart" 
                    onclick="addToCart('<?php echo htmlspecialchars($product['tenhang']); ?>', 
                                       <?php echo htmlspecialchars($product['gia']); ?>, 
                                       '<?php echo htmlspecialchars($productImage); ?>', 
                                       <?php echo htmlspecialchars($product['soluong']); ?>)">
                    Thêm vào giỏ hàng
                </button>
            </form>
        </div>
    </div>
    <div class="product-description">
        <h3>Mô tả sản phẩm</h3>
        <p><?php echo htmlspecialchars($product['mota']); ?></p>
    </div>
    <div id="cartModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; z-index: 1000;">
        <h3>Giỏ hàng của bạn</h3>
        <ul id="cartModalItems"></ul>
        <div class="basket-total">
            <strong>Tổng cộng: </strong><span id="cartModalTotal">0 VND</span>
        </div>
        <form action="chinhsuathongtincanhan.php" method="post">
            <input type="hidden" name="basketData" id="cartModalData">
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; margin-top: 10px;">Mua hàng</button>
        </form>
        <button onclick="closeCartModal()" style="background: #dc3545; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; margin-top: 10px;">Đóng</button>
    </div>
    <script>
        const products = [
            { id: 1, name: "Điện thoại Samsung Galaxy S21", image: "img/samsunggalaxys21.jpg", price: 20000000 },
            { id: 2, name: "iPhone 13", image: "img/iphone13.jpeg", price: 25000000 },
            { id: 3, name: "Laptop Dell XPS 13", image: "DELLXPS13.webp", price: 30000000 },
            { id: 4, name: "Laptop HP Spectre x360", image: "HpSpectreX360.webp", price: 35000000 },
            { id: 5, name: "iPad Pro 2021", image: "ipadpro.jpeg", price: 28000000 },
            { id: 6, name: "Samsung Galaxy Tab S7", image: "samsungtab.jpeg", price: 18000000 },
            { id: 7, name: "Lenovo Tab P11 Pro", image: "lenovotab.jpeg", price: 15000000 }
        ];

        let basket = JSON.parse(localStorage.getItem('basket')) || [];

        function addToCart(productName, productPrice, productImage, availableQuantity) {
            const existingProductIndex = basket.findIndex(item => item.name === productName);
            if (existingProductIndex !== -1) {
                if (basket[existingProductIndex].quantity + 1 > availableQuantity) {
                    alert(`Không thể thêm hơn ${availableQuantity} sản phẩm vào giỏ hàng.`);
                    return;
                }
                basket[existingProductIndex].quantity += 1;
            } else {
                basket.push({ name: productName, price: productPrice, image: productImage, quantity: 1, availableQuantity });
            }
            localStorage.setItem('basket', JSON.stringify(basket));
            alert(`${productName} đã được thêm vào giỏ hàng!`);
            updateBasketDisplay();
            animateBasket();
        }

        function updateQuantity(index, newQuantity) {
            const availableQuantity = basket[index].availableQuantity;
            if (newQuantity < 1) return;
            if (newQuantity > availableQuantity) {
                alert(`Không thể chọn hơn ${availableQuantity} sản phẩm.`);
                return;
            }
            basket[index].quantity = parseInt(newQuantity, 10);
            localStorage.setItem('basket', JSON.stringify(basket));
            updateBasketDisplay();
        }

        function updateBasketDisplay() {
            const basketItems = document.getElementById('basketItems');
            const basketTotal = document.getElementById('basketTotal');
            const basketDataInput = document.getElementById('cartModalData');
            basketItems.innerHTML = '';
            let total = 0;

            basket.forEach((item, index) => {
                const li = document.createElement('li');
                li.classList.add('basket-item');
                const img = document.createElement('img');
                img.src = item.image;
                img.style.width = '40px';
                img.style.marginRight = '10px';

                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.value = item.quantity;
                quantityInput.min = 1;
                quantityInput.max = item.availableQuantity;
                quantityInput.style.width = '50px';
                quantityInput.addEventListener('change', () => updateQuantity(index, quantityInput.value));

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Xóa';
                deleteButton.style.marginLeft = '10px';
                deleteButton.style.backgroundColor = '#dc3545';
                deleteButton.style.color = 'white';
                deleteButton.style.border = 'none';
                deleteButton.style.padding = '5px 10px';
                deleteButton.style.borderRadius = '5px';
                deleteButton.style.cursor = 'pointer';
                deleteButton.addEventListener('click', () => deleteProduct(index));

                li.appendChild(img);
                li.appendChild(document.createTextNode(`${item.name} - ${item.price.toLocaleString()} VND`));
                li.appendChild(quantityInput);
                li.appendChild(deleteButton);
                basketItems.appendChild(li);

                total += item.price * item.quantity;
            });

            basketTotal.textContent = `${total.toLocaleString()} VND`;
            basketDataInput.value = JSON.stringify(basket);
        }

        function deleteProduct(index) {
            basket.splice(index, 1);
            localStorage.setItem('basket', JSON.stringify(basket));
            updateBasketDisplay();
        }

        function showCartModal() {
            const cartModal = document.getElementById('cartModal');
            const cartModalItems = document.getElementById('cartModalItems');
            const cartModalTotal = document.getElementById('cartModalTotal');
            const cartModalData = document.getElementById('cartModalData');

            cartModalItems.innerHTML = '';
            let total = 0;

            basket.forEach(item => {
                const li = document.createElement('li');
                li.classList.add('basket-item');
                const img = document.createElement('img');
                img.src = item.image;
                img.style.width = '40px';
                img.style.marginRight = '10px';
                li.appendChild(img);
                li.appendChild(document.createTextNode(`${item.name} - ${item.quantity} x ${item.price.toLocaleString()} VND`));
                cartModalItems.appendChild(li);
                total += item.price * item.quantity;
            });

            cartModalTotal.textContent = `${total.toLocaleString()} VND`;
            cartModalData.value = JSON.stringify(basket);
            cartModal.style.display = 'block';
        }

        function closeCartModal() {
            const cartModal = document.getElementById('cartModal');
            cartModal.style.display = 'none';
        }

        function animateBasket() {
            const basketImg = document.querySelector('.basket img');
            basketImg.style.transition = 'transform 0.3s ease';
            basketImg.style.transform = 'scale(1.3)';
            setTimeout(() => {
                basketImg.style.transform = 'scale(1)';
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', updateBasketDisplay);
    </script>
</body>
</html>
    <?php
} else {
    echo "<p>Product not found.</p>";
}

$conn->close();
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    if ($quantity > 0) {
        $stmt = $conn->prepare("SELECT * FROM sanpham WHERE mahang = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($product) {
            $cartItem = [
                'id' => $product['mahang'],
                'name' => $product['tenhang'],
                'price' => $product['gia'],
                'quantity' => $quantity,
                'image' => "testhinhanh.php?mahang=" . urlencode($product['mahang']) // Retrieve image dynamically
            ];

            $exists = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] === $productId) {
                    $item['quantity'] += $quantity;
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $_SESSION['cart'][] = $cartItem;
            }
            echo "<script>alert('Product added to cart successfully!');</script>";
        } else {
            echo "<script>alert('Product not available!');</script>";
        }
    }
}
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_cart'])) {
    if (!empty($_SESSION['cart'])) {
        $totalCost = 0;

        foreach ($_SESSION['cart'] as $cartItem) {
            $totalCost += $cartItem['price'] * $cartItem['quantity'];

            // Update product quantity in the database
            $stmt = $conn->prepare("UPDATE sanpham SET soluong = soluong - ? WHERE mahang = ?");
            $stmt->bind_param("ii", $cartItem['quantity'], $cartItem['id']);
            $stmt->execute();
            $stmt->close();
        }

        // Clear the cart after purchase
        $_SESSION['cart'] = [];
        echo "<script>alert('Mua hàng thành công! Tổng tiền: " . number_format($totalCost, 0, ',', '.') . " VND');</script>";
    } else {
        echo "<script>alert('Giỏ hàng của bạn đang trống!');</script>";
    }
}
?>
<div class="basket-content" id="basketContent">
    <h4>Giỏ hàng của bạn</h4>
    <ul id="basketItems">
        <?php 
        $totalCost = 0;
        foreach ($_SESSION['cart'] as $cartItem): 
            $totalCost += $cartItem['price'] * $cartItem['quantity'];
        ?>
            <li data-product-id="<?php echo $cartItem['id']; ?>">
                <img src="<?php echo $cartItem['image']; ?>" alt="<?php echo htmlspecialchars($cartItem['name']); ?>">
                <span><?php echo htmlspecialchars($cartItem['name']); ?> - <?php echo number_format($cartItem['price'], 0, ',', '.'); ?> VND</span>
                <input type="number" value="<?php echo $cartItem['quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $cartItem['id']; ?>, this.value)">
                <button onclick="deleteProduct(<?php echo $cartItem['id']; ?>, this)">Xóa</button>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="basket-total">
        <strong>Tổng tiền: </strong><span id="basketTotal"><?php echo number_format($totalCost, 0, ',', '.'); ?> VND</span>
    </div>
    <form method="post"></form></form>