<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['pembayaran_id'] ?? '';
    $kode_unik = $_POST['kode_unik'] ?? '';
    $tanggal   = $_POST['tanggal'] ?? '';
    $metode    = $_POST['metode'] ?? '';
    $status    = $_POST['status'] ?? '';
    
    // Ambil parameter filter dari POST
    $bulan_filter = $_POST['bulan_filter'] ?? '';
    $tahun_filter = $_POST['tahun_filter'] ?? '';
    $status_filter = $_POST['status_filter'] ?? '';
    $search_filter = $_POST['search_filter'] ?? '';

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
            $msg = 'update_success';
        } else {
            $msg = 'update_failed';
        }
        $stmt->close();
    } else {
        $msg = 'update_failed';
    }

    // Bangun string query dengan parameter filter yang ada
    $query_params = [];
    if (!empty($bulan_filter)) {
        $query_params[] = "bulan=" . urlencode($bulan_filter);
    }
    if (!empty($tahun_filter)) {
        $query_params[] = "tahun=" . urlencode($tahun_filter);
    }
    if (!empty($status_filter)) {
        $query_params[] = "status=" . urlencode($status_filter);
    }
    if (!empty($search_filter)) {
        $query_params[] = "search=" . urlencode($search_filter);
    }
    
    // Tambahkan pesan dan parameter filter ke URL
    $redirect_url = 'laporan_transaksi.php?msg=' . $msg;
    if (!empty($query_params)) {
        $redirect_url .= '&' . implode('&', $query_params);
    }

    // Lakukan redirect
    header("Location: " . $redirect_url);
    exit();

} else {
    // Jika bukan POST, redirect kembali
    header("Location: laporan_transaksi.php");
    exit();
}
?>