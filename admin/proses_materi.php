<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (isset($_POST['setujui'])) {
        $sql = "UPDATE materi SET status='diterima' WHERE id=$id";
    } elseif (isset($_POST['tolak'])) {
        $sql = "UPDATE materi SET status='ditolak' WHERE id=$id";
    } elseif (isset($_POST['hapus'])) {
        // Hapus file jika ada
        $get = mysqli_query($conn, "SELECT file FROM materi WHERE id=$id");
        $row = mysqli_fetch_assoc($get);
        if ($row && file_exists("../uploads/" . $row['file'])) {
            unlink("../uploads/" . $row['file']);
        }
        $sql = "DELETE FROM materi WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: kelola_materi.php?success=1");
    } else {
        echo "Gagal memproses: " . mysqli_error($conn);
    }
}
?>
