<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 360px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            margin-bottom: 30px;
            color: #2c3e50;
            font-weight: 600;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: #4CAF50;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            color: #34495e;
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 25px;
            font-size: 14px;
            background: #f9f9f9;
            transition: all 0.3s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4CAF50;
            background: #fff;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.2);
        }

        .register-link {
            margin-bottom: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #45a049;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            transition: transform 0.3s;
        }

        .home-link:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <a href="testtrangchu.php" class="home-link">
        <img src="img/home.png" alt="Home" style="width: 40px; height: auto;">
    </a>
    <div class="login-container">
        <h2>Đăng Nhập</h2>
        <form action="getthongtindangnhap.php" method="post">
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="email" required>
            </div>
            <div class="input-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <div class="register-link">
                Chưa có tài khoản? <a href="trangdangky2.php">Đăng ký</a>
            </div>
            <button type="submit" class="btn-login">Đăng Nhập</button>
        </form>
    </div>

    <script>
        const inputs = document.querySelectorAll('.input-group input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.previousElementSibling.style.color = '#4CAF50';
            });
            input.addEventListener('blur', () => {
                input.previousElementSibling.style.color = '#34495e';
            });
        });

        const form = document.querySelector('form');
        form.addEventListener('submit', (e) => {
            const button = form.querySelector('.btn-login');
            button.textContent = 'Đang đăng nhập...';
            button.disabled = true;
            // Remove the next line in production; it's just for demo
            setTimeout(() => {
                button.textContent = 'Đăng Nhập';
                button.disabled = false;
            }, 2000);
        });
    </script>
</body>
</html>