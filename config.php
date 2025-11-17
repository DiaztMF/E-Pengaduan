<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'e_pengaduan');

// Koneksi Database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Fungsi untuk memulai session jika belum
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Fungsi untuk cek login admin
function isAdminLoggedIn() {
    startSession();
    return isset($_SESSION['admin_id']);
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk sanitasi input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulanAngka = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return $hari . ' ' . $bulan[$bulanAngka] . ' ' . $tahun . ' - ' . $jam;
}

// Fungsi untuk badge status
function getBadgeStatus($status) {
    $badges = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'diproses' => 'bg-blue-100 text-blue-800',
        'selesai' => 'bg-green-100 text-green-800',
        'ditolak' => 'bg-red-100 text-red-800'
    ];
    
    return $badges[$status] ?? 'bg-gray-100 text-gray-800';
}
?>