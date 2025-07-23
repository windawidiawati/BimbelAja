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
$materi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM materi WHERE tutor_id = '$tutor_id'"))['total'] ?? 0;
// Total Soal
$soal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM soal WHERE tutor_id = '$tutor_id'"))['total'] ?? 0;
// Total Jadwal Online
$jadwal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kelas_online WHERE tutor_id = '$tutor_id'"))['total'] ?? 0;
// Total Forum
$forum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM forum WHERE user_id = '$tutor_id'"))['total'] ?? 0;

include '../includes/tutor_header.php';
?>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Tutor</h4>
    <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b>! Semangat mengajar hari ini 😊</p>

    <div class="row g-4 mt-3">
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-primary fs-2 me-3"><i class="bi bi-upload"></i></div>
                    <div>
                        <div class="text-muted small">Materi</div>
                        <div class="fw-bold fs-5"><?= $materi ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-success fs-2 me-3"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <div class="text-muted small">Soal</div>
                        <div class="fw-bold fs-5"><?= $soal ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-warning fs-2 me-3"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <div class="text-muted small">Jadwal Online</div>
                        <div class="fw-bold fs-5"><?= $jadwal ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="text-info fs-2 me-3"><i class="bi bi-chat-dots"></i></div>
                    <div>
                        <div class="text-muted small">Forum</div>
                        <div class="fw-bold fs-5"><?= $forum ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
