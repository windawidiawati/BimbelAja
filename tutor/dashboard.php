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
// Total Jadwal Offline
$jadwal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM jadwal_offline WHERE tutor_id = '$tutor_id'"))['total'] ?? 0;
// Total Forum
$forum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM forum WHERE user_id = '$tutor_id'"))['total'] ?? 0;

include '../includes/tutor_header.php';
?>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Tutor</h4>
    <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b>! Semangat mengajar hari ini 😊</p>

    <!-- ✅ Tambahan: Jadwal Hari Ini dan Besok -->
    <?php
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $jadwal_hari_ini_besok = mysqli_query($conn, "
        SELECT jo.*, k.nama_kelas, km.nama_kategori 
        FROM jadwal_offline jo
        JOIN kelas k ON jo.kelas_id = k.id
        JOIN kategori_materi km ON jo.kategori_id = km.id
        WHERE jo.tutor_id = '$tutor_id'
        AND (jo.tanggal = '$today' OR jo.tanggal = '$tomorrow')
        ORDER BY jo.tanggal ASC, jo.jam_mulai ASC
    ");

    $hari_indo = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <b>📅 Jadwal Hari Ini & Besok</b>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($jadwal_hari_ini_besok) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-secondary">
                            <tr>
                                <th>Tanggal</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Mapel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($jadwal_hari_ini_besok)): ?>
                                <tr>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= $hari_indo[date('l', strtotime($row['tanggal']))] ?></td>
                                    <td><?= $row['jam_mulai'] . " - " . $row['jam_selesai'] ?></td>
                                    <td><?= $row['nama_kelas'] ?></td>
                                    <td><?= $row['nama_kategori'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Tidak ada jadwal untuk hari ini atau besok.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kartu-kartu ringkasan -->
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
                        <div class="text-muted small">Jadwal Offline</div>
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
