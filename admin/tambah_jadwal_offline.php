<?php
include '../config/database.php';

// Validasi input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kelas_id = $_POST['kelas_id'];
    $kategori_id = $_POST['kategori_id'];
    $tutor_id = $_POST['tutor_id'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $keterangan = $_POST['keterangan'];

    // Upload file materi jika ada
    $materi_file = null;
    if (!empty($_FILES['materi_file']['name'])) {
        $target_dir = "../uploads/";
        $materi_file = time() . '_' . basename($_FILES["materi_file"]["name"]);
        $target_file = $target_dir . $materi_file;
        move_uploaded_file($_FILES["materi_file"]["tmp_name"], $target_file);
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO jadwal_offline (kelas_id, kategori_id, tutor_id, tanggal, jam_mulai, jam_selesai, keterangan, materi_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssss", $kelas_id, $kategori_id, $tutor_id, $tanggal, $jam_mulai, $jam_selesai, $keterangan, $materi_file);
    
    if ($stmt->execute()) {
        echo "<script>alert('Jadwal berhasil ditambahkan'); window.location.href='kelola_jadwal_offline.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan jadwal'); window.location.href='kelola_jadwal_offline.php';</script>";
    }

    $stmt->close();
}
?>