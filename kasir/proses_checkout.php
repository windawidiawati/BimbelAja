<?php
session_start();
require_once '../config/database.php';

// Batasi akses hanya untuk kasir
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'kasir') {
  header("Location: ../index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'] ?? '';
  $paket_id = $_POST['paket_id'] ?? '';

  // Validasi input
  if (empty($user_id) || empty($paket_id)) {
    die('Data siswa dan paket harus dipilih.');
  }

  // Ambil detail user (untuk validasi lanjutan jika perlu)
  $user_result = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id' AND role = 'siswa'");
  if (mysqli_num_rows($user_result) === 0) {
    die("Data siswa tidak valid.");
  }

  // Ambil detail paket
  $paket_result = mysqli_query($conn, "SELECT * FROM paket WHERE id = '$paket_id'");
  if (mysqli_num_rows($paket_result) === 0) {
    die("Paket tidak ditemukan.");
  }

  $paket = mysqli_fetch_assoc($paket_result);
  $harga = $paket['harga'];
  $nama_paket = $paket['nama_paket'];
  $durasi = $paket['durasi'];
  $satuan = $paket['satuan'];
  $warna = $paket['warna'];
  $kode_unik = uniqid(); // Ganti kode bayar dengan kode unik internal

  // Hitung tanggal aktif_hingga
  $aktif_hingga = date('Y-m-d', strtotime("+$durasi $satuan"));

  // Simpan ke tabel pembayaran
  $query = "INSERT INTO pembayaran (user_id, paket_id, kode_unik, harga, metode, status, tanggal, aktif_hingga)
            VALUES ('$user_id', '$paket_id', '$kode_unik', '$harga', 'tunai', 'lunas', NOW(), '$aktif_hingga')";

  if (mysqli_query($conn, $query)) {
    // Redirect ke halaman transaksi dengan notifikasi sukses
    header("Location: transaksi.php?berhasil=1");
    exit;
  } else {
    echo "Gagal menyimpan transaksi: " . mysqli_error($conn);
  }
} else {
  // Jika bukan metode POST
  header("Location: checkout_tunai.php");
  exit;
}
?>
