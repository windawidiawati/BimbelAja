<?php
session_start();
require_once '../config/database.php';

// Batasi akses hanya kasir
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $paket_id = $_POST['paket_id'] ?? '';

    // Validasi input
    if (empty($user_id) || empty($paket_id)) {
        header("Location: checkout_tunai.php?error=Data siswa dan paket harus dipilih.");
        exit;
    }

    // Ambil detail user
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'siswa'");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows === 0) {
        header("Location: checkout_tunai.php?error=Data siswa tidak valid.");
        exit;
    }

    // Ambil detail paket
    $paket_stmt = $conn->prepare("SELECT nama_paket, harga, durasi, satuan FROM paket WHERE id = ?");
    $paket_stmt->bind_param("i", $paket_id);
    $paket_stmt->execute();
    $paket_result = $paket_stmt->get_result();
    if ($paket_result->num_rows === 0) {
        header("Location: checkout_tunai.php?error=Paket tidak ditemukan.");
        exit;
    }
    $paket = $paket_result->fetch_assoc();

    $harga = $paket['harga'];
    $nama_paket = $paket['nama_paket'];
    $durasi = $paket['durasi'];
    $satuan = $paket['satuan'];
    $kode_unik = "TUNAI" . strtoupper(uniqid());

    // Hitung tanggal aktif_hingga
    $aktif_hingga = date('Y-m-d', strtotime("+$durasi $satuan"));

    // Simpan ke tabel pembayaran
    $insert_stmt = $conn->prepare("
        INSERT INTO pembayaran (user_id, paket_id, kode_unik, harga, metode, status, tanggal, aktif_hingga) 
        VALUES (?, ?, ?, ?, 'tunai', 'lunas', NOW(), ?)
    ");
    $insert_stmt->bind_param("iisds", $user_id, $paket_id, $kode_unik, $harga, $aktif_hingga);

    if ($insert_stmt->execute()) {
        header("Location: transaksi.php?berhasil=1");
        exit;
    } else {
        header("Location: checkout_tunai.php?error=Gagal menyimpan transaksi.");
        exit;
    }
} else {
    header("Location: checkout_tunai.php");
    exit;
}
?>
