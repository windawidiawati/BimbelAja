<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

// Pastikan hanya tutor yang bisa akses
if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

// Ambil ID user dari session
$user_id = $_SESSION['user']['id'];

// Ambil data user dari database
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);
?>

<div class="container mt-4">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-body">
            <h4 class="card-title">Profil Tutor</h4>
            <hr>
            <table class="table">
                <tr>
                    <th>Nama</th>
                    <td><?php echo htmlspecialchars($user['nama']); ?></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <th>Keahlian</th>
                    <td><?php echo htmlspecialchars($user['keahlian'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Kelas</th>
                    <td><?php echo htmlspecialchars($user['kelas'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jenjang</th>
                    <td><?php echo htmlspecialchars($user['jenjang'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?php echo ucfirst($user['role']); ?></td>
                </tr>
            </table>
            <a href="edit_profil.php" class="btn btn-primary">Edit Profil</a>
        </div>
    </div>
</div>
