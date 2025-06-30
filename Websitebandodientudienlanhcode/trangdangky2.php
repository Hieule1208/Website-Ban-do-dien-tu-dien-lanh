<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1f4037, #99f2c8);
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Particle Background */
        #particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 340px;
            text-align: center;
            position: relative;
            z-index: 1;
            animation: bounceIn 1s ease-out;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.8); opacity: 0; }
            60% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }

        h2 {
            color: #1f4037;
            font-size: 32px;
            margin-bottom: 30px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            font-size: 14px;
            color: #555;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .input-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .input-group input:focus,
        .input-group input:not(:placeholder-shown) {
            border-color: #99f2c8;
            box-shadow: 0 0 10px rgba(153, 242, 200, 0.5);
            outline: none;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #1f4037;
            background: #fff;
            padding: 0 5px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #1f4037, #99f2c8);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #99f2c8, #1f4037);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .home-icon {
            position: absolute;
            top: 20px;
            left: 20px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .home-icon:hover {
            transform: scale(1.2) rotate(360deg);
        }

        /* Decorative Elements */
        .login-container::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, rgba(153, 242, 200, 0.5), transparent);
            border-radius: 50%;
            z-index: -1;
        }

        .login-container::after {
            content: '';
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, rgba(31, 64, 55, 0.5), transparent);
            border-radius: 50%;
            z-index: -1;
        }
    </style>
</head>
<body>
    <!-- Particle Background -->
    <canvas id="particles"></canvas>

    <a href="testtrangchu.php" class="home-icon">
        <img src="img/home.png" alt="Home" style="width: 40px; height: auto;">
    </a>

    <div class="login-container">
        <h2>Đăng Ký</h2>
        <form action="getthongtindangky.php" method="post">
            <div class="input-group">
                <input type="email" id="email" name="email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <label for="password">Mật khẩu</label>
            </div>
            <button type="submit" class="btn-login">Đăng Ký</button>
        </form>
    </div>

    <!-- JavaScript for Particle Effect -->
    <script>
        const canvas = document.getElementById('particles');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particlesArray = [];
        const numberOfParticles = 50;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 5 + 1;
                this.speedX = Math.random() * 1 - 0.5;
                this.speedY = Math.random() * 1 - 0.5;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.size > 0.2) this.size -= 0.01;
            }
            draw() {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function init() {
            for (let i = 0; i < numberOfParticles; i++) {
                particlesArray.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();
                if (particlesArray[i].size <= 0.2) {
                    particlesArray.splice(i, 1);
                    i--;
                    particlesArray.push(new Particle());
                }
            }
            requestAnimationFrame(animate);
        }

        init();
        animate();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    </script>
</body>
</html>