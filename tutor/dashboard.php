<?php
include '../includes/auth.php';
include '../config/database.php';

// Cek role tutor
if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

// Ambil data summary
$tutor_id = $_SESSION['user']['id'];

// Total Materi
$materi_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM materi WHERE tutor_id = '$tutor_id'");
$materi = mysqli_fetch_assoc($materi_query)['total'] ?? 0;

// Total Soal
$soal_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM soal WHERE tutor_id = '$tutor_id'");
$soal = mysqli_fetch_assoc($soal_query)['total'] ?? 0;

// Total Jadwal
$jadwal_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kelas_online WHERE tutor_id = '$tutor_id'");
$jadwal = mysqli_fetch_assoc($jadwal_query)['total'] ?? 0;

// Total Forum
$forum_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM forum WHERE user_id = '$tutor_id'");
$forum = mysqli_fetch_assoc($forum_query)['total'] ?? 0;

include '../includes/header.php';
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
    .card-icon {
        font-size: 2rem;
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
    <a href="forum.php"><i class="bi bi-chat-dots me-2"></i>Forum</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
</div>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Tutor</h4>
    <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b>! Semangat mengajar hari ini 😊</p>

    <div class="row g-4 mt-3">
        <!-- Total Materi -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-primary card-icon me-3"><i class="bi bi-upload"></i></div>
                    <div>
                        <div class="text-muted small">Materi</div>
                        <div class="fw-bold fs-5"><?= $materi ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Soal -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-success card-icon me-3"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <div class="text-muted small">Soal</div>
                        <div class="fw-bold fs-5"><?= $soal ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Jadwal -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-warning card-icon me-3"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <div class="text-muted small">Jadwal</div>
                        <div class="fw-bold fs-5"><?= $jadwal ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Forum -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-info card-icon me-3"><i class="bi bi-chat-dots"></i></div>
                    <div>
                        <div class="text-muted small">Forum</div>
                        <div class="fw-bold fs-5"><?= $forum ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
