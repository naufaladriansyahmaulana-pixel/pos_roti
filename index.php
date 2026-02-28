<?php
require_once 'config/config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Sistem Point of Sale Modern</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #5a4a3a;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f5e6d3 0%, #e8d5b7 50%, #d4c4a8 100%);
            background-attachment: fixed;
        }

        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, #8B6F47 0%, #6B5435 100%);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(139, 111, 71, 0.3);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;   
            gap: 12px;             
            text-decoration: none; 
            color: #fff;           
            font-size: 20px;      
            font-weight: 600;
            }

        .logo .image_logo img {
                width: 40px;           
                height: auto;
                display: block;
            }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .btn-login {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-login:hover {
            background: white;
            color: #8B6F47;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.4);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #8B6F47 0%, #d4a574 50%, #e8d5b7 100%);
            color: white;
            padding: 150px 2rem 100px;
            text-align: center;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '🍞';
            position: absolute;
            font-size: 400px;
            opacity: 0.05;
            top: -50px;
            right: -100px;
            transform: rotate(15deg);
            pointer-events: none;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-primary {
            background: white;
            color: #8B6F47;
            padding: 1rem 2.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(139, 111, 71, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(139, 111, 71, 0.5);
            background: #fff8f0;
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 1rem 2.5rem;
            border: 2px solid white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: white;
            color: #8B6F47;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }

        /* Features Section */
        .features {
            padding: 80px 2rem;
            background: linear-gradient(135deg, #f5e6d3 0%, #e8d5b7 50%, #d4c4a8 100%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #8B6F47;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: linear-gradient(to bottom, #fff8f0, #fffbf5);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(139, 111, 71, 0.15);
            transition: all 0.3s;
            text-align: center;
            border: 2px solid rgba(218, 165, 32, 0.2);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '🍞';
            position: absolute;
            font-size: 150px;
            opacity: 0.03;
            top: -30px;
            right: -30px;
            transform: rotate(15deg);
            pointer-events: none;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(139, 111, 71, 0.3);
            border-color: rgba(212, 165, 116, 0.4);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #8B6F47;
            font-weight: 700;
        }

        .feature-card p {
            color: #5a4a3a;
            line-height: 1.8;
        }

        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, #6B5435 0%, #8B6F47 50%, #d4a574 100%);
            color: white;
            padding: 60px 2rem;
            position: relative;
            overflow: hidden;
        }

        .stats::before {
            content: '🍞';
            position: absolute;
            font-size: 300px;
            opacity: 0.05;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(15deg);
            pointer-events: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 80px 2rem;
            background: linear-gradient(to bottom, #fff8f0, #fffbf5);
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #8B6F47;
            font-weight: 700;
        }

        .cta p {
            font-size: 1.2rem;
            color: #5a4a3a;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #5a4530 0%, #6B5435 100%);
            color: white;
            padding: 40px 2rem;
            text-align: center;
        }

        .footer p {
            opacity: 0.8;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <div class="image_logo">
                    <img src="assets/roti_logo.png" alt="roti">
                </div>
                <?php echo APP_NAME; ?></a>
            <div class="nav-links">
                <a href="#features">Fitur</a>
                <a href="#about">Tentang</a>
                <a href="login.php" class="btn-login">Masuk</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>🍞 Kelola Toko Roti Anda dengan Mudah</h1>
            <p>Sistem Point of Sale modern yang membantu Anda mengelola penjualan, stok, dan laporan dengan efisien</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary">Mulai Sekarang</a>
                <a href="#features" class="btn-secondary">Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Fitur Unggulan</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h3>Point of Sale</h3>
                    <p>Sistem kasir modern dengan interface yang mudah digunakan. Proses transaksi cepat dan efisien dengan fitur pencarian roti otomatis.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Laporan Lengkap</h3>
                    <p>Laporan penjualan harian, bulanan, dan tahunan. Analisis data yang akurat untuk membantu pengambilan keputusan bisnis.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Manajemen Stok</h3>
                    <p>Kelola stok roti dengan mudah. Sistem alert untuk stok menipis dan manajemen pembelian dari supplier.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Multi User</h3>
                    <p>Sistem dengan 3 level akses: Admin, Kasir, dan Gudang. Setiap user memiliki hak akses sesuai perannya.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎁</div>
                    <h3>Diskon & PPN</h3>
                    <p>Fitur diskon fleksibel dan perhitungan PPN otomatis. Struk transaksi yang lengkap dan profesional.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Pembayaran Mudah</h3>
                    <p>Proses pembayaran yang cepat dengan perhitungan kembalian otomatis. Dukungan untuk berbagai metode pembayaran.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Akurat</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Tersedia</p>
                </div>
                <div class="stat-item">
                    <h3>3</h3>
                    <p>Level Akses</p>
                </div>
                <div class="stat-item">
                    <h3>∞</h3>
                    <p>Transaksi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="about">
        <div class="container">
            <h2>Siap Memulai?</h2>
            <p>Bergabunglah dengan sistem POS modern yang akan membantu bisnis minimart Anda berkembang</p>
            <a href="login.php" class="btn-primary">Login Sekarang</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">Dibuat dengan ❤️ untuk kemudahan bisnis Anda</p>
        </div>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
