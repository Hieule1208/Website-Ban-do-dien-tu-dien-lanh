<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "quanlydodientu";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Greet the user by name if logged in
$isLoggedIn = isset($_SESSION['hovaten']);
$userName = $isLoggedIn ? $_SESSION['hovaten'] : '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Function to add product to cart
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
                'image' => "testhinhanh.php?mahang=" . urlencode($product['mahang'])
            ];

            // Check if product already exists in cart
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

// Function to handle "Buy" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_cart'])) {
    foreach ($_SESSION['cart'] as $cartItem) {
        $stmt = $conn->prepare("UPDATE sanpham SET soluong = soluong - ? WHERE mahang = ?");
        $stmt->bind_param("ii", $cartItem['quantity'], $cartItem['id']);
        $stmt->execute();
        $stmt->close();
    }
    $_SESSION['cart'] = []; // Clear the cart after purchase
    echo "<script>alert('Purchase successful!');</script>";
}

// Function to delete a cart item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_from_cart'])) {
    $productId = $_POST['product_id'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($productId) {
        return $item['id'] !== $productId;
    });
    echo "<script>alert('Product removed from cart!');</script>";
}

// New AJAX-based delete handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_from_cart') {
    $productId = intval($_POST['product_id']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $productId) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false]);
    exit;
}

// Search products handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search_products') {
    $searchQuery = trim($_POST['query']);
    $response = [];

    if (!empty($searchQuery)) {
        $stmt = $conn->prepare("SELECT mahang, tenhang, gia, soluong FROM sanpham WHERE tenhang LIKE ?");
        $likeQuery = "%" . $searchQuery . "%";
        $stmt->bind_param("s", $likeQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'id' => $row['mahang'],
                'name' => $row['tenhang'],
                'price' => $row['gia'],
                'quantity' => $row['soluong'],
                'image' => "testhinhanh.php?mahang=" . urlencode($row['mahang'])
            ];
        }
        $stmt->close();
    }

    echo json_encode($response);
    exit;
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['email']);
$userEmail = $isLoggedIn ? $_SESSION['email'] : '';

// SQL to get all product data
$sql = "SELECT * FROM sanpham ORDER BY mahang DESC"; // Fetch all products, newest first
$result = $conn->query($sql);

// Check if products are found
if ($result->num_rows > 0) {
    // Fetch all product data
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row; // Store each product in an array
    }
} else {
    echo "No products found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cửa hàng điện tử</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.8), rgba(52, 152, 219, 0.8)), url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            color: #2c3e50;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 10px 20px;
        }

        .top-bar {
            text-align: left;
            padding: 10px;
            font-size: 14px;
        }

        .top-bar a {
            color: #2ecc71;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .top-bar a:hover {
            color: #27ae60;
        }

        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
        }

        .category img, .basket img, .logo img {
            width: 40px;
            transition: transform 0.3s;
        }

        .category img:hover, .basket img:hover, .logo img:hover {
            transform: scale(1.1);
        }

        .search-bar {
            position: relative;
        }

        .search-bar input {
            width: 350px;
            padding: 12px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 25px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #2ecc71;
            background: #fff;
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.3);
        }

        .basket {
            position: relative;
        }

        .basket-content {
            display: none;
            position: absolute;
            right: 0;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 250px;
            max-height: 300px;
            overflow-y: auto;
            padding: 15px;
            z-index: 10;
        }

        .basket:hover .basket-content {
            display: block;
            animation: slideDown 0.3s ease-in-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .basket-content ul li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .basket-content ul li img {
            border-radius: 5px;
        }

        .basket-total {
            font-weight: 600;
            color: #2ecc71;
        }

        .basket-content ul li input[type="number"] {
            width: 50px; /* Make the quantity box smaller */
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .slideshow-container {
            position: relative;
            max-width: 1200px;
            margin: 20px auto;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .slider img {
            display: none;
            width: 100%;
            transition: opacity 0.5s ease-in-out;
        }

        .slider img.active {
            display: block;
            opacity: 1;
        }

        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 15px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .prev:hover, .next:hover {
            background: #2ecc71;
        }

        .prev { left: 20px; }
        .next { right: 20px; }

        .category-section {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.85);
            margin: 20px auto;
            max-width: 1200px;
            border-radius: 10px;
        }

        .category-section h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .category-section a {
            margin: 0 15px;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .category-section a:hover {
            color: #2980b9;
        }

        h2 {
            text-align: center;
            margin: 30px 0;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        h2::after {
            content: '';
            width: 50px;
            height: 3px;
            background: #2ecc71;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .product-container-wrapper {
            position: relative;
            max-width: 1200px;
            margin: 0 auto 40px;
            overflow: hidden;
        }

        .product-container {
            display: flex;
            transition: transform 0.5s ease-in-out;
            padding: 10px;
        }

        .product-card {
            width: 220px;
            flex-shrink: 0;
            margin: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid #2ecc71;
        }

        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 1px solid #ecf0f1;
        }

        .product-card .product-name {
            font-size: 16px;
            font-weight: 600;
            margin: 10px 0;
            color: #34495e;
        }

        .product-card .product-price {
            font-size: 18px;
            color: #e74c3c;
            margin-bottom: 15px;
        }

        .product-card button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: #fff;
            border: none;
            border-radius: 0 0 15px 15px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .product-card button:hover {
            background: linear-gradient(to right, #27ae60, #2ecc71);
        }

        #searchResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-height: 400px;
            overflow-y: auto;
            margin-top: 5px;
            z-index: 1000;
            padding: 8px 0;
        }

        #searchResults li {
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #searchResults li:last-child {
            border-bottom: none;
        }

        #searchResults li:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        #searchResults .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        #searchResults img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        #searchResults .product-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        #searchResults .product-name {
            font-weight: 600;
            color: #2c3e50;
        }

        #searchResults .product-price {
            color: #e74c3c;
            font-weight: 500;
        }

        /* ...existing styles... */
    </style>
</head>
<body>
    <div class="header">
        <div class="top-bar">
            <?php if ($isLoggedIn): ?>
                <span>Xin chào, <?php echo explode('@', $userEmail)[0]; ?></span> | <a href="trangdangnhap.php">Đăng xuất</a>
            <?php else: ?>
                <a href="trangdangnhap.php">Đăng nhập</a> | <a href="trangdangky2.php">Đăng ký</a>
            <?php endif; ?>
        </div>
        <div class="main-header">
            <div class="category">
                <a href="testtrangchu.php">
                    <img src="img/home.png" alt="Home">
                </a>
            </div>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm...">
                <ul id="searchResults"></ul>
            </div>
            <div class="basket">
                <img src="img/anhgiohang.png" alt="Giỏ hàng">
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
                                <span><?php echo htmlspecialchars($cartItem['name']); ?> - <?php echo number_format($cartItem['price'], 0, ',', '.'); ?> VND x <?php echo $cartItem['quantity']; ?></span>
                                <button onclick="deleteProduct(<?php echo $cartItem['id']; ?>, this)">Xóa</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="basket-total">
                        <strong>Tổng tiền: </strong><span id="basketTotal"><?php echo number_format($totalCost, 0, ',', '.'); ?> VND</span>
                    </div>
                    <form method="post">
                        <button type="submit" name="buy_cart" style="background: #2ecc71; color: #fff; border: none; padding: 12px 25px; border-radius: 25px; cursor: pointer; margin-top: 15px; transition: all 0.3s ease;">Mua hàng</button>
                    </form>
                </div>
            </div>
            <div class="logo">
                <a href="chinhsuathongtincanhan.php">
                    <img src="img/thongtinkh.png" alt="User Logo">
                </a>
            </div>
        </div>
    </div>

    <div class="nar">
        <div class="slideshow-container">
            <div class="slider">
                <img src="img/quangcao1.png" alt="Slider 1" class="active">
                <img src="img/quangcao2.png" alt="Slider 2">
                <img src="img/quangcao3.png" alt="Slider 3">
            </div>
            <span class="prev">❮</span>
            <span class="next">❯</span>
        </div>
    </div>

    <div class="category-section">
        <h3>Danh mục sản phẩm</h3>
        <a href="#dienthoai">Điện thoại</a>
        <a href="#laptop">Laptop</a>
        <a href="#tablet">Máy tính bảng</a>
        <a href="#dienthoai">Tủ lạnh </a>
        <a href="#laptop">Điều hòa </a>
        <a href="#tablet">Phụ kiện</a>
    </div>

    <h2 id="dienthoai">Sản phẩm Điện thoại</h2>
    <div class="product-container-wrapper">
        <div class="product-container">
             <?php foreach ($products as $product): ?>
                <?php if (isset($product['madanhmuc']) && $product['madanhmuc'] == 1): ?>
                <div class="product-card">
                    <img src="testhinhanh.php?mahang=<?php echo urlencode($product['mahang']); ?>" 
                         alt="<?php echo htmlspecialchars($product['tenhang']); ?>" 
                         style="width: 100%; height: 150px; object-fit: cover;">
                    <div class="product-name"><?php echo htmlspecialchars($product['tenhang']); ?></div>
                    <div class="product-price"><?php echo number_format($product['gia'], 0, ',', '.'); ?> VND</div>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['mahang']; ?>">
                        <input type="number" name="quantity" min="1" max="<?php echo $product['soluong']; ?>" value="1" required>
                        
                    </form>
                    <button onclick="window.location.href='chitietsanpham.php?id=<?php echo $product['mahang']; ?>'">Xem chi tiết</button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
         
        <button class="prev">‹</button>
        <button class="next">›</button>
    </div>

    <h2 id="laptop">Sản phẩm Laptop</h2>
    <div class="product-container-wrapper">
        <div class="product-container">
            <?php foreach ($products as $product): ?>
                <?php if (isset($product['madanhmuc']) && $product['madanhmuc'] == 2): ?>
                <div class="product-card">
                    <img src="testhinhanh.php?mahang=<?php echo urlencode($product['mahang']); ?>" 
                         alt="<?php echo htmlspecialchars($product['tenhang']); ?>" 
                         style="width: 100%; height: 150px; object-fit: cover;">
                    <div class="product-name"><?php echo htmlspecialchars($product['tenhang']); ?></div>
                    <div class="product-price"><?php echo number_format($product['gia'], 0, ',', '.'); ?> VND</div>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['mahang']; ?>">
                        <input type="number" name="quantity" min="1" max="<?php echo $product['soluong']; ?>" value="1" required>
                        
                    </form>
                    <button onclick="window.location.href='chitietsanpham.php?id=<?php echo $product['mahang']; ?>'">Xem chi tiết</button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <button class="prev">‹</button>
        <button class="next">›</button>
    </div>

    <h2 id="tablet">Sản phẩm Máy tính bảng</h2>
    <div class="product-container-wrapper">
        <div class="product-container">
             <?php foreach ($products as $product): ?>
                <?php if (isset($product['madanhmuc']) && $product['madanhmuc'] == 33): ?>
                <div class="product-card">
                    <img src="testhinhanh.php?mahang=<?php echo urlencode($product['mahang']); ?>" 
                         alt="<?php echo htmlspecialchars($product['tenhang']); ?>" 
                         style="width: 100%; height: 150px; object-fit: cover;">
                    <div class="product-name"><?php echo htmlspecialchars($product['tenhang']); ?></div>
                    <div class="product-price"><?php echo number_format($product['gia'], 0, ',', '.'); ?> VND</div>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['mahang']; ?>">
                        <input type="number" name="quantity" min="1" max="<?php echo $product['soluong']; ?>" value="1" required>
                        
                    </form>
                    <button onclick="window.location.href='chitietsanpham.php?id=<?php echo $product['mahang']; ?>'">Xem chi tiết</button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <button class="prev">‹</button>
        <button class="next">›</button>
    </div>

    <script>
        // Slideshow
        const sliderImages = document.querySelectorAll('.slider img');
        let currentImageIndex = 0;

        function showNextImage() {
            sliderImages[currentImageIndex].classList.remove('active');
            currentImageIndex = (currentImageIndex + 1) % sliderImages.length;
            sliderImages[currentImageIndex].classList.add('active');
        }

        function showPrevImage() {
            sliderImages[currentImageIndex].classList.remove('active');
            currentImageIndex = (currentImageIndex - 1 + sliderImages.length) % sliderImages.length;
            sliderImages[currentImageIndex].classList.add('active');
        }

        document.querySelector('.slideshow-container .next').addEventListener('click', showNextImage);
        document.querySelector('.slideshow-container .prev').addEventListener('click', showPrevImage);
        setInterval(showNextImage, 5000);

       // Product Slider
document.querySelectorAll('.product-container-wrapper').forEach(wrapper => {
    const container = wrapper.querySelector('.product-container');
    const cards = container.querySelectorAll('.product-card');

    // ADD THIS CHECK:
    if (cards.length === 0) {
        // If there are no product cards in this container, skip processing it.
        // Also hide the navigation buttons if they exist for this empty container.
        const prevButton = wrapper.querySelector('.prev');
        const nextButton = wrapper.querySelector('.next');
        if (prevButton) prevButton.style.display = 'none';
        if (nextButton) nextButton.style.display = 'none';
        return; // Exit this iteration of the forEach loop
    }

    const cardWidth = cards[0].offsetWidth + 30; // This line is now safe
    let currentIndex = 0;

    // Adjust visibility of navigation buttons (improved logic for less than a full row)
    const prevButton = wrapper.querySelector('.prev');
    const nextButton = wrapper.querySelector('.next');

    // You need to decide how many cards are visible at once to adjust this condition
    // For example, if 3 cards are always visible, show buttons only if cards.length > 3
    if (cards.length <= 3) { // Assuming 3 cards are visible without scrolling
        if (prevButton) prevButton.style.display = 'none';
        if (nextButton) nextButton.style.display = 'none';
    } else { // Ensure they are visible if there are enough cards to scroll
        if (prevButton) prevButton.style.display = 'block'; // Or original display style
        if (nextButton) nextButton.style.display = 'block'; // Or original display style
    }


    if (nextButton) {
        nextButton.addEventListener('click', () => {
            // Adjust this condition based on how many cards are visible and total width
            // Example: If 4 cards fit, you can scroll until the last one is visible.
            // A more robust calculation would involve wrapper.clientWidth and container.scrollWidth
            const visibleCards = Math.floor(wrapper.offsetWidth / cardWidth);
            if (currentIndex < cards.length - visibleCards) {
                currentIndex++;
                container.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
            }
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                container.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
            }
        });
    }
});

        // Basket Functionality
        let basket = JSON.parse(localStorage.getItem('basket')) || [];

        function addToBasket(productName, productPrice, productImage, availableQuantity) {
            const existingProductIndex = basket.findIndex(item => item.name === productName);
            if (existingProductIndex !== -1) {
                if (basket[existingProductIndex].quantity < availableQuantity) {
                    basket[existingProductIndex].quantity += 1;
                } else {
                    alert(`Không thể thêm hơn ${availableQuantity} sản phẩm vào giỏ hàng.`);
                    return;
                }
            } else {
                basket.push({ name: productName, price: productPrice, image: productImage, quantity: 1, availableQuantity });
            }
            localStorage.setItem('basket', JSON.stringify(basket));
            alert(`${productName} đã được thêm vào giỏ hàng!`);
            updateBasketDisplay();
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
            basketItems.innerHTML = '';
            let total = 0;

            basket.forEach((item, index) => {
                const li = document.createElement('li');
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
                deleteButton.style.backgroundColor = '#e74c3c';
                deleteButton.style.color = '#fff';
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
        }

        // Sửa hàm deleteProduct để truyền đúng index khi xóa sản phẩm từ localStorage (cho các sản phẩm tĩnh)
        function deleteProduct(productId, button) {
            // Nếu button được truyền vào (xóa từ PHP session/cart)
            if (button) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_from_cart&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = button.closest('li');
                        item.remove();
                        // Không gọi updateBasketDisplay() vì PHP sẽ render lại giỏ hàng khi reload
                        alert('Product removed from cart!');
                    } else {
                        alert('Failed to remove product from cart.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the product.');
                });
            } else {
                // Xóa sản phẩm khỏi localStorage (cho các sản phẩm tĩnh)
                basket.splice(productId, 1);
                localStorage.setItem('basket', JSON.stringify(basket));
                updateBasketDisplay();
            }
        }

        // Search Functionality
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        // Replace the existing searchProducts function with this updated version
        function searchProducts(query) {
            if (query.trim().length < 1) {
                searchResults.style.display = 'none';
                return;
            }

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=search_products&query=${encodeURIComponent(query)}`
            })
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';

                if (data.length > 0) {
                    data.forEach(product => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <div class="product-info">
                                <img src="${product.image}" alt="${product.name}">
                                <div class="product-details">
                                    <span class="product-name">${product.name}</span>
                                    <span style="color: #e74c3c;">${Number(product.price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0, maximumFractionDigits: 0, currencyDisplay: 'code' })}</span>
                                </div>
                            </div>
                        `;

                        li.addEventListener('click', () => {
                            window.location.href = `chitietsanpham.php?id=${product.id}`;
                        });

                        searchResults.appendChild(li);
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = `
                        <li style="justify-content: center; color: #666; cursor: default;">
                            Không tìm thấy sản phẩm
                        </li>
                    `;
                    searchResults.style.display = 'block';
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Add debounce to search
        let searchTimeout = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchProducts(searchInput.value);
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Initial basket display
        document.addEventListener('DOMContentLoaded', updateBasketDisplay);
    </script>
</body>
</html>