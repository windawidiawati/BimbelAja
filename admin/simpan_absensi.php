<?php
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jadwal_id = $_POST['jadwal_id'];
    $statusList = $_POST['status'];
    $catatanList = $_POST['catatan'];

    foreach ($statusList as $siswa_id => $status) {
        $catatan = isset($catatanList[$siswa_id]) ? $conn->real_escape_string($catatanList[$siswa_id]) : null;

        // Cek apakah sudah ada absensi sebelumnya
        $cek = $conn->query("SELECT id FROM absensi_offline WHERE jadwal_id = $jadwal_id AND siswa_id = $siswa_id");

        if ($cek->num_rows > 0) {
            // Update
            $conn->query("UPDATE absensi_offline SET status='$status', catatan='$catatan' 
                          WHERE jadwal_id = $jadwal_id AND siswa_id = $siswa_id");
        } else {
            // Insert baru
            $conn->query("INSERT INTO absensi_offline (jadwal_id, siswa_id, status, catatan) 
                          VALUES ($jadwal_id, $siswa_id, '$status', '$catatan')");
        }
    }

    header("Location: kelola_absensi.php?jadwal_id=$jadwal_id&pesan=sukses");
}
?>
