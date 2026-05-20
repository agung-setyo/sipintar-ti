<?php
include_once __DIR__ . '/../config/database.php';
include '../config/session.php';
include '../middleware/auth.php';
include '../middleware/peminjam.php';

global $conn;

// ============================================
// FETCH ITEMS WITH CATEGORY JOIN
// ============================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Build query
$where_clauses = [];
if ($search) {
    $where_clauses[] = "(items.name LIKE '%$search%' OR items.item_code LIKE '%$search%')";
}
if ($category_filter > 0) {
    $where_clauses[] = "items.category_id = $category_filter";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Sorting
$sort_options = [
    'name' => 'items.name ASC',
    'name_desc' => 'items.name DESC',
    'stock_asc' => 'items.stock ASC',
    'stock_desc' => 'items.stock DESC',
    'category' => 'categories.name ASC'
];
$order_sql = isset($sort_options[$sort]) ? "ORDER BY " . $sort_options[$sort] : "ORDER BY items.name ASC";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM items JOIN categories ON items.category_id = categories.id $where_sql";
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $limit);

// Main query with pagination
$query = "
    SELECT items.*, categories.name as category_name 
    FROM items 
    JOIN categories ON items.category_id = categories.id 
    $where_sql 
    $order_sql 
    LIMIT $offset, $limit
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Fetch categories for filter dropdown
$categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $cat;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR-TI | Daftar Inventaris</title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            justify-content: space-between;
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

        /* Content Container */
        .content-container {
            padding: 2rem;
        }

        /* Header Section */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-mini-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid var(--gray-200);
        }

        .stat-mini-icon {
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

        .stat-mini-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-mini-info p {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin: 0;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--gray-200);
        }

        .search-input-group {
            position: relative;
        }

        .search-input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1rem;
        }

        .search-input-group input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .search-input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-bg);
        }

        .filter-select {
            padding: 0.625rem 1rem;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.875rem;
            background: white;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Items Grid */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .item-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s;
            border: 1px solid var(--gray-200);
            position: relative;
        }

        .item-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .item-card.stock-low {
            border-left: 4px solid var(--warning);
        }

        .item-card.stock-out {
            border-left: 4px solid var(--danger);
            opacity: 0.7;
        }

        .item-card-body {
            padding: 1.25rem;
        }

        .item-category {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--primary-bg);
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .item-code {
            font-size: 0.7rem;
            color: var(--gray-400);
            font-family: monospace;
            margin-bottom: 0.5rem;
        }

        .item-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
        }

        .item-stock {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .stock-high {
            background: rgba(16,185,129,0.1);
            color: var(--success);
        }

        .stock-medium {
            background: rgba(245,158,11,0.1);
            color: var(--warning);
        }

        .stock-low-badge {
            background: rgba(239,68,68,0.1);
            color: var(--danger);
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background: var(--gray-200);
        }

        .progress-bar {
            border-radius: 3px;
        }

        .item-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-borrow {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-borrow:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-borrow.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .btn-detail {
            background: var(--gray-100);
            color: var(--gray-600);
            border: none;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-detail:hover {
            background: var(--gray-200);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.3s;
        }

        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .content-container {
                padding: 1rem;
            }
            .stats-row {
                grid-template-columns: 1fr;
            }
            .filter-bar .row {
                gap: 0.75rem;
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
        <a href="borrow.php" class="nav-item">
            <i class="ti ti-plus-circle"></i>
            <span class="sidebar-text">Pinjam Barang</span>
        </a>
        <a href="items.php" class="nav-item active">
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
                    <div class="user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                    <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['identity_type'] ?? ($_SESSION['role'] ?? 'Peminjam'))) ?></div>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 2)) ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="content-container fade-in">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Daftar Inventaris</h1>
            <p class="page-subtitle">Kelola dan lihat ketersediaan barang inventaris</p>
        </div>
        
        <!-- Statistics -->
        <?php
        // Calculate stats
        $total_items_query = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(stock) as total_stock FROM items");
        $stats = mysqli_fetch_assoc($total_items_query);
        $low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM items WHERE stock <= 3 AND stock > 0"))['total'];
        $out_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM items WHERE stock = 0"))['total'];
        ?>
        <div class="stats-row">
            <div class="stat-mini-card">
                <div class="stat-mini-icon"><i class="ti ti-package"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['total']) ?></h3>
                    <p>Total Item</p>
                </div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-icon"><i class="ti ti-box"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['total_stock']) ?></h3>
                    <p>Total Stok</p>
                </div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-icon"><i class="ti ti-alert-triangle"></i></div>
                <div class="stat-mini-info">
                    <h3><?= $low_stock ?></h3>
                    <p>Stok Menipis</p>
                </div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-icon"><i class="ti ti-circle-x"></i></div>
                <div class="stat-mini-info">
                    <h3><?= $out_stock ?></h3>
                    <p>Stok Habis</p>
                </div>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" id="filterForm">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="search-input-group">
                            <i class="ti ti-search"></i>
                            <input type="text" name="search" placeholder="Cari barang atau kode..." 
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="filter-select w-100" onchange="this.form.submit()">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="filter-select w-100" onchange="this.form.submit()">
                            <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Nama A-Z</option>
                            <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Nama Z-A</option>
                            <option value="stock_asc" <?= $sort == 'stock_asc' ? 'selected' : '' ?>>Stok (Rendah ke Tinggi)</option>
                            <option value="stock_desc" <?= $sort == 'stock_desc' ? 'selected' : '' ?>>Stok (Tinggi ke Rendah)</option>
                            <option value="category" <?= $sort == 'category' ? 'selected' : '' ?>>Kategori</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <?php if ($search || $category_filter || $sort != 'name'): ?>
                            <a href="items.php" class="btn btn-outline-secondary w-100" style="border-radius: 12px;">
                                <i class="ti ti-refresh"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Items Grid -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="items-grid">
                <?php 
                $no = 1 + $offset;
                while($row = mysqli_fetch_assoc($result)) : 
                    $stock_percent = min(100, ($row['stock'] / 20) * 100);
                    $stock_class = '';
                    $stock_text = '';
                    
                    if ($row['stock'] == 0) {
                        $stock_class = 'stock-out';
                        $stock_text = 'Habis';
                    } elseif ($row['stock'] <= 3) {
                        $stock_class = 'stock-low';
                        $stock_text = 'Menipis';
                    } elseif ($row['stock'] <= 10) {
                        $stock_text = 'Sedang';
                    } else {
                        $stock_text = 'Tersedia';
                    }
                ?>
                    <div class="item-card <?= $row['stock'] <= 3 ? ($row['stock'] == 0 ? 'stock-out' : 'stock-low') : '' ?>" data-aos="fade-up" data-aos-delay="<?= ($no % 6) * 50 ?>">
                        <div class="item-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="item-category">
                                    <i class="ti ti-folder"></i> <?= htmlspecialchars($row['category_name']) ?>
                                </span>
                                <span class="item-code"><?= htmlspecialchars($row['item_code']) ?></span>
                            </div>
                            <h3 class="item-name"><?= htmlspecialchars($row['name']) ?></h3>
                            <div class="item-stock">
                                <span class="stock-badge <?= 
                                    $row['stock'] > 10 ? 'stock-high' : 
                                    ($row['stock'] > 0 ? 'stock-medium' : 'stock-low-badge') 
                                ?>">
                                    <i class="ti ti-<?= $row['stock'] > 0 ? 'package' : 'circle-x' ?>"></i>
                                    Stok: <?= number_format($row['stock']) ?>
                                </span>
                                <span class="stock-badge <?= 
                                    $row['stock'] > 0 ? 'stock-high' : 'stock-low-badge' 
                                ?>">
                                    <?= $stock_text ?>
                                </span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar <?= $row['stock'] == 0 ? 'bg-danger' : ($row['stock'] <= 3 ? 'bg-warning' : 'bg-success') ?>" 
                                     style="width: <?= $stock_percent ?>%"></div>
                            </div>
                            <div class="item-actions">
                                <?php if ($row['stock'] > 0): ?>
                                    <a href="borrow.php?item_id=<?= $row['id'] ?>" class="btn-borrow">
                                        <i class="ti ti-plus"></i> Pinjam
                                    </a>
                                <?php else: ?>
                                    <button class="btn-borrow disabled" disabled>
                                        <i class="ti ti-circle-x"></i> Stok Habis
                                    </button>
                                <?php endif; ?>
                                <a href="item-detail.php?id=<?= $row['id'] ?>" class="btn-detail">
                                    <i class="ti ti-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                $no++;
                endwhile; 
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&sort=<?= $sort ?>" class="page-link">
                            <i class="ti ti-chevron-left"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&sort=<?= $sort ?>" 
                           class="page-link <?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&sort=<?= $sort ?>" class="page-link">
                            Selanjutnya <i class="ti ti-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="ti ti-package-off"></i>
                <h5>Tidak ada barang ditemukan</h5>
                <p class="text-muted">Tidak ada barang yang sesuai dengan kriteria pencarian Anda.</p>
                <a href="items.php" class="btn-borrow" style="display: inline-block; padding: 0.5rem 1.5rem;">
                    <i class="ti ti-refresh"></i> Refresh Halaman
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// Initialize AOS
AOS.init({
    duration: 600,
    once: true,
    offset: 50
});

// Sidebar Toggle
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

// Auto submit on search input delay
let searchTimeout;
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
}

// Animate stats
const statNumbers = document.querySelectorAll('.stat-mini-info h3');
statNumbers.forEach(stat => {
    const finalValue = parseInt(stat.textContent.replace(/\./g, ''));
    if (!isNaN(finalValue)) {
        let currentValue = 0;
        const increment = finalValue / 30;
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue.toLocaleString('id-ID');
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(currentValue).toLocaleString('id-ID');
            }
        }, 30);
    }
});
</script>

</body>
</html>