<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $langganan_id = $_POST['langganan_id'];
    $paket_id_baru = $_POST['paket_id'];
    $tanggal_mulai_baru = $_POST['tanggal_mulai'];
    $tanggal_selesai_baru = $_POST['tanggal_selesai'];

    $query = "UPDATE langganan SET 
                paket_id = ?, 
                tanggal_mulai = ?, 
                tanggal_selesai = ?
              WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssi", $paket_id_baru, $tanggal_mulai_baru, $tanggal_selesai_baru, $langganan_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: data_siswa.php?status=sukses");
        exit;
    } else {
        echo "Gagal memperpanjang paket.";
    }
} else {
    echo "Akses tidak sah.";
}
?>
