<?php
include_once __DIR__ . '/../config/app.php';
$current = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$userRole = $_SESSION['role'] ?? 'guest';
function active_menu(string $needle, string $current): string
{
    return strpos($current, $needle) !== false ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-head">
        <div class="sidebar-logo"><i class="fas fa-boxes-stacked"></i></div>
        <div>
            <strong>SIPINTAR-TI</strong>
            <small>Inventaris Jurusan</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($userRole === 'admin') : ?>
            <p class="nav-label">Menu Utama</p>
            <a class="<?= active_menu('admin/dashboard', $current); ?>" href="<?= e(base_url('admin/dashboard.php')); ?>"><i class="fas fa-table-cells-large"></i><span>Dashboard</span></a>
            <a class="<?= active_menu('admin/borrow', $current); ?>" href="<?= e(base_url('admin/borrow/index.php')); ?>"><i class="fas fa-clipboard-list"></i><span>Permintaan</span></a>
            <a class="<?= active_menu('admin/items', $current); ?>" href="<?= e(base_url('admin/items/index.php')); ?>"><i class="fas fa-box"></i><span>Barang</span></a>
            <a class="<?= active_menu('admin/categories', $current); ?>" href="<?= e(base_url('admin/categories/index.php')); ?>"><i class="fas fa-layer-group"></i><span>Kategori</span></a>
            <p class="nav-label">Lainnya</p>
            <a class="<?= active_menu('admin/logs', $current); ?>" href="<?= e(base_url('admin/logs/index.php')); ?>"><i class="fas fa-clock-rotate-left"></i><span>Aktivitas</span></a>
        <?php elseif ($userRole === 'peminjam') : ?>
            <p class="nav-label">Menu Utama</p>
            <a class="<?= active_menu('peminjam/dashboard', $current); ?>" href="<?= e(base_url('peminjam/dashboard.php')); ?>"><i class="fas fa-table-cells-large"></i><span>Dashboard</span></a>
            <a class="<?= active_menu('peminjam/items', $current); ?>" href="<?= e(base_url('peminjam/items.php')); ?>"><i class="fas fa-boxes-stacked"></i><span>Katalog Barang</span></a>
            <a class="<?= active_menu('peminjam/borrow', $current); ?>" href="<?= e(base_url('peminjam/borrow.php')); ?>"><i class="fas fa-plus-circle"></i><span>Ajukan Pinjaman</span></a>
            <a class="<?= active_menu('peminjam/history', $current); ?>" href="<?= e(base_url('peminjam/history.php')); ?>"><i class="fas fa-clock-rotate-left"></i><span>Riwayat</span></a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-foot">
        <a href="<?= e(base_url('auth/change_password.php')); ?>"><i class="fas fa-key"></i><span>Ubah Password</span></a>
        <a href="<?= e(base_url('auth/logout.php')); ?>"><i class="fas fa-right-from-bracket"></i><span>Keluar</span></a>
    </div>
</aside>
