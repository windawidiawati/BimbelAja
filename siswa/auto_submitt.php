<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$user_id = $_SESSION['user']['id'];
$data = json_decode(file_get_contents("php://input"), true);

$latihan_id = (int)($data['latihan_id'] ?? 0);
$jawaban_array = $data['jawaban'] ?? [];

if ($latihan_id <= 0 || empty($jawaban_array)) {
    http_response_code(400);
    echo "Data tidak lengkap";
    exit;
}

// Cek apakah siswa sudah pernah mengerjakan
$cek = mysqli_query($conn, "SELECT * FROM jawaban_siswa WHERE user_id=$user_id AND latihan_id=$latihan_id");
if (mysqli_num_rows($cek) > 0) {
    echo "Sudah dikerjakan sebelumnya";
    exit;
}

mysqli_query($conn, "UPDATE latihan_siswa SET waktu_selesai = NOW() WHERE siswa_id = $user_id AND latihan_id = $latihan_id");

foreach ($jawaban_array as $soal_id => $jawaban) {
    $soal_id = (int)$soal_id;
    $jawaban = mysqli_real_escape_string($conn, $jawaban);
    
    $q = mysqli_query($conn, "SELECT jawaban FROM soal WHERE id=$soal_id");
    $data = mysqli_fetch_assoc($q);
    $benar = ($jawaban === $data['jawaban']) ? 1 : 0;

    mysqli_query($conn, "INSERT INTO jawaban_siswa (user_id, soal_id, latihan_id, jawaban, benar)
                         VALUES ($user_id, $soal_id, $latihan_id, '$jawaban', $benar)");
}

echo "success";
