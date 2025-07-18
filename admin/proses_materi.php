<?php
session_start();
include '../config/database.php';

function redirectWith($key, $val) {
  header("Location: kelola_materi.php?$key=$val");
  exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
  redirectWith('error', 'unauthorized');
}

// Tambah Materi oleh Admin
if (isset($_POST['tambah_admin'])) {
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori_id = (int)$_POST['kategori_id'];
  $kelas_id = (int)$_POST['kelas_id'];
  $created_at = date('Y-m-d H:i:s');

  // File upload
  $allowed = ['pdf', 'mp4', 'mkv', 'avi', 'mov'];
  $file = $_FILES['file'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $size = $file['size'];

  if (!in_array($ext, $allowed)) redirectWith('error', 'file');
  if ($size > 10 * 1024 * 1024) redirectWith('error', 'ukuran'); // max 10MB

  $filename = uniqid() . '.' . $ext;
  $path = '../assets/uploads/' . $filename;

  $tipe = in_array($ext, ['pdf']) ? 'pdf' : 'video';

  if (move_uploaded_file($file['tmp_name'], $path)) {
    $query = "INSERT INTO materi (judul, deskripsi, kategori_id, kelas_id, file, tipe_file, status, created_at)
              VALUES ('$judul', '$deskripsi', $kategori_id, $kelas_id, '$filename', '$tipe', 'disetujui', '$created_at')";
    if (mysqli_query($conn, $query)) redirectWith('success', 'tambah');
    else redirectWith('error', 'tambah');
  } else {
    redirectWith('error', 'tambah');
  }
}

// Setujui Materi
if (isset($_POST['setujui'])) {
  $id = (int)$_POST['id'];
  $query = "UPDATE materi SET status = 'disetujui' WHERE id = $id";
  if (mysqli_query($conn, $query)) {
    redirectWith('success', 'setujui');
  } else {
    redirectWith('error', 'setujui');
  }
}

// Tolak Materi
if (isset($_POST['tolak'])) {
  $id = (int)$_POST['id'];
  $query = "UPDATE materi SET status = 'ditolak' WHERE id = $id";
  if (mysqli_query($conn, $query)) {
    redirectWith('success', 'tolak');
  } else {
    redirectWith('error', 'tolak');
  }
}

// Hapus Materi
if (isset($_POST['hapus'])) {
  $id = (int)$_POST['id'];
  $get = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file FROM materi WHERE id = $id"));
  $file = $get['file'];
  $lokasi = "../assets/uploads/$file";

  if (mysqli_query($conn, "DELETE FROM materi WHERE id = $id")) {
    if (file_exists($lokasi)) unlink($lokasi);
    redirectWith('success', 'hapus');
  } else {
    redirectWith('error', 'hapus');
  }
}

redirectWith('error', 'unknown');
