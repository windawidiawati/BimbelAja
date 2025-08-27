<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    exit("Tidak diizinkan");
}

$siswa_id = $_SESSION['user']['id'];
$latihan_id = isset($_GET['latihan_id']) ? (int)$_GET['latihan_id'] : 0;

if ($latihan_id <= 0) {
    exit("Latihan tidak valid.");
}

// Ambil soal, jawaban benar & jawaban siswa
$sql = "SELECT s.id, s.pertanyaan, s.opsi_a, s.opsi_b, s.opsi_c, s.opsi_d, 
               s.jawaban AS jawaban_benar, 
               j.jawaban AS jawaban_siswa
        FROM soal s
        LEFT JOIN jawaban_siswa j 
            ON s.id = j.soal_id AND j.user_id = ? AND j.latihan_id = ?
        WHERE s.latihan_id = ?
        ORDER BY s.id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $siswa_id, $latihan_id, $latihan_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "<div class='alert alert-info'>Tidak ada data jawaban.</div>";
    exit;
}

while ($row = $res->fetch_assoc()) {
    $jawaban_siswa = $row['jawaban_siswa'];
    $jawaban_benar = $row['jawaban_benar'];

    echo "<div class='mb-3 p-2 border rounded'>";
    echo "<p><strong>{$row['pertanyaan']}</strong></p>";

    foreach (['A','B','C','D'] as $opsi) {
        $pilihan = $row['opsi_'.strtolower($opsi)];
        $status = "";

        if ($jawaban_siswa == $opsi && $jawaban_benar == $opsi) {
            $status = "<span class='text-success fw-bold'>✅ (Jawaban Kamu & Benar)</span>";
        } elseif ($jawaban_siswa == $opsi && $jawaban_benar != $opsi) {
            $status = "<span class='text-danger fw-bold'>❌ (Jawaban Kamu)</span>";
        } elseif ($jawaban_benar == $opsi) {
            $status = "<span class='text-success'>✔️ (Jawaban Benar)</span>";
        }

        echo "<div>{$opsi}. {$pilihan} $status</div>";
    }

    echo "</div>";
}
