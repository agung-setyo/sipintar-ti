<?php
include 'config/session.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: peminjam/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR-TI — Sistem Informasi Peminjaman Inventaris Teknik Informatika</title>
    <meta name="description" content="Sistem informasi peminjaman inventaris untuk Jurusan Teknik Informatika. Kelola peminjaman dengan mudah, cepat, dan transparan.">
    
    <!-- Bootstrap 5 + Icons + Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0F3B3C;
            --primary-light: #1E5556;
            --primary-dark: #0A2A2B;
            --secondary: #6C9F8A;
            --accent: #E8A87C;
            --accent-light: #F4C9B1;
            --gray-50: #F8F9FA;
            --gray-100: #F1F3F5;
            --gray-200: #E9ECEF;
            --gray-600: #6C757D;
            --gray-800: #343A40;
            --success: #2E8B57;
            --warning: #E6A017;
            --danger: #DC3545;
            --gradient-primary: linear-gradient(135deg, #0F3B3C 0%, #1E5556 100%);
            --gradient-hero: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 40px rgba(0,0,0,0.15);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            overflow-x: hidden;
        }

        /* Modern Navbar */
        .navbar-modern {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-modern.scrolled {
            padding: 0.75rem 0;
            box-shadow: var(--shadow-md);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            box-shadow: var(--shadow-sm);
        }

        .brand-text h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-text p {
            font-size: 0.7rem;
            margin: 0;
            color: var(--gray-600);
            letter-spacing: 0.5px;
        }

        /* Modern Buttons */
        .btn-modern {
            padding: 0.625rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-outline-modern {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline-modern:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        /* Hero Section */
        .hero-section {
            min-height: 90vh;
            display: flex;
            align-items: center;
            background: var(--gradient-hero);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%230F3B3C" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 59, 60, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.125rem;
            line-height: 1.6;
            color: var(--gray-600);
            margin-bottom: 2rem;
            max-width: 500px;
        }

        /* Stats Cards */
        .stats-container {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .stat-card {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        /* Feature Cards Modern */
        .feature-card-modern {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
            height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .feature-card-modern:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(15,59,60,0.1) 0%, rgba(108,159,138,0.1) 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 32px;
            color: var(--primary);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--gray-800);
        }

        .feature-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            background: var(--gradient-primary);
            border-radius: 30px;
            padding: 4rem;
            margin: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .cta-text {
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
        }

        /* Footer Modern */
        .footer-modern {
            background: var(--gray-100);
            padding: 3rem 0 1.5rem;
            margin-top: 4rem;
        }

        .footer-brand {
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: var(--gray-600);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.875rem;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .social-icons {
            display: flex;
            gap: 1rem;
        }

        .social-icon {
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            transition: all 0.3s;
            text-decoration: none;
        }

        .social-icon:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
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

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-section {
                text-align: center;
                padding: 2rem 0;
            }
            
            .stats-container {
                justify-content: center;
            }
            
            .cta-section {
                padding: 2rem;
                text-align: center;
            }
            
            .cta-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Modern Navbar -->
    <nav class="navbar navbar-expand-lg navbar-modern fixed-top" id="navbar">
        <div class="container">
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <i class="ti ti-package"></i>
                </div>
                <div class="brand-text">
                    <h1>SIPINTAR-TI</h1>
                    <p>Sistem Informasi Peminjaman Inventaris</p>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 ms-lg-3">
                    <a href="auth/login.php" class="btn-modern btn-outline-modern">
                        <i class="ti ti-login"></i> Masuk
                    </a>
                    <a href="auth/register.php" class="btn-modern btn-primary-modern">
                        <i class="ti ti-user-plus"></i> Daftar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6" data-aos="fade-up">
                    <div class="hero-badge">
                        <i class="ti ti-school"></i>
                        Teknik Informatika - Universitas Contoh
                    </div>
                    <h1 class="hero-title">
                        Kelola Peminjaman<br>Inventaris dengan Mudah
                    </h1>
                    <p class="hero-subtitle">
                        SIPINTAR-TI adalah solusi modern untuk mengelola peminjaman inventaris di Jurusan Teknik Informatika. Efisien, transparan, dan terintegrasi.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="auth/register.php" class="btn-modern btn-primary-modern">
                            <i class="ti ti-rocket"></i> Mulai Sekarang
                        </a>
                        <a href="#features" class="btn-modern btn-outline-modern">
                            <i class="ti ti-info-circle"></i> Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                    <div class="position-relative">
                        <div class="bg-white rounded-4 shadow-lg p-4">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                    <i class="ti ti-check text-success fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Mudah Digunakan</h6>
                                    <small class="text-muted">Antarmuka intuitif</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                    <i class="ti ti-clock text-info fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Real-time Tracking</h6>
                                    <small class="text-muted">Pantau status peminjaman</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                    <i class="ti ti-shield text-warning fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Aman & Terpercaya</h6>
                                    <small class="text-muted">Data terenkripsi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="hero-badge d-inline-flex mb-3">
                    <i class="ti ti-crown"></i> Fitur Unggulan
                </span>
                <h2 class="display-6 fw-bold mb-3">Solusi Lengkap untuk<br>Manajemen Inventaris</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    Kami menyediakan semua yang Anda butuhkan untuk mengelola peminjaman inventaris dengan efisien
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card-modern">
                        <div class="feature-icon">
                            <i class="ti ti-clipboard-list"></i>
                        </div>
                        <h3 class="feature-title">Pengajuan Mudah</h3>
                        <p class="feature-description">
                            Ajukan peminjaman inventaris kapan saja, di mana saja dengan formulir yang sederhana dan cepat.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card-modern">
                        <div class="feature-icon">
                            <i class="ti ti-clock"></i>
                        </div>
                        <h3 class="feature-title">Pantau Status Real-time</h3>
                        <p class="feature-description">
                            Lacak status permohonan peminjaman Anda secara real-time dengan notifikasi otomatis.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card-modern">
                        <div class="feature-icon">
                            <i class="ti ti-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Manajemen Admin</h3>
                        <p class="feature-description">
                            Admin dapat mengelola, menyetujui, dan memantau semua peminjaman dengan dashboard powerful.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card-modern">
                        <div class="feature-icon">
                            <i class="ti ti-chart-bar"></i>
                        </div>
                        <h3 class="feature-title">Laporan & Analitik</h3>
                        <p class="feature-description">
                            Dapatkan laporan lengkap tentang peminjaman, inventaris, dan statistik penggunaan.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card-modern">
                        <div class="feature-icon">
                            <i class="ti ti-bell"></i>
                        </div>
                        <h3 class="feature-title">Notifikasi Cerdas</h3>
                        <p class="feature-description">
                            Terima notifikasi untuk pengingat pengembalian, persetujuan, dan update status.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card-modern">
                        <div class="feature-icon">
                            <i class="ti ti-database"></i>
                        </div>
                        <h3 class="feature-title">Database Terpusat</h3>
                        <p class="feature-description">
                            Semua data inventaris dan peminjaman tersimpan aman dalam satu database terpusat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <div class="container">
        <div class="cta-section" data-aos="zoom-in">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="cta-title">Siap untuk mulai menggunakan SIPINTAR-TI?</h3>
                    <p class="cta-text mb-lg-0">
                        Bergabunglah dengan ribuan pengguna lainnya yang sudah merasakan kemudahan mengelola inventaris.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="auth/register.php" class="btn-modern" style="background: white; color: var(--primary);">
                        <i class="ti ti-user-plus"></i> Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <div class="brand">
                            <div class="brand-icon">
                                <i class="ti ti-package"></i>
                            </div>
                            <div class="brand-text">
                                <h5 class="mb-0">SIPINTAR-TI</h5>
                                <small>Sistem Informasi Peminjaman Inventaris</small>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-3">
                        Solusi modern untuk mengelola peminjaman inventaris di Jurusan Teknik Informatika dengan efisien dan transparan.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="ti ti-brand-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="ti ti-brand-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="ti ti-brand-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="ti ti-brand-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Tentang</h6>
                    <ul class="footer-links">
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Fitur</a></li>
                        <li><a href="#">Testimoni</a></li>
                        <li><a href="#">Karir</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Dukungan</h6>
                    <ul class="footer-links">
                        <li><a href="#">Pusat Bantuan</a></li>
                        <li><a href="#">Panduan Pengguna</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Hubungi Kami</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <ul class="footer-links">
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                        <li><a href="#">Lisensi</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="text-center">
                <p class="text-muted small mb-0">
                    &copy; <?= date('Y') ?> SIPINTAR-TI. All rights reserved. | Jurusan Teknik Informatika
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
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
        
        // Animated counter for stats
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            
            function updateCounter() {
                start += increment;
                if (start < target) {
                    element.textContent = Math.floor(start);
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target;
                }
            }
            
            updateCounter();
        }
        
        // Trigger counters when stats come into view
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(document.getElementById('statItems'), 1250, 1500);
                    animateCounter(document.getElementById('statUsers'), 850, 1500);
                    animateCounter(document.getElementById('statTransactions'), 320, 1500);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        const statsContainer = document.querySelector('.stats-container');
        if (statsContainer) {
            observer.observe(statsContainer);
        }
        
        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>