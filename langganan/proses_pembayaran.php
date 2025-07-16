<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php"); exit;
}

include_once __DIR__ . '/../config/database.php';

$user_id = $_POST['user_id'] ?? null;
$paket_id = $_POST['paket_id'] ?? null;
$metode = $_POST['metode'] ?? null;

if (!$paket_id || !$user_id || !$metode) {
    echo "Data tidak lengkap.";
    exit;
}

// Ambil data paket
$query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "Paket tidak ditemukan atau tidak aktif."; exit;
}

$paket = mysqli_fetch_assoc($query);
$nama_paket = $paket['nama'];
$harga = $paket['harga'];
$tanggal = date('Y-m-d H:i:s');
$status = 'menunggu_verifikasi'; // Atau 'pending', tergantung alurmu

// Simpan ke tabel pembayaran
$stmt = $conn->prepare("INSERT INTO pembayaran (user_id, paket, harga, metode, status, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isisss", $user_id, $nama_paket, $harga, $metode, $status, $tanggal);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: /BimbelAja/langganan/riwayat.php");
    exit;
} else {
    echo "Gagal menyimpan pembayaran.";
}
