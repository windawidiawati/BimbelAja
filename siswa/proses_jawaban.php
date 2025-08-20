<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$latihan_id = isset($_POST['latihan_id']) ? (int)$_POST['latihan_id'] : 0;
$soal_ids = $_POST['soal_ids'] ?? [];
$jawaban_array = $_POST['jawaban'] ?? [];

if ($latihan_id <= 0 || empty($soal_ids) || empty($jawaban_array)) {
    die("Data tidak lengkap. Pastikan soal dan latihan_id dikirim");
}

// Cek apakah siswa sudah pernah menjawab latihan ini
$cek = mysqli_query($conn, "SELECT * FROM jawaban_siswa WHERE user_id=$user_id AND latihan_id=$latihan_id");
if (mysqli_num_rows($cek) > 0) {
    die("Latihan ini sudah pernah kamu kerjakan.");
}
$selesai = date('Y-m-d H:i:s');
mysqli_query($conn, "UPDATE latihan_siswa SET waktu_selesai = NOW() WHERE siswa_id = $user_id AND latihan_id = $latihan_id");

// Simpan jawaban siswa
foreach ($soal_ids as $soal_id) {
    $soal_id = (int)$soal_id;
    $jawaban = $jawaban_array[$soal_id] ?? null;

    if (!$jawaban) continue;

    $q = mysqli_query($conn, "SELECT jawaban FROM soal WHERE id=$soal_id");
$data = mysqli_fetch_assoc($q);
$benar = ($jawaban === $data['jawaban']) ? 1 : 0;


    mysqli_query($conn, "INSERT INTO jawaban_siswa (user_id, soal_id, latihan_id, jawaban, benar)
                         VALUES ($user_id, $soal_id, $latihan_id, '$jawaban', $benar)");
}
// setelah selesai simpan jawaban:
header("Location: soal.php?selesai=1&latihan_id=$latihan_id");
exit;
