<?php
include_once __DIR__ . '/../config/database.php';
include '../config/session.php';
include '../helpers/csrf_helper.php';
include '../middleware/auth.php';
include '../middleware/peminjam.php';

global $conn;
$user_id = (int)$_SESSION['user_id'];

// ============================================
// PROCESS BORROW REQUEST
// ============================================
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        // Get and sanitize input
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $borrow_date = $_POST['borrow_date'] ?? '';
        $return_date = $_POST['return_date'] ?? '';
        $purpose = trim(htmlspecialchars($_POST['purpose'] ?? ''));
        
        // Validation
        $errors = [];
        
        if (!$item_id) $errors[] = 'Item harus dipilih';
        if ($quantity < 1) $errors[] = 'Quantity minimal 1';
        if (!$borrow_date) $errors[] = 'Tanggal pinjam harus diisi';
        if (!$return_date) $errors[] = 'Tanggal kembali harus diisi';
        if (strlen($purpose) < 10) $errors[] = 'Tujuan peminjaman minimal 10 karakter';
        
        // Date validation
        if ($borrow_date && $return_date) {
            $borrow_timestamp = strtotime($borrow_date);
            $return_timestamp = strtotime($return_date);
            $today = strtotime(date('Y-m-d'));
            
            if ($borrow_timestamp < $today) {
                $errors[] = 'Tanggal pinjam tidak boleh kurang dari hari ini';
            }
            if ($return_timestamp <= $borrow_timestamp) {
                $errors[] = 'Tanggal kembali harus lebih besar dari tanggal pinjam';
            }
            $diff_days = ($return_timestamp - $borrow_timestamp) / (60 * 60 * 24);
            if ($diff_days > 14) {
                $errors[] = 'Maksimal peminjaman adalah 14 hari';
            }
        }
        
        // Check item availability if no validation errors yet
        if (empty($errors)) {
            $check = $conn->prepare("SELECT id, name, stock FROM items WHERE id = ? AND status = 'available'");
            $check->bind_param("i", $item_id);
            $check->execute();
            $result = $check->get_result();
            $item = $result->fetch_assoc();
            
            if (!$item) {
                $errors[] = 'Item tidak ditemukan atau tidak tersedia';
            } elseif ($item['stock'] < $quantity) {
                $errors[] = "Stok tidak cukup. Stok tersedia: {$item['stock']}";
            }
        }
        
        // If no errors, process the borrow request
        if (empty($errors)) {
            $status = "pending";
            $request_code = "BRW-" . date('Ymd') . "-" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert into borrow_requests
                $insert_borrow = $conn->prepare(
                    "INSERT INTO borrow_requests (user_id, request_code, borrow_date, return_date, purpose, status, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $insert_borrow->bind_param("isssss", $user_id, $request_code, $borrow_date, $return_date, $purpose, $status);
                
                if (!$insert_borrow->execute()) {
                    throw new Exception("Gagal membuat request: " . $insert_borrow->error);
                }
                
                $borrow_id = $insert_borrow->insert_id;
                
                // Insert into borrow_details
                $insert_detail = $conn->prepare(
                    "INSERT INTO borrow_details (borrow_request_id, item_id, quantity) VALUES (?, ?, ?)"
                );
                $insert_detail->bind_param("iii", $borrow_id, $item_id, $quantity);
                
                if (!$insert_detail->execute()) {
                    throw new Exception("Gagal menambah detail: " . $insert_detail->error);
                }
                
                // Commit transaction
                $conn->commit();
                $success = "Peminjaman berhasil diajukan! Kode Request: " . $request_code;
                
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'history.php';
                    }, 2000);
                </script>";
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

// ============================================
// FETCH AVAILABLE ITEMS WITH CATEGORY JOIN
// ============================================
// Fix: Using JOIN with categories table
$items_query = "
    SELECT i.id, i.name, i.stock, i.description, c.name as category_name 
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    WHERE i.status = 'available' AND i.stock > 0 
    ORDER BY c.name, i.name ASC
";
$items = mysqli_query($conn, $items_query);

if (!$items) {
    die("Query gagal: " . mysqli_error($conn));
}

// Get user's active loans count
$active_query = $conn->prepare("
    SELECT COUNT(*) as total FROM borrow_requests 
    WHERE user_id = ? AND status IN ('pending', 'approved')
");
$active_query->bind_param("i", $user_id);
$active_query->execute();
$active_loans = $active_query->get_result()->fetch_assoc()['total'];

// Get user's name for avatar
$user_name = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR-TI | Form Peminjaman Barang</title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            --primary-bg: rgba(15, 59, 60, 0.08);
            --accent: #E8A87C;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --gray-50: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E1;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1E293B;
            --gradient-primary: linear-gradient(135deg, #0F3B3C 0%, #1E5556 100%);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-800);
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: var(--gradient-primary);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 4px; }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .sidebar-badge,
        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 0.875rem;
        }

        .sidebar.collapsed .nav-item i {
            margin: 0;
            font-size: 1.5rem;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .logo-text h3 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .logo-text p {
            font-size: 0.7rem;
            margin: 0;
            color: rgba(255,255,255,0.7);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 0.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 12px;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }

        .nav-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .nav-item i { font-size: 1.25rem; width: 24px; }
        .sidebar-text { font-size: 0.875rem; font-weight: 500; flex: 1; }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Top Navbar */
        .top-navbar {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            padding: 0.875rem 2rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid var(--gray-200);
        }

        .toggle-sidebar {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-600);
            margin-right: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .toggle-sidebar:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        .user-menu { display: flex; align-items: center; gap: 1rem; }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .user-profile:hover { background: var(--gray-100); }

        .user-avatar {
            width: 44px;
            height: 44px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .user-info { text-align: right; }
        .user-name { font-size: 0.875rem; font-weight: 600; color: var(--gray-800); }
        .user-role { font-size: 0.7rem; color: var(--gray-500); }

        /* Form Container */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .form-header {
            background: var(--gradient-primary);
            padding: 1.5rem 2rem;
            color: white;
        }

        .form-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .form-header p {
            margin: 0.25rem 0 0;
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .form-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--primary);
            font-size: 1rem;
        }

        .required {
            color: var(--danger);
            font-size: 0.7rem;
        }

        .form-control, .form-select {
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-bg);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Info Cards */
        .info-card {
            background: var(--gray-50);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-bg);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary);
        }

        .info-content h6 {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .info-content p {
            margin: 0.25rem 0 0;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Alert Messages */
        .alert-custom {
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border: none;
        }

        .alert-custom i {
            font-size: 1.25rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        /* Buttons */
        .btn-submit {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-cancel {
            background: var(--gray-100);
            color: var(--gray-600);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: block;
        }

        .btn-cancel:hover {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
            }
            .form-body {
                padding: 1.5rem;
            }
            .user-info {
                display: none;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">
            <i class="ti ti-package"></i>
        </div>
        <div class="logo-text">
            <h3>SIPINTAR-TI</h3>
            <p>Sistem Informasi Peminjaman</p>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item">
            <i class="ti ti-dashboard"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>
        <a href="borrow.php" class="nav-item active">
            <i class="ti ti-plus-circle"></i>
            <span class="sidebar-text">Pinjam Barang</span>
        </a>
        <a href="items.php" class="nav-item">
            <i class="ti ti-package"></i>
            <span class="sidebar-text">Daftar Barang</span>
        </a>
        <a href="history.php" class="nav-item">
            <i class="ti ti-history"></i>
            <span class="sidebar-text">Riwayat</span>
        </a>
        <a href="history.php" class="nav-item">
            <i class="ti ti-x-circle"></i>
            <span class="sidebar-text">Batalkan</span>
        </a>
        <a href="../auth/logout.php" class="nav-item">
            <i class="ti ti-logout"></i>
            <span class="sidebar-text">Logout</span>
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="ti ti-menu-2"></i>
        </button>
        
        <div class="user-menu">
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                    <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['identity_type'] ?? ($_SESSION['role'] ?? 'Peminjam'))) ?></div>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($user_name, 0, 2)) ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="form-container fade-in">
        <div class="form-card">
            <div class="form-header">
                <h3><i class="ti ti-plus-circle"></i> Form Peminjaman Barang</h3>
                <p>Isi formulir di bawah ini untuk mengajukan peminjaman inventaris</p>
            </div>
            
            <div class="form-body">
                <!-- Info Cards -->
                <div class="info-card">
                    <div class="info-icon">
                        <i class="ti ti-info-circle"></i>
                    </div>
                    <div class="info-content">
                        <h6>Informasi Peminjaman</h6>
                        <p>Maksimal peminjaman 14 hari. Pastikan barang tersedia sebelum mengajukan.</p>
                    </div>
                </div>
                
                <?php if ($active_loans >= 3): ?>
                <div class="alert-custom alert-warning">
                    <i class="ti ti-alert-triangle"></i>
                    <div>Anda memiliki <?= $active_loans ?> peminjaman aktif. Maksimal 3 peminjaman aktif dalam satu waktu.</div>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert-custom alert-error">
                    <i class="ti ti-alert-circle"></i>
                    <div><?= $error ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert-custom alert-success">
                    <i class="ti ti-check-circle"></i>
                    <div><?= $success ?></div>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="borrowForm" onsubmit="return validateForm()">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="ti ti-package"></i> Pilih Barang
                            <span class="required">*</span>
                        </label>
                        <select name="item_id" id="item_id" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php 
                            $current_category = '';
                            while($item = mysqli_fetch_assoc($items)) : 
                                // Group by category
                                if ($current_category != $item['category_name']) {
                                    if ($current_category != '') echo '</optgroup>';
                                    $current_category = $item['category_name'];
                                    echo '<optgroup label="' . htmlspecialchars($current_category ?: 'Uncategorized') . '">';
                                }
                            ?>
                                <option value="<?= $item['id']; ?>" 
                                        data-stock="<?= $item['stock']; ?>"
                                        data-category="<?= htmlspecialchars($item['category_name']); ?>">
                                    <?= htmlspecialchars($item['name']); ?> 
                                    (Stok: <?= $item['stock']; ?>)
                                </option>
                            <?php 
                            endwhile;
                            if ($current_category != '') echo '</optgroup>';
                            ?>
                        </select>
                        <small class="text-muted" id="stockInfo"></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="ti ti-calculator"></i> Jumlah
                            <span class="required">*</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="10" required>
                        <small class="text-muted" id="quantityInfo"></small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="ti ti-calendar"></i> Tanggal Pinjam
                                    <span class="required">*</span>
                                </label>
                                <input type="date" name="borrow_date" id="borrow_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="ti ti-calendar-return"></i> Tanggal Kembali
                                    <span class="required">*</span>
                                </label>
                                <input type="date" name="return_date" id="return_date" class="form-control" required>
                                <small class="text-muted" id="dateInfo"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="ti ti-message"></i> Tujuan Peminjaman
                            <span class="required">*</span>
                        </label>
                        <textarea name="purpose" id="purpose" class="form-control" placeholder="Contoh: Untuk praktikum mata kuliah Pemrograman Web..." required></textarea>
                        <small class="text-muted" id="purposeInfo"></small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" name="submit" class="btn-submit" id="submitBtn">
                                <i class="ti ti-send"></i> Ajukan Peminjaman
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="dashboard.php" class="btn-cancel">
                                <i class="ti ti-x"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Toggle sidebar
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleSidebar');
const mainContent = document.getElementById('mainContent');

toggleBtn.addEventListener('click', () => {
    if (window.innerWidth <= 1024) {
        sidebar.classList.toggle('mobile-open');
    } else {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    }
    
    // Save state
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
});

// Load saved sidebar state
if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 1024) {
    sidebar.classList.add('collapsed');
    mainContent.classList.add('expanded');
}

// Close mobile sidebar when clicking outside
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('mobile-open');
        }
    }
});

// Stock info on item select
const itemSelect = document.getElementById('item_id');
const quantityInput = document.getElementById('quantity');
const stockInfo = document.getElementById('stockInfo');
const quantityInfo = document.getElementById('quantityInfo');

itemSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const stock = selectedOption.dataset.stock;
    const category = selectedOption.dataset.category;
    
    if (stock) {
        if (stock < 5) {
            stockInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #F59E0B;"></i> <span style="color: #F59E0B;">Stok terbatas: ${stock} unit tersisa</span>`;
        } else {
            stockInfo.innerHTML = `<i class="ti ti-check" style="color: #10B981;"></i> <span style="color: #10B981;">Stok tersedia: ${stock} unit</span>`;
        }
        quantityInput.max = stock;
        
        if (parseInt(quantityInput.value) > parseInt(stock)) {
            quantityInput.value = stock;
            quantityInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #EF4444;"></i> <span style="color: #EF4444;">Jumlah melebihi stok. Maksimal ${stock}</span>`;
        }
    } else {
        stockInfo.innerHTML = '';
    }
});

// Validate quantity
quantityInput.addEventListener('input', function() {
    const maxStock = itemSelect.options[itemSelect.selectedIndex]?.dataset.stock || 0;
    const value = parseInt(this.value);
    
    if (isNaN(value) || value < 1) {
        quantityInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #EF4444;"></i> <span style="color: #EF4444;">Minimal 1 item</span>`;
        this.classList.add('is-invalid');
    } else if (value > maxStock) {
        quantityInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #EF4444;"></i> <span style="color: #EF4444;">Jumlah melebihi stok. Maksimal ${maxStock}</span>`;
        this.classList.add('is-invalid');
    } else {
        quantityInfo.innerHTML = `<i class="ti ti-check" style="color: #10B981;"></i> <span style="color: #10B981;">Stok mencukupi</span>`;
        this.classList.remove('is-invalid');
    }
});

// Date validation
const borrowDate = document.getElementById('borrow_date');
const returnDate = document.getElementById('return_date');
const dateInfo = document.getElementById('dateInfo');

// Set min date to today
const today = new Date().toISOString().split('T')[0];
borrowDate.min = today;
returnDate.min = today;

function validateDates() {
    const borrow = borrowDate.value;
    const ret = returnDate.value;
    
    if (borrow && ret) {
        const borrowTimestamp = new Date(borrow).getTime();
        const returnTimestamp = new Date(ret).getTime();
        const diffDays = Math.ceil((returnTimestamp - borrowTimestamp) / (1000 * 60 * 60 * 24));
        
        if (returnTimestamp <= borrowTimestamp) {
            dateInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #EF4444;"></i> <span style="color: #EF4444;">Tanggal kembali harus lebih besar dari tanggal pinjam</span>`;
            return false;
        } else if (diffDays > 14) {
            dateInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #F59E0B;"></i> <span style="color: #F59E0B;">Peminjaman ${diffDays} hari (maksimal 14 hari)</span>`;
            return false;
        } else {
            dateInfo.innerHTML = `<i class="ti ti-check" style="color: #10B981;"></i> <span style="color: #10B981;">Durasi peminjaman ${diffDays} hari</span>`;
            return true;
        }
    }
    return true;
}

borrowDate.addEventListener('change', validateDates);
returnDate.addEventListener('change', validateDates);

// Set default dates
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const nextWeek = new Date();
nextWeek.setDate(nextWeek.getDate() + 7);

borrowDate.value = today;
returnDate.value = tomorrow.toISOString().split('T')[0];
validateDates();

// Purpose validation
const purposeInput = document.getElementById('purpose');
const purposeInfo = document.getElementById('purposeInfo');

purposeInput.addEventListener('input', function() {
    const length = this.value.length;
    if (length < 10 && length > 0) {
        purposeInfo.innerHTML = `<i class="ti ti-alert-triangle" style="color: #F59E0B;"></i> <span style="color: #F59E0B;">Minimal 10 karakter (${length}/10)</span>`;
        this.classList.add('is-invalid');
    } else if (length >= 10) {
        purposeInfo.innerHTML = `<i class="ti ti-check" style="color: #10B981;"></i> <span style="color: #10B981;">Tujuan peminjaman valid</span>`;
        this.classList.remove('is-invalid');
    } else {
        purposeInfo.innerHTML = '';
    }
});

// Form validation
function validateForm() {
    const item = itemSelect.value;
    const quantity = quantityInput.value;
    const borrow = borrowDate.value;
    const ret = returnDate.value;
    const purpose = purposeInput.value.trim();
    
    if (!item) {
        alert('Silakan pilih barang yang akan dipinjam');
        itemSelect.focus();
        return false;
    }
    
    if (!quantity || quantity < 1) {
        alert('Jumlah peminjaman minimal 1 item');
        quantityInput.focus();
        return false;
    }
    
    const maxStock = itemSelect.options[itemSelect.selectedIndex]?.dataset.stock || 0;
    if (quantity > maxStock) {
        alert(`Jumlah peminjaman (${quantity}) melebihi stok tersedia (${maxStock})`);
        quantityInput.focus();
        return false;
    }
    
    if (!borrow) {
        alert('Silakan pilih tanggal pinjam');
        borrowDate.focus();
        return false;
    }
    
    if (!ret) {
        alert('Silakan pilih tanggal kembali');
        returnDate.focus();
        return false;
    }
    
    if (!validateDates()) {
        alert('Tanggal kembali harus lebih besar dari tanggal pinjam dan maksimal 14 hari');
        returnDate.focus();
        return false;
    }
    
    if (purpose.length < 10) {
        alert('Tujuan peminjaman minimal 10 karakter');
        purposeInput.focus();
        return false;
    }
    
    <?php if ($active_loans >= 3): ?>
    alert('Anda memiliki 3 peminjaman aktif. Maksimal 3 peminjaman dalam satu waktu.');
    return false;
    <?php endif; ?>
    
    // Disable submit button to prevent double submission
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ti ti-loader"></i> Memproses...';
    
    return true;
}
</script>

</body>
</html>