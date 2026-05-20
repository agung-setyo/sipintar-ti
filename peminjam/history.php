<?php
include_once __DIR__ . '/../config/database.php';
include '../config/session.php';
include '../middleware/auth.php';
include '../middleware/peminjam.php';

global $conn;
$user_id = (int)$_SESSION['user_id'];

// ============================================
// FETCH BORROW HISTORY WITH DETAILS
// ============================================

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Build query
$where_clauses = ["br.user_id = $user_id"];
if ($status_filter && in_array($status_filter, ['pending', 'approved', 'rejected', 'returned'])) {
    $where_clauses[] = "br.status = '$status_filter'";
}
if ($search) {
    $where_clauses[] = "(br.request_code LIKE '%$search%' OR i.name LIKE '%$search%')";
}
$where_sql = implode(" AND ", $where_clauses);

// Sorting
$sort_options = [
    'latest' => 'br.created_at DESC',
    'oldest' => 'br.created_at ASC',
    'borrow_date_asc' => 'br.borrow_date ASC',
    'borrow_date_desc' => 'br.borrow_date DESC'
];
$order_sql = $sort_options[$sort] ?? 'br.created_at DESC';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(DISTINCT br.id) as total 
                FROM borrow_requests br 
                LEFT JOIN borrow_details bd ON br.id = bd.borrow_request_id 
                LEFT JOIN items i ON bd.item_id = i.id 
                WHERE $where_sql";
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $limit);

// Main query with items grouped
$query = "
    SELECT br.*, 
           GROUP_CONCAT(i.name SEPARATOR ', ') as items_list,
           GROUP_CONCAT(i.item_code SEPARATOR ', ') as items_code,
           COUNT(bd.item_id) as total_items
    FROM borrow_requests br 
    LEFT JOIN borrow_details bd ON br.id = bd.borrow_request_id 
    LEFT JOIN items i ON bd.item_id = i.id 
    WHERE $where_sql
    GROUP BY br.id
    ORDER BY $order_sql
    LIMIT $offset, $limit
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned
    FROM borrow_requests 
    WHERE user_id = $user_id
";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR-TI | Riwayat Peminjaman</title>
    
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
            --info: #3B82F6;
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

        /* Page Header */
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
            grid-template-columns: repeat(5, 1fr);
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
            transition: all 0.3s;
            cursor: pointer;
        }

        .stat-mini-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-mini-card.active {
            border-color: var(--primary);
            background: var(--primary-bg);
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

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .table-custom {
            margin-bottom: 0;
        }

        .table-custom thead th {
            background: var(--gray-50);
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .table-custom tbody tr:hover {
            background: var(--gray-50);
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-approved {
            background: rgba(16,185,129,0.1);
            color: var(--success);
        }

        .status-pending {
            background: rgba(245,158,11,0.1);
            color: var(--warning);
        }

        .status-rejected {
            background: rgba(239,68,68,0.1);
            color: var(--danger);
        }

        .status-returned {
            background: rgba(59,130,246,0.1);
            color: var(--info);
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s;
        }

        .btn-view {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .btn-view:hover {
            background: var(--primary);
            color: white;
        }

        .btn-cancel {
            background: rgba(239,68,68,0.1);
            color: var(--danger);
        }

        .btn-cancel:hover {
            background: var(--danger);
            color: white;
        }

        /* Items List */
        .items-list {
            max-width: 250px;
        }

        .items-list .item-tag {
            display: inline-block;
            background: var(--gray-100);
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            margin: 0.125rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            padding: 1.5rem;
            background: white;
            border-top: 1px solid var(--gray-200);
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        /* Modal Styles */
        .modal-custom .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header-custom {
            background: var(--gradient-primary);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.25rem 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray-600);
        }

        .detail-value {
            color: var(--gray-800);
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
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .content-container {
                padding: 1rem;
            }
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            .filter-bar .row {
                gap: 0.75rem;
            }
            .user-info {
                display: none;
            }
            .table-container {
                overflow-x: auto;
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
        <a href="items.php" class="nav-item">
            <i class="ti ti-package"></i>
            <span class="sidebar-text">Daftar Barang</span>
        </a>
        <a href="history.php" class="nav-item active">
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
            <h1 class="page-title">Riwayat Peminjaman</h1>
            <p class="page-subtitle">Lihat dan pantau semua aktivitas peminjaman Anda</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-mini-card <?= $status_filter == '' ? 'active' : '' ?>" onclick="window.location.href='?status='">
                <div class="stat-mini-icon"><i class="ti ti-chart-bar"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['total']) ?></h3>
                    <p>Semua</p>
                </div>
            </div>
            <div class="stat-mini-card <?= $status_filter == 'pending' ? 'active' : '' ?>" onclick="window.location.href='?status=pending'">
                <div class="stat-mini-icon"><i class="ti ti-clock"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['pending']) ?></h3>
                    <p>Menunggu</p>
                </div>
            </div>
            <div class="stat-mini-card <?= $status_filter == 'approved' ? 'active' : '' ?>" onclick="window.location.href='?status=approved'">
                <div class="stat-mini-icon"><i class="ti ti-check"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['approved']) ?></h3>
                    <p>Disetujui</p>
                </div>
            </div>
            <div class="stat-mini-card <?= $status_filter == 'returned' ? 'active' : '' ?>" onclick="window.location.href='?status=returned'">
                <div class="stat-mini-icon"><i class="ti ti-check-circle"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['returned']) ?></h3>
                    <p>Dikembalikan</p>
                </div>
            </div>
            <div class="stat-mini-card <?= $status_filter == 'rejected' ? 'active' : '' ?>" onclick="window.location.href='?status=rejected'">
                <div class="stat-mini-icon"><i class="ti ti-x-circle"></i></div>
                <div class="stat-mini-info">
                    <h3><?= number_format($stats['rejected']) ?></h3>
                    <p>Ditolak</p>
                </div>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" id="filterForm">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="search-input-group">
                            <i class="ti ti-search"></i>
                            <input type="text" name="search" placeholder="Cari berdasarkan kode atau nama barang..." 
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="sort" class="filter-select w-100" onchange="this.form.submit()">
                            <option value="latest" <?= $sort == 'latest' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Terlama</option>
                            <option value="borrow_date_asc" <?= $sort == 'borrow_date_asc' ? 'selected' : '' ?>>Tanggal Pinjam (Awal)</option>
                            <option value="borrow_date_desc" <?= $sort == 'borrow_date_desc' ? 'selected' : '' ?>>Tanggal Pinjam (Akhir)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <?php if ($search || $status_filter || $sort != 'latest'): ?>
                            <a href="history.php" class="btn btn-outline-secondary w-100" style="border-radius: 12px;">
                                <i class="ti ti-refresh"></i> Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            </form>
        </div>
        
        <!-- History Table -->
        <div class="table-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Request</th>
                                <th>Barang</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1 + $offset;
                            while($row = mysqli_fetch_assoc($result)) : 
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= $no++ ?></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($row['request_code']) ?></span>
                                    <br>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="items-list">
                                        <?php 
                                        $items = explode(', ', $row['items_list']);
                                        foreach ($items as $item): 
                                        ?>
                                            <span class="item-tag"><?= htmlspecialchars($item) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <small class="text-muted"><?= $row['total_items'] ?> item(s)</small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['borrow_date'])) ?></td>
                                <td>
                                    <?php 
                                    $return_date = $row['return_date'] ? date('d/m/Y', strtotime($row['return_date'])) : '-';
                                    echo $return_date;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_icon = '';
                                    $status_text = '';
                                    
                                    switch($row['status']) {
                                        case 'approved':
                                            $status_class = 'status-approved';
                                            $status_icon = 'check';
                                            $status_text = 'Disetujui';
                                            break;
                                        case 'pending':
                                            $status_class = 'status-pending';
                                            $status_icon = 'clock';
                                            $status_text = 'Menunggu';
                                            break;
                                        case 'rejected':
                                            $status_class = 'status-rejected';
                                            $status_icon = 'x';
                                            $status_text = 'Ditolak';
                                            break;
                                        case 'returned':
                                            $status_class = 'status-returned';
                                            $status_icon = 'check-circle';
                                            $status_text = 'Dikembalikan';
                                            break;
                                    }
                                    ?>
                                    <span class="status-badge <?= $status_class ?>">
                                        <i class="ti ti-<?= $status_icon ?>"></i> <?= $status_text ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn-action btn-view" onclick="showDetail(<?= $row['id'] ?>)">
                                            <i class="ti ti-eye"></i> Detail
                                        </button>
                                        <?php if($row['status'] == 'pending'): ?>
                                            <a href="cancel.php?id=<?= (int)$row['id']; ?>" 
                                               class="btn-action btn-cancel"
                                               onclick="return confirm('Yakin ingin membatalkan peminjaman ini?')">
                                                <i class="ti ti-x"></i> Batal
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                 </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" class="page-link">
                                <i class="ti ti-chevron-left"></i> Sebelumnya
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                               class="page-link <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" class="page-link">
                                Selanjutnya <i class="ti ti-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="ti ti-history-off"></i>
                    <h5>Tidak ada riwayat peminjaman</h5>
                    <p class="text-muted">Anda belum melakukan peminjaman apapun.</p>
                    <a href="borrow.php" class="btn-action btn-view" style="display: inline-block; padding: 0.5rem 1.5rem; background: var(--primary); color: white;">
                        <i class="ti ti-plus"></i> Pinjam Barang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title"><i class="ti ti-info-circle"></i> Detail Peminjaman</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalContent">
                <div class="text-center">
                    <div class="loading-spinner">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Show detail modal
function showDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const modalContent = document.getElementById('modalContent');
    
    modalContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat data...</p></div>';
    modal.show();
    
    fetch(`get_detail.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalContent.innerHTML = `
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-barcode"></i> Kode Request</span>
                        <span class="detail-value fw-semibold">${data.request_code}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-package"></i> Barang</span>
                        <span class="detail-value">${data.items}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-calendar"></i> Tanggal Pinjam</span>
                        <span class="detail-value">${data.borrow_date}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-calendar-return"></i> Tanggal Kembali</span>
                        <span class="detail-value">${data.return_date || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-message"></i> Tujuan</span>
                        <span class="detail-value">${data.purpose || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-info-circle"></i> Status</span>
                        <span class="detail-value">${data.status_badge}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="ti ti-clock"></i> Diajukan Pada</span>
                        <span class="detail-value">${data.created_at}</span>
                    </div>
                `;
            } else {
                modalContent.innerHTML = `<div class="text-center py-4 text-danger"><i class="ti ti-alert-circle" style="font-size: 3rem;"></i><p>${data.message}</p></div>`;
            }
        })
        .catch(error => {
            modalContent.innerHTML = `<div class="text-center py-4 text-danger"><i class="ti ti-alert-circle" style="font-size: 3rem;"></i><p>Gagal memuat data</p></div>`;
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