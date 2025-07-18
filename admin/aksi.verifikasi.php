<?php
include '../config/database.php';

if (isset($_GET['id']) && isset($_GET['aksi'])) {
  $id = intval($_GET['id']);
  $aksi = $_GET['aksi'];

  if ($aksi == 'lunas' || $aksi == 'ditolak') {
    $query = "UPDATE pembayaran SET status='$aksi' WHERE id=$id";
    if ($conn->query($query) === TRUE) {
      header("Location: verifikasi_pembayaran.php?status=sukses");
    } else {
      echo "Gagal mengubah status: " . $conn->error;
    }
  } else {
    echo "Aksi tidak valid.";
  }
} else {
  echo "Data tidak lengkap.";
}
?>
