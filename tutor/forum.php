<?php
include '../includes/auth.php';
include '../includes/header.php';

if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

include '../config/database.php';
$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Proses kirim diskusi baru atau balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
    $query = "INSERT INTO forum (judul, isi, user_id, role, parent_id, created_at) 
              VALUES ('$judul', '$isi', $user_id, '$role', $parent_id, NOW())";
    mysqli_query($conn, $query);
}

// Ambil diskusi utama
$diskusi = mysqli_query($conn, "SELECT * FROM forum WHERE parent_id IS NULL ORDER BY created_at DESC");
?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
    }
    .sidebar {
        width: 240px;
        background-color: #0d6efd;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        padding-top: 20px;
    }
    .sidebar a {
        color: white;
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        font-size: 16px;
    }
    .sidebar a:hover {
        background-color: #0b5ed7;
    }
    .sidebar .logo {
        text-align: center;
        margin-bottom: 30px;
        color: white;
        font-size: 20px;
        font-weight: bold;
    }
    .content {
        margin-left: 240px;
        padding: 20px;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="unggah_materi.php"><i class="bi bi-upload me-2"></i>Unggah Materi</a>
    <a href="buat_soal.php"><i class="bi bi-pencil-square me-2"></i>Buat Soal</a>
    <a href="jadwal_kelas.php"><i class="bi bi-calendar-event me-2"></i>Jadwal Kelas</a>
    <a href="forum.php" class="active" style="background-color:#0b5ed7;"><i class="bi bi-chat-dots me-2"></i>Forum</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
</div>

<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 text-primary">💬 Forum Diskusi</h3>

        <!-- Form buat diskusi baru -->
        <form method="POST" class="mb-4 p-4 border rounded bg-light shadow-sm">
            <h5>Buat Diskusi Baru</h5>
            <div class="mb-2">
                <input type="text" name="judul" class="form-control" placeholder="Judul Diskusi" required>
            </div>
            <div class="mb-2">
                <textarea name="isi" class="form-control" placeholder="Tulis isi diskusi..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim</button>
        </form>

        <!-- Daftar Diskusi -->
        <?php while ($row = mysqli_fetch_assoc($diskusi)): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['judul']); ?></h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['isi'])); ?></p>
                    <small class="text-muted">Oleh <?= htmlspecialchars($row['role']); ?> - <?= date('d M Y H:i', strtotime($row['created_at'])); ?></small>

                    <!-- Form balasan -->
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="parent_id" value="<?= $row['id']; ?>">
                        <input type="hidden" name="judul" value="Re: <?= htmlspecialchars($row['judul']); ?>">
                        <textarea name="isi" class="form-control mb-2" placeholder="Tulis balasan..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-secondary">Balas</button>
                    </form>

                    <!-- Balasan -->
                    <?php
                    $id = $row['id'];
                    $balasan = mysqli_query($conn, "SELECT * FROM forum WHERE parent_id = $id ORDER BY created_at ASC");
                    while ($b = mysqli_fetch_assoc($balasan)): ?>
                        <div class="border rounded p-2 mb-2 ms-4 bg-light">
                            <p class="mb-1"><strong><?= htmlspecialchars($b['role']); ?>:</strong> <?= nl2br(htmlspecialchars($b['isi'])); ?></p>
                            <small class="text-muted"><?= date('d M Y H:i', strtotime($b['created_at'])); ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
