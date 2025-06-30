<?php
session_start(); // Start the session

$servername = "localhost"; // Database server
$username = "root"; // Database username
$password = "123456"; // Database password
$dbname = "quanlydodientu"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['email']);
$userEmail = $isLoggedIn ? $_SESSION['email'] : '';

// SQL to get all product data
$sql = "SELECT * FROM sanpham"; // Fetch all products
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
        /* Your existing CSS remains unchanged */
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

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 10px 20px;
        }

        .top-bar {
            text-align: right;
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

        /* Slideshow */
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

        /* Category Section */
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

        /* Product Cards */
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

        /* Search Results */
        #searchResults {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 350px;
            max-height: 250px;
            overflow-y: auto;
            padding: 10px;
            margin-top: 5px;
            list-style: none;
            display: none;
            z-index: 1000;
        }

        #searchResults li {
            padding: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        #searchResults li:hover {
            background: #ecf0f1;
        }
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
                <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." onkeyup="searchProducts()">
                <ul id="searchResults"></ul>
            </div>
            <div class="basket">
                <img src="img/anhgiohang.png" alt="Giỏ hàng">
                <div class="basket-content" id="basketContent">
                    <h4>Giỏ hàng của bạn</h4>
                    <ul id="basketItems"></ul>
                    <div class="basket-total">
                        <strong>Tổng cộng: </strong><span id="basketTotal">0 VND</span>
                    </div>
                    <button onclick="showCartModal()" style="background: #2ecc71; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer; margin-top: 10px;">Xem giỏ hàng</button>
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
                <img src="img/quangcao北方2.png" alt="Slider 2">
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
    </div>

    <h2 id="dienthoai">Sản phẩm Điện thoại</h2>
    <div class="product-container-wrapper">
        <div class="product-container">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Use testhinhanh.php to dynamically fetch the image -->
                    <img src="testhinhanh.php?mahang=<?php echo urlencode($product['mahang']); ?>" 
                         alt="<?php echo htmlspecialchars($product['tenhang']); ?>" 
                         style="width: 100%; height: 150px; object-fit: cover;">
                    <div class="product-name"><?php echo htmlspecialchars($product['tenhang']); ?></div>
                    <div class="product-price"><?php echo number_format($product['gia'], 0, ',', '.'); ?> VND</div>
                    <button onclick="window.location.href='chitietsanpham.php?id=<?php echo $product['mahang']; ?>'">Xem chi tiết</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="prev">‹</button>
        <button class="next">›</button>
    </div>

    <h2 id="laptop">Sản phẩm Laptop</h2>
    <div class="product-container-wrapper">
        <div class="product-container">
            <div class="product-card">
                <img src="DELLXPS13.webp" alt="Sản phẩm 3">
                <div class="product-name">Laptop Dell XPS 13</div>
                <div class="product-price">30.000.000 VND</div>
                <button onclick="addToBasket('Laptop Dell XPS 13', 30000000, 'DELLXPS13.webp', 10)">Thêm vào giỏ hàng</button>
            </div>
            <div class="product-card">
                <img src="HpSpectreX360.webp" alt="Sản phẩm 4">
                <div class="product-name">Laptop HP Spectre x360</div>
                <div class="product-price">35.000.000 VND</div>
                <button onclick="addToBasket('Laptop HP Spectre x360', 35000000, 'HpSpectreX360.webp', 10)">Thêm vào giỏ hàng</button>
            </div>
        </div>
        <button class="prev">‹</button>
        <button class="next">›</button>
    </div>

    <h2 id="tablet">Sản phẩm Máy tính bảng</h2>
    <div class="product-container-wrapper">
        <div class="product-container">
            <div class="product-card">
                <img src="ipadpro.jpeg" alt="Sản phẩm 5">
                <div class="product-name">iPad Pro 2021</div>
                <div class="product-price">28.000.000 VND</div>
                <button onclick="addToBasket('iPad Pro 2021', 28000000, 'ipadpro.jpeg', 10)">Thêm vào giỏ hàng</button>
            </div>
            <div class="product-card">
                <img src="samsungtab.jpeg" alt="Sản phẩm 6">
                <div class="product-name">Samsung Galaxy Tab S7</div>
                <div class="product-price">18.000.000 VND</div>
                <button onclick="addToBasket('Samsung Galaxy Tab S7', 18000000, 'samsungtab.jpeg', 10)">Thêm vào giỏ hàng</button>
            </div>
            <div class="product-card">
                <img src="lenovotab.jpeg" alt="Sản phẩm 7">
                <div class="product-name">Lenovo Tab P11 Pro</div>
                <div class="product-price">15.000.000 VND</div>
                <button onclick="addToBasket('Lenovo Tab P11 Pro', 15000000, 'lenovotab.jpeg', 10)">Thêm vào giỏ hàng</button>
            </div>
        </div>
        <button class="prev">‹</button>
        <button class="next">›</button>
    </div>

    <script>
        // Your existing JavaScript remains unchanged
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
        setInterval(showNextImage, 5000); // Auto-slide every 5 seconds

        // Product Slider
        document.querySelectorAll('.product-container-wrapper').forEach(wrapper => {
            const container = wrapper.querySelector('.product-container');
            const cards = container.querySelectorAll('.product-card');
            const cardWidth = cards[0].offsetWidth + 30; // Including margin
            let currentIndex = 0;

            wrapper.querySelector('.next').addEventListener('click', () => {
                if (currentIndex < cards.length - 3) {
                    currentIndex++;
                    container.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
                }
            });

            wrapper.querySelector('.prev').addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    container.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
                }
            });
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

        function deleteProduct(index) {
            basket.splice(index, 1);
            localStorage.setItem('basket', JSON.stringify(basket));
            updateBasketDisplay();
        }

        function showCartModal() {
            alert('Chức năng giỏ hàng đang được phát triển!');
        }

        function searchProducts() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const results = document.getElementById('searchResults');
            results.innerHTML = '';
            if (input.length < 2) {
                results.style.display = 'none';
                return;
            }

            const allProducts = [
                ...<?php echo json_encode($products); ?>,
                { tenhang: 'Laptop Dell XPS 13', gia: 30000000 },
                { tenhang: 'Laptop HP Spectre x360', gia: 35000000 },
                { tenhang: 'iPad Pro 2021', gia: 28000000 },
                { tenhang: 'Samsung Galaxy Tab S7', gia: 18000000 },
                { tenhang: 'Lenovo Tab P11 Pro', gia: 15000000 }
            ];

            const filtered = allProducts.filter(p => p.tenhang.toLowerCase().includes(input));
            if (filtered.length > 0) {
                filtered.forEach(p => {
                    const li = document.createElement('li');
                    li.textContent = `${p.tenhang} - ${p.gia.toLocaleString()} VND`;
                    li.onclick = () => window.location.href = `#${p.tenhang.split(' ')[0].toLowerCase()}`;
                    results.appendChild(li);
                });
                results.style.display = 'block';
            } else {
                results.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', updateBasketDisplay);
    </script>
</body>
</html>