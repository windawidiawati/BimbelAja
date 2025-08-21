<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['pembayaran_id'] ?? '';
    $kode_unik = $_POST['kode_unik'] ?? '';
    $tanggal   = $_POST['tanggal'] ?? '';
    $metode    = $_POST['metode'] ?? '';
    $status    = $_POST['status'] ?? '';

    if (!empty($id) && !empty($kode_unik) && !empty($tanggal) && !empty($metode) && !empty($status)) {

        // Gunakan prepared statement untuk keamanan
        $sql = "UPDATE pembayaran 
                SET kode_unik = ?, 
                    tanggal = ?, 
                    metode = ?, 
                    status = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $kode_unik, $tanggal, $metode, $status, $id);

        if ($stmt->execute()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['status' => 'success']);
            } else {
                header("Location: laporan_transaksi.php?msg=update_success");
            }
        } else {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data']);
            } else {
                header("Location: laporan_transaksi.php?msg=update_failed");
            }
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
