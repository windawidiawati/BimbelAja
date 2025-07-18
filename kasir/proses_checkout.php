<?php
include '../config/database.php';
include '../includes/auth.php';

// Pastikan hanya kasir yang bisa mengakses
if ($_SESSION['user']['role'] !== 'kasir') {
  header("Location: ../index.php");
  exit;
}

// Cek jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'] ?? '';
  $paket_id = $_POST['paket_id'] ?? '';

  // Validasi input
  if (empty($user_id) || empty($paket_id)) {
    die('Data siswa dan paket harus dipilih.');
  }

  // Ambil data paket
  $paket_query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id");
  $paket = mysqli_fetch_assoc($paket_query);

  if (!$paket) {
    die('Paket tidak ditemukan.');
  }

  // Data transaksi
  $nama_paket = $paket['nama'];
  $harga = $paket['harga'];
  $durasi = $paket['durasi'];
  $satuan = $paket['satuan_durasi'];

  // Hitung tanggal aktif hingga
  $aktif_hingga = date('Y-m-d', strtotime("+$durasi $satuan"));

  // Insert ke tabel pembayaran
  $query = "INSERT INTO pembayaran (user_id, paket, harga, metode, status, tanggal, aktif_hingga)
            VALUES ('$user_id', '$nama_paket', '$harga', 'tunai', 'lunas', NOW(), '$aktif_hingga')";

  if (mysqli_query($conn, $query)) {
    header("Location: transaksi.php?berhasil=1");
    exit;
  } else {
    echo "Gagal menyimpan transaksi: " . mysqli_error($conn);
  }
} else {
  header("Location: checkout_tunai.php");
  exit;
}
?>
