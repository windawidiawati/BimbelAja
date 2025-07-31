<?php
include '../includes/auth.php';
include '../includes/tutor_header.php'; // Gunakan header khusus tutor agar ada sidebar
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

<div class="content">
    <div class="container mt-4">
        <div class="card shadow-sm" style="max-width: 600px; margin: 0 auto;">
            <div class="card-body">
                <h4 class="card-title text-primary mb-3">👤 Profil Tutor</h4>
                <hr>
                <table class="table table-striped">
                    <tr>
                        <th>Nama</th>
                        <td><?= htmlspecialchars($user['nama']); ?></td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Keahlian</th>
                        <td><?= htmlspecialchars($user['keahlian'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td><?= htmlspecialchars($user['kelas'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Jenjang</th>
                        <td><?= htmlspecialchars($user['jenjang'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><?= ucfirst($user['role']); ?></td>
                    </tr>
                </table>
                <div class="text-end">
                    <a href="edit_profil.php" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
