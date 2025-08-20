<?php
ob_start();
session_start();
include '../config/database.php';

function redirectWith($key, $val) {
  header("Location: kelola_materi.php?$key=$val");
  exit;
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  redirectWith('error', 'unauthorized');
}

// -------------------- SETUJUI MATERI --------------------
if (isset($_POST['setujui'])) {
  $id = (int)$_POST['id'];
  if (mysqli_query($conn, "UPDATE materi SET status = 'diterima' WHERE id = $id")) {
    redirectWith('success', 'setujui');
  } else {
    redirectWith('error', 'setujui');
  }
}

// -------------------- TOLAK MATERI --------------------
if (isset($_POST['tolak'])) {
  $id = (int)$_POST['id'];
  if (mysqli_query($conn, "UPDATE materi SET status = 'ditolak' WHERE id = $id")) {
    redirectWith('success', 'tolak');
  } else {
    redirectWith('error', 'tolak');
  }
}

// -------------------- HAPUS MATERI --------------------
if (isset($_POST['hapus'])) {
  $id = (int)$_POST['id'];
  $get = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file FROM materi WHERE id = $id"));
  $file = $get['file'] ?? '';
  $lokasi = "../assets/uploads/$file";

  if (mysqli_query($conn, "DELETE FROM materi WHERE id = $id")) {
    if ($file && file_exists($lokasi)) unlink($lokasi);
    redirectWith('success', 'hapus');
  } else {
    redirectWith('error', 'hapus');
  }
}

// -------------------- TAMBAH MATERI --------------------
if (isset($_POST['tambah_admin'])) {
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori_id = (int)$_POST['kategori_id'];
  $kelas_id = (int)$_POST['kelas_id'];
  $created_at = date('Y-m-d H:i:s');

  // Ambil paket dari form
  $paket_id = (int)($_POST['paket_id'] ?? 0);
  $paket_baru = trim($_POST['paket_baru'] ?? '');

  // Jika pilih tambah paket baru
  if ($paket_id === 0 && $paket_baru !== '') {
    $paket_baru = mysqli_real_escape_string($conn, $paket_baru);
    $rowP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(id) AS max_id FROM paket"));
    $newPaketId = ($rowP['max_id'] ?? 0) + 1;
    if (mysqli_query($conn, "INSERT INTO paket (id, nama) VALUES ($newPaketId, '$paket_baru')")) {
      $paket_id = $newPaketId;
    } else {
      redirectWith('error', 'tambah');
    }
  }

  // Validasi paket_id
  if ($paket_id <= 0) {
    redirectWith('error', 'tambah');
  }

  // Upload file
  $allowed = ['pdf', 'mp4', 'mkv', 'avi', 'mov'];
  $file = $_FILES['file'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $size = $file['size'];

  if (!in_array($ext, $allowed)) redirectWith('error', 'file');
  if ($size > 10 * 1024 * 1024) redirectWith('error', 'ukuran');

  $filename = uniqid() . '.' . $ext;
  $path = '../assets/uploads/' . $filename;
  $tipe = ($ext === 'pdf') ? 'pdf' : 'video';

  if (move_uploaded_file($file['tmp_name'], $path)) {
    // Ambil ID baru materi secara manual
    $rowM = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(id) AS max_id FROM materi"));
    $newMateriId = ($rowM['max_id'] ?? 0) + 1;

    $query = "INSERT INTO materi (id, judul, deskripsi, kategori_id, kelas_id, paket_id, file, tipe_file, status, created_at)
              VALUES ($newMateriId, '$judul', '$deskripsi', $kategori_id, $kelas_id, $paket_id, '$filename', '$tipe', 'menunggu', '$created_at')";
    if (mysqli_query($conn, $query)) {
      redirectWith('success', 'tambah');
    } else {
      redirectWith('error', 'tambah');
    }
  } else {
    redirectWith('error', 'tambah');
  }
}

// -------------------- EDIT MATERI --------------------
if (isset($_POST['edit_admin'])) {
  $id = (int)$_POST['id'];
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori_id = (int)$_POST['kategori_id'];
  $kelas_id = (int)$_POST['kelas_id'];
  $paket_id = (int)$_POST['paket_id'];

  // Data lama
  $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file FROM materi WHERE id = $id"));
  $old_file = $old['file'] ?? '';
  $new_file = $old_file;
  $tipe_file = (pathinfo($old_file, PATHINFO_EXTENSION) === 'pdf') ? 'pdf' : 'video';

  // Jika file baru diunggah
  if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $size = $file['size'];
    $allowed = ['pdf', 'mp4', 'mkv', 'avi', 'mov'];

    if (!in_array($ext, $allowed)) redirectWith('error', 'file');
    if ($size > 10 * 1024 * 1024) redirectWith('error', 'ukuran');

    $new_file = uniqid() . '.' . $ext;
    $path = '../assets/uploads/' . $new_file;
    $tipe_file = ($ext === 'pdf') ? 'pdf' : 'video';

    if (move_uploaded_file($file['tmp_name'], $path)) {
      $old_path = '../assets/uploads/' . $old_file;
      if ($old_file && file_exists($old_path)) unlink($old_path);
    } else {
      redirectWith('error', 'edit');
    }
  }

  $status = isset($_POST['status']) ? trim($_POST['status']) : 'menunggu';
$allowedStatus = ['menunggu', 'diproses', 'disetujui', 'ditolak'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'menunggu';
}
$status = mysqli_real_escape_string($conn, $status);

$update = "UPDATE materi 
           SET judul='$judul', deskripsi='$deskripsi', kategori_id=$kategori_id, kelas_id=$kelas_id, paket_id=$paket_id, file='$new_file', tipe_file='$tipe_file', status='$status'
           WHERE id = $id";


  if (mysqli_query($conn, $update)) {
    redirectWith('success', 'edit');
  } else {
    redirectWith('error', 'edit');
  }
}

redirectWith('error', 'unknown');
