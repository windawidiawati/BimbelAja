<?php
include '../config/database.php';

// Pastikan data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id']) && !empty($_POST['status'])) {

        // Sanitasi input
        $id     = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        // Query update
        $query = "UPDATE absensi_offline SET status = '$status' WHERE id = '$id'";
        $update = mysqli_query($conn, $query);

        if ($update) {
            echo "<script>
                alert('Absensi berhasil diperbarui!');
                window.location='kelola_absensi.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal memperbarui absensi: " . mysqli_error($conn) . "');
                window.location='kelola_absensi.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Data tidak lengkap!');
            window.location='kelola_absensi.php';
        </script>";
    }
} else {
    // Kalau bukan POST, kembalikan ke halaman utama
    header("Location: kelola_absensi.php");
    exit;
}
?>
