<?php
ob_start(); // penting agar header() bisa jalan
session_start();
include '../config/database.php';

function redirectWith($key, $val) {
  header("Location: kelola_materi.php?$key=$val");
  exit;
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  redirectWith('error', 'unauthorized');
}

// Setujui Materi
if (isset($_POST['setujui'])) {
  $id = (int)$_POST['id'];
  $query = "UPDATE materi SET status = 'diterima' WHERE id = $id";
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

// Tambah Materi oleh Admin
if (isset($_POST['tambah_admin'])) {
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori_id = (int)$_POST['kategori_id'];
  $kelas_id = (int)$_POST['kelas_id'];
  $created_at = date('Y-m-d H:i:s');

  $allowed = ['pdf', 'mp4', 'mkv', 'avi', 'mov'];
  $file = $_FILES['file'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $size = $file['size'];

  if (!in_array($ext, $allowed)) redirectWith('error', 'file');
  if ($size > 10 * 1024 * 1024) redirectWith('error', 'ukuran');

  $filename = uniqid() . '.' . $ext;
  $path = '../assets/uploads/' . $filename;
  $tipe = $ext === 'pdf' ? 'pdf' : 'video';

  if (move_uploaded_file($file['tmp_name'], $path)) {
    $query = "INSERT INTO materi (judul, deskripsi, kategori_id, kelas_id, file, tipe_file, status, created_at)
              VALUES ('$judul', '$deskripsi', $kategori_id, $kelas_id, '$filename', '$tipe', 'diterima', '$created_at')";
    if (mysqli_query($conn, $query)) redirectWith('success', 'tambah');
    else redirectWith('error', 'tambah');
  } else {
    redirectWith('error', 'tambah');
  }
}

// Edit Materi oleh Admin
if (isset($_POST['edit_admin'])) {
  $id = (int)$_POST['id'];
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori_id = (int)$_POST['kategori_id'];
  $kelas_id = (int)$_POST['kelas_id'];

  // Ambil file lama
  $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file FROM materi WHERE id = $id"));
  $old_file = $old['file'];
  $new_file = $old_file;
  $tipe_file = pathinfo($old_file, PATHINFO_EXTENSION) === 'pdf' ? 'pdf' : 'video';

  // Cek apakah file baru diunggah
  if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $size = $file['size'];
    $allowed = ['pdf', 'mp4', 'mkv', 'avi', 'mov'];

    if (!in_array($ext, $allowed)) redirectWith('error', 'file');
    if ($size > 10 * 1024 * 1024) redirectWith('error', 'ukuran');

    $new_file = uniqid() . '.' . $ext;
    $path = '../assets/uploads/' . $new_file;
    $tipe_file = $ext === 'pdf' ? 'pdf' : 'video';

    if (move_uploaded_file($file['tmp_name'], $path)) {
      $old_path = '../assets/uploads/' . $old_file;
      if (file_exists($old_path)) unlink($old_path); // hapus file lama
    } else {
      redirectWith('error', 'edit');
    }
  }

  // Update ke database
  $update = "UPDATE materi 
             SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id, file='$new_file', tipe_file='$tipe_file', status='diterima'
             WHERE id = $id";

  if (mysqli_query($conn, $update)) {
    redirectWith('success', 'edit');
  } else {
    redirectWith('error', 'edit');
  }
}

redirectWith('error', 'unknown');
