<?php
include '../config/database.php';

// Ambil data dari form
$user_id          = $_POST['user_id'];
$kelas_id         = $_POST['kelas_id'];
$paket_id         = $_POST['paket_id'];
$tanggal_mulai    = $_POST['tanggal_mulai'];
$tanggal_berakhir = $_POST['tanggal_berakhir'];
$metode           = $_POST['metode'];
$status           = $_POST['status'];

// Ambil nama paket
$paket_result = mysqli_query($conn, "SELECT nama, harga FROM paket WHERE id = '$paket_id'");
$paket_row    = mysqli_fetch_assoc($paket_result);
$paket_nama   = $paket_row['nama'];
$paket_harga  = $paket_row['harga'];

// Simpan ke tabel langganan
$sql = "INSERT INTO langganan 
            (user_id, kelas_id, paket_id, paket, tanggal_mulai, tanggal_berakhir, status, created_at) 
        VALUES 
            ('$user_id', '$kelas_id', '$paket_id', '$paket_nama', '$tanggal_mulai', '$tanggal_berakhir', '$status', NOW())";

if (mysqli_query($conn, $sql)) {
    // Simpan ke tabel pembayaran
    mysqli_query($conn, "INSERT INTO pembayaran (user_id, paket, harga, metode, status, tanggal) 
                         VALUES ('$user_id', '$paket_nama', '$paket_harga', '$metode', 'lunas', NOW())");

    // Redirect kembali dengan pesan sukses
    header("Location: data_siswa.php?success=1");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>