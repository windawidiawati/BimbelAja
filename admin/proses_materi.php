<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

// Fungsi redirect dengan notifikasi
function redirectWith($key, $val) {
  header("Location: kelola_materi.php?$key=$val");
  exit;
}

// === TAMBAH MATERI ===
if (isset($_POST['tambah'])) {
  $judul    = mysqli_real_escape_string($conn, $_POST['judul']);
  $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
  $tutor    = mysqli_real_escape_string($conn, $_POST['tutor']);
  $tanggal  = date('Y-m-d');

  // Validasi file
  $allowed = ['pdf', 'mp4', 'zip'];
  $file    = $_FILES['file'];
  $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $ukuran  = $file['size'];

  if (!in_array($ext, $allowed)) {
    redirectWith('error', 'file');
  }

  if ($ukuran > 20 * 1024 * 1024) { // Maks 20MB
    redirectWith('error', 'ukuran');
  }

  $namaFile = uniqid() . '.' . $ext;
  $lokasi   = '../uploads/' . $namaFile;

  if (move_uploaded_file($file['tmp_name'], $lokasi)) {
    $query = "INSERT INTO materi (judul, kategori, tutor, file, tanggal_upload, status) 
              VALUES ('$judul', '$kategori', '$tutor', '$namaFile', '$tanggal', 'menunggu')";
    if (mysqli_query($conn, $query)) {
      redirectWith('success', 'tambah');
    } else {
      unlink($lokasi); // Hapus file kalau gagal insert
      redirectWith('error', 'tambah');
    }
  } else {
    redirectWith('error', 'tambah');
  }
}

if (isset($_POST['disetujui'])) {
  $id = (int)$_POST['id'];
  $query = "UPDATE materi SET status = 'disetujui' WHERE id = $id";
  if (mysqli_query($conn, $query)) {
    redirectWith('success', 'disetujui');
  } else {
    redirectWith('error', 'disetujui');
  }
}

// === TOLAK MATERI ===
if (isset($_POST['tolak'])) {
  $id = (int)$_POST['id'];
  $query = "UPDATE materi SET status = 'ditolak' WHERE id = $id";
  if (mysqli_query($conn, $query)) {
    redirectWith('success', 'tolak');
  } else {
    redirectWith('error', 'tolak');
  }
}

// === HAPUS MATERI ===
if (isset($_POST['hapus'])) {
  $id = (int)$_POST['id'];
  $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file FROM materi WHERE id = $id"));
  $file = $data['file'];
  $lokasi = '../uploads/' . $file;

  if (mysqli_query($conn, "DELETE FROM materi WHERE id = $id")) {
    if (file_exists($lokasi)) {
      unlink($lokasi);
    }
    redirectWith('success', 'hapus');
  } else {
    redirectWith('error', 'hapus');
  }
}

//redirectWith('error', 'unknown');
