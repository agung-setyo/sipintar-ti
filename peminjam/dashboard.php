<?php
include_once __DIR__ . '/../config/database.php';
include '../config/session.php';
include '../middleware/auth.php';
include '../middleware/peminjam.php';

global $conn;

// ============================================
// DATABASE QUERIES WITH PREPARED STATEMENTS
// ============================================
$user_id = (int)$_SESSION['user_id'];

// Get dashboard statistics
$stats = [
    'total' => 0,
    'active' => 0,
    'completed' => 0,
    'pending' => 0,
    'trend' => 0
];

$queries = [
    'total' => "SELECT COUNT(*) as total FROM borrow_requests WHERE user_id = ?",
    'active' => "SELECT COUNT(*) as total FROM borrow_requests WHERE user_id = ? AND status IN ('pending', 'approved')",
    'completed' => "SELECT COUNT(*) as total FROM borrow_requests WHERE user_id = ? AND status = 'returned'",
    'pending' => "SELECT COUNT(*) as total FROM borrow_requests WHERE user_id = ? AND status = 'pending'"
];

foreach ($queries as $key => $sql) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats[$key] = $stmt->get_result()->fetch_assoc()['total'];
}

// Get trend data
$stmtLast = $conn->prepare("
    SELECT COUNT(*) as total FROM borrow_requests 
    WHERE user_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
");
$stmtLast->bind_param("i", $user_id);
$stmtLast->execute();
$lastMonth = $stmtLast->get_result()->fetch_assoc()['total'];

$stmtCurrent = $conn->prepare("
    SELECT COUNT(*) as total FROM borrow_requests 
    WHERE user_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE)
");
$stmtCurrent->bind_param("i", $user_id);
$stmtCurrent->execute();
$thisMonth = $stmtCurrent->get_result()->fetch_assoc()['total'];

$stats['trend'] = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

// Get recent loans
$recentQuery = "
    SELECT br.*, i.name as item_name, i.item_code
    FROM borrow_requests br
    JOIN borrow_details bd ON br.id = bd.borrow_request_id
    JOIN items i ON bd.item_id = i.id
    WHERE br.user_id = ?
    ORDER BY br.created_at DESC
    LIMIT 5
";
$stmtRecent = $conn->prepare($recentQuery);
$stmtRecent->bind_param("i", $user_id);
$stmtRecent->execute();
$recentLoans = $stmtRecent->get_result()->fetch_all(MYSQLI_ASSOC);

// Get monthly chart data
$monthlyStats = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM borrow_requests
        WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $stmt->bind_param("is", $user_id, $month);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['total'];
    $monthlyStats[] = [
        'month' => date('M Y', strtotime($month)),
        'count' => (int)$count
    ];
}

// Helper functions
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper($word[0]);
    }
    return substr($initials, 0, 2);
}

function getStatusBadge($status) {
    $badges = [
        'approved' => ['class' => 'status-approved', 'icon' => 'check', 'text' => 'Disetujui'],
        'pending' => ['class' => 'status-pending', 'icon' => 'clock', 'text' => 'Menunggu'],
        'rejected' => ['class' => 'status-rejected', 'icon' => 'x', 'text' => 'Ditolak'],
        'returned' => ['class' => 'status-returned', 'icon' => 'check-circle', 'text' => 'Dikembalikan']
    ];
    
    $badge = $badges[$status] ?? $badges['pending'];
    return sprintf(
        '<span class="status-badge %s"><i class="ti ti-%s"></i> %s</span>',
        $badge['class'],
        $badge['icon'],
        $badge['text']
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR-TI | Dashboard Peminjam</title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ========================================
           CSS VARIABLES & RESET
        ======================================== */
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
            --secondary: #6C9F8A;
            --accent: #E8A87C;
            --accent-dark: #D68B5C;
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
            --gray-900: #0F172A;
            --gradient-primary: linear-gradient(135deg, #0F3B3C 0%, #1E5556 100%);
            --gradient-accent: linear-gradient(135deg, #E8A87C 0%, #D68B5C 100%);
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1);
            --shadow-2xl: 0 25px 50px -12px rgba(0,0,0,0.25);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-800);
            overflow-x: hidden;
        }

        /* ========================================
           SIDEBAR COMPONENT
        ======================================== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: var(--gradient-primary);
            box-shadow: var(--shadow-2xl);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 4px; }

        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .sidebar-badge,
        .sidebar.collapsed .logo-text { display: none; }
        .sidebar.collapsed .nav-item { justify-content: center; padding: 0.875rem; }
        .sidebar.collapsed .nav-item i { margin: 0; font-size: 1.5rem; }
        .sidebar.collapsed .sidebar-header { justify-content: center; }

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

        .sidebar-badge {
            background: var(--danger);
            color: white;
            border-radius: 20px;
            padding: 0.125rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* ========================================
           MAIN CONTENT
        ======================================== */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        .main-content.expanded { margin-left: 80px; }

        /* ========================================
           TOP NAVBAR
        ======================================== */
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

        .toggle-btn {
            width: 44px;
            height: 44px;
            background: var(--gray-100);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--gray-600);
        }

        .toggle-btn:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.02);
        }

        .search-wrapper { flex: 1; max-width: 360px; margin: 0 2rem; }
        
        .search-input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.75rem;
            border: 1.5px solid var(--gray-200);
            border-radius: 30px;
            font-size: 0.875rem;
            transition: all 0.3s;
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-bg);
        }

        .user-menu { display: flex; align-items: center; gap: 1rem; }
        
        .notification-btn {
            position: relative;
            width: 44px;
            height: 44px;
            background: var(--gray-100);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--gray-600);
        }

        .notification-btn:hover {
            background: var(--primary);
            color: white;
        }

        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 10px;
            height: 10px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid white;
        }

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

        /* ========================================
           DASHBOARD COMPONENTS
        ======================================== */
        .dashboard-container { padding: 2rem; max-width: 1440px; margin: 0 auto; }

        /* Welcome Card */
        .welcome-card {
            background: var(--gradient-primary);
            border-radius: 24px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .welcome-card::after {
            content: '📦';
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 80px;
            opacity: 0.08;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .welcome-title { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
        .welcome-text { opacity: 0.9; margin-bottom: 1.5rem; font-size: 0.875rem; max-width: 500px; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-bg);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--primary);
        }

        .stat-value { font-size: 2rem; font-weight: 800; color: var(--gray-800); margin-bottom: 0.25rem; }
        .stat-label { font-size: 0.8rem; color: var(--gray-500); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .stat-trend { font-size: 0.7rem; margin-top: 0.75rem; display: flex; align-items: center; gap: 4px; }
        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }

        /* Section Title */
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Quick Actions */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .action-card:hover::before { left: 100%; }
        .action-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-xl); border-color: var(--primary); }

        .action-icon {
            width: 64px;
            height: 64px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 26px;
            color: white;
            transition: all 0.3s;
        }

        .action-card:hover .action-icon { transform: scale(1.05); }
        .action-title { font-size: 0.9rem; font-weight: 600; color: var(--gray-800); margin-bottom: 0.5rem; }
        .action-desc { font-size: 0.7rem; color: var(--gray-500); }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title { font-size: 0.95rem; font-weight: 600; margin: 0; }
        .chart-container { height: 280px; position: relative; }

        /* Activity Table */
        .activity-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .activity-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--gray-50);
        }

        .activity-header h5 { margin: 0; font-weight: 700; font-size: 0.95rem; }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .view-all:hover { color: var(--accent); transform: translateX(4px); }

        .table-custom { margin-bottom: 0; }
        
        .table-custom thead th {
            background: var(--gray-50);
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--gray-100);
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

        .status-approved { background: rgba(16,185,129,0.1); color: var(--success); }
        .status-pending { background: rgba(245,158,11,0.1); color: var(--warning); }
        .status-rejected { background: rgba(239,68,68,0.1); color: var(--danger); }
        .status-returned { background: rgba(59,130,246,0.1); color: var(--info); }

        /* Buttons */
        .btn-outline-custom {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.7rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-outline-custom:hover { background: var(--primary); color: white; }

        .btn-primary-custom {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
        }

        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i { font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem; display: block; }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-section { grid-template-columns: 1fr; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }

        @media (max-width: 768px) {
            .dashboard-container { padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .actions-grid { grid-template-columns: 1fr; }
            .search-wrapper { display: none; }
            .user-info { display: none; }
            .welcome-title { font-size: 1.25rem; }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in-up { animation: fadeInUp 0.5s ease-out; }
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
        <a href="dashboard.php" class="nav-item active">
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
        <a href="history.php" class="nav-item">
            <i class="ti ti-history"></i>
            <span class="sidebar-text">Riwayat</span>
        </a>
        <a href="history.php" class="nav-item">
            <i class="ti ti-x-circle"></i>
            <span class="sidebar-text">Batal</span>
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
        <button class="toggle-btn" id="toggleSidebar">
            <i class="ti ti-menu-2"></i>
        </button>
        
        <div class="search-wrapper">
            <div style="position: relative;">
                <i class="ti ti-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 1rem;"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari barang atau riwayat...">
            </div>
        </div>
        
        <div class="user-menu">
            <button class="notification-btn" id="notificationBtn">
                <i class="ti ti-bell"></i>
                <?php if ($stats['pending'] > 0): ?>
                    <span class="notification-dot"></span>
                <?php endif; ?>
            </button>
            
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                    <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['identity_type'] ?? ($_SESSION['role'] ?? 'Peminjam'))) ?></div>
                </div>
                <div class="user-avatar">
                    <?= getInitials($_SESSION['name']) ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <!-- Welcome Card -->
        <div class="welcome-card fade-in-up" data-aos="fade-up">
            <div class="welcome-badge">
                <i class="ti ti-calendar"></i>
                <?= date('l, d F Y') ?>
            </div>
            <div class="welcome-title">
                Selamat datang, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>! 👋
            </div>
            <div class="welcome-text">
                Kelola peminjaman inventaris Anda dengan mudah dan cepat.
            </div>
            <a href="borrow.php" class="btn-primary-custom">
                <i class="ti ti-plus"></i> Ajukan Peminjaman
            </a>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-header">
                    <div class="stat-icon"><i class="ti ti-chart-bar"></i></div>
                </div>
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Peminjaman</div>
                <div class="stat-trend <?= $stats['trend'] >= 0 ? 'trend-up' : 'trend-down' ?>">
                    <i class="ti ti-<?= $stats['trend'] >= 0 ? 'trending-up' : 'trending-down' ?>"></i>
                    <span><?= abs($stats['trend']) ?>% dari bulan lalu</span>
                </div>
            </div>
            
            <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-header">
                    <div class="stat-icon"><i class="ti ti-clock"></i></div>
                </div>
                <div class="stat-value"><?= number_format($stats['active']) ?></div>
                <div class="stat-label">Peminjaman Aktif</div>
                <div class="stat-trend trend-up">
                    <i class="ti ti-activity"></i>
                    <span>Sedang berlangsung</span>
                </div>
            </div>
            
            <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-header">
                    <div class="stat-icon"><i class="ti ti-check-circle"></i></div>
                </div>
                <div class="stat-value"><?= number_format($stats['completed']) ?></div>
                <div class="stat-label">Peminjaman Selesai</div>
                <div class="stat-trend trend-up">
                    <i class="ti ti-check"></i>
                    <span>Sudah dikembalikan</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div data-aos="fade-up" data-aos-delay="400">
            <div class="section-title">
                <i class="ti ti-bolt" style="color: var(--accent);"></i>
                Aksi Cepat
            </div>
            <div class="actions-grid">
                <a href="borrow.php" class="action-card">
                    <div class="action-icon"><i class="ti ti-plus"></i></div>
                    <div class="action-title">Pinjam Barang</div>
                    <div class="action-desc">Ajukan peminjaman baru</div>
                </a>
                <a href="history.php" class="action-card">
                    <div class="action-icon"><i class="ti ti-history"></i></div>
                    <div class="action-title">Riwayat</div>
                    <div class="action-desc">Lihat riwayat peminjaman</div>
                </a>
                <a href="items.php" class="action-card">
                    <div class="action-icon"><i class="ti ti-search"></i></div>
                    <div class="action-title">Cari Barang</div>
                    <div class="action-desc">Cek ketersediaan barang</div>
                </a>
                <a href="#" class="action-card" id="helpBtn">
                    <div class="action-icon"><i class="ti ti-help"></i></div>
                    <div class="action-title">Panduan</div>
                    <div class="action-desc">Petunjuk penggunaan</div>
                </a>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card" data-aos="fade-right" data-aos-delay="500">
                <div class="chart-header">
                    <div>
                        <div class="chart-title">Statistik Peminjaman</div>
                        <small class="text-muted">6 bulan terakhir</small>
                    </div>
                    <i class="ti ti-chart-line" style="color: var(--primary); font-size: 1.25rem;"></i>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card" data-aos="fade-left" data-aos-delay="600">
                <div class="chart-header">
                    <div>
                        <div class="chart-title">Ringkasan Status</div>
                        <small class="text-muted">Distribusi peminjaman</small>
                    </div>
                    <i class="ti ti-pie-chart" style="color: var(--primary); font-size: 1.25rem;"></i>
                </div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="activity-card" data-aos="fade-up" data-aos-delay="700">
            <div class="activity-header">
                <h5><i class="ti ti-activity" style="color: var(--accent);"></i> Aktivitas Terbaru</h5>
                <a href="history.php" class="view-all">Lihat Semua <i class="ti ti-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr><th>Kode</th><th>Nama Barang</th><th>Tanggal Pinjam</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLoans)): ?>
                            <tr><td colspan="5" class="empty-state"><i class="ti ti-inbox"></i><p class="text-muted">Belum ada aktivitas peminjaman</p><a href="borrow.php" class="btn-primary-custom">Mulai Pinjam</a></td></tr>
                        <?php else: ?>
                            <?php foreach ($recentLoans as $loan): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($loan['item_code']) ?></td>
                                    <td><?= htmlspecialchars($loan['item_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($loan['created_at'])) ?></td>
                                    <td><?= getStatusBadge($loan['status']) ?></td>
                                    <td><a href="detail.php?id=<?= $loan['id'] ?>" class="btn-outline-custom"><i class="ti ti-eye"></i> Detail</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// Initialize AOS
AOS.init({ duration: 600, once: true, offset: 50 });

// Sidebar Toggle
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const toggleBtn = document.getElementById('toggleSidebar');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
});

if (localStorage.getItem('sidebarCollapsed') === 'true') {
    sidebar.classList.add('collapsed');
    mainContent.classList.add('expanded');
}

// Mobile sidebar
if (window.innerWidth <= 1024) {
    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('mobile-open');
    });
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('mobile-open');
        }
    });
}

// Monthly Chart
const monthlyData = <?= json_encode($monthlyStats) ?>;
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthlyData.map(m => m.month),
        datasets: [{
            label: 'Peminjaman',
            data: monthlyData.map(m => m.count),
            borderColor: '#0F3B3C',
            backgroundColor: 'rgba(15, 59, 60, 0.05)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#0F3B3C',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1E293B', padding: 10, cornerRadius: 8 } },
        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
    }
});

// Status Chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Disetujui', 'Menunggu', 'Lainnya'],
        datasets: [{
            data: [<?= $stats['completed'] ?>, <?= $stats['pending'] ?>, <?= $stats['active'] - $stats['pending'] ?>],
            backgroundColor: ['#10B981', '#F59E0B', '#3B82F6'],
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } } },
        cutout: '65%'
    }
});

// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.table-custom tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});

// Notification button
document.getElementById('notificationBtn')?.addEventListener('click', () => window.location.href = 'history.php?filter=pending');

// Help button
document.getElementById('helpBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    alert('📖 Panduan SIPINTAR-TI\n\n1. Pinjam Barang → Klik menu "Pinjam Barang"\n2. Isi formulir peminjaman\n3. Tunggu persetujuan admin\n4. Ambil barang setelah disetujui\n5. Kembalikan barang tepat waktu\n\n💬 Butuh bantuan? Hubungi admin.');
});

// Animate stats
document.querySelectorAll('.stat-value').forEach(stat => {
    const final = parseInt(stat.textContent.replace(/\./g, ''));
    if (!isNaN(final)) {
        let current = 0;
        const increment = final / 40;
        const timer = setInterval(() => {
            current += increment;
            if (current >= final) {
                stat.textContent = final.toLocaleString('id-ID');
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current).toLocaleString('id-ID');
            }
        }, 20);
    }
});
</script>

</body>
</html>