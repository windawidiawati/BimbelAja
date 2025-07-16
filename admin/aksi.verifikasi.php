<?php
include '../config/database.php'; // pastikan path benar

if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    header('Location: verifikasi_pembayaran.php');
    exit();
}

$id = intval($_GET['id']);
$status = $_GET['aksi'];

$query = "UPDATE pembayaran SET status=? WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param('si', $status, $id);
$stmt->execute();

// Jika disetujui, otomatis buat langganan
if ($status === 'lunas') {
    $get = $conn->query("SELECT * FROM pembayaran WHERE id=$id")->fetch_assoc();
    if ($get) {
        $user = intval($get['user_id']);
        $paket = $conn->real_escape_string($get['paket']);
        $today = date('Y-m-d');
        $akhir = date('Y-m-d', strtotime('+30 days')); // Langganan aktif 30 hari

        $insert = "INSERT INTO langganan (user_id, paket, tanggal_mulai, tanggal_berakhir)
                   VALUES (?, ?, ?, ?)";
        $stmt2 = $conn->prepare($insert);
        $stmt2->bind_param('isss', $user, $paket, $today, $akhir);
        $stmt2->execute();
    }
}

header('Location: verifikasi_pembayaran.php');
exit();