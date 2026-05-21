<?php
$TRANSLATIONS = [
    // General
    "Dashboard" => "Dasbor",
    "Welcome back! Here's your overview." => "Selamat datang kembali! Berikut ringkasan Anda.",

    // Stats
    "Total Users" => "Total Pengguna",
    "Active Members" => "Anggota Aktif",
    "Total Items" => "Total Barang",
    "In Inventory" => "Di Inventaris",
    "Total Borrows" => "Total Peminjaman",
    "This Month" => "Bulan Ini",
    "Pending Requests" => "Permintaan Menunggu",
    "Awaiting Action" => "Menunggu Tindakan",

    // Tables / Lists
    "Recent Borrow Requests" => "Permintaan Peminjaman Terbaru",
    "ID" => "ID",
    "User" => "Pengguna",
    "Item" => "Barang",
    "Request Date" => "Tanggal Permintaan",
    "Status" => "Status",
    "Action" => "Aksi",
    "View" => "Lihat",
    "No borrow requests found." => "Tidak ada permintaan peminjaman.",

    // Borrow management
    "Borrow Requests Management" => "Manajemen Permintaan Pinjam",
    "Manage and approve borrow requests from users" => "Kelola dan setujui permintaan peminjaman dari pengguna",
    "Borrow Requests List" => "Daftar Permintaan Pinjam",
    "Approve" => "Setujui",
    "Reject" => "Tolak",
    "Returned" => "Dikembalikan",
    "Mark as Returned" => "Tandai Sebagai Dikembalikan",
    "No Action" => "Tidak Ada Aksi",

    // Status keys (lowercase)
    "pending" => "Menunggu",
    "approved" => "Disetujui",
    "rejected" => "Ditolak",
    "returned" => "Dikembalikan",
];

function __($key)
{
    global $TRANSLATIONS;
    if (is_string($key)) {
        // try exact match first
        if (array_key_exists($key, $TRANSLATIONS)) return $TRANSLATIONS[$key];
        // try lowercase
        $lk = strtolower($key);
        if (array_key_exists($lk, $TRANSLATIONS)) return $TRANSLATIONS[$lk];
    }
    return $key;
}

?>
