<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'siswa') {
    header('Location: ../index.php');
    exit;
}
$title = "Progres Siswa";
include '../includes/siswa_header_langganan.php';
include '../config/database.php';

$siswa_id = $_SESSION['user']['id'];

// =====================
// Hitung Absensi
// =====================
$sql_absensi = "
    SELECT 
        SUM(status = 'Hadir') AS hadir,
        SUM(status = 'Sakit') AS sakit,
        SUM(status = 'Alpha') AS alpha
    FROM absensi_offline
    WHERE siswa_id = ?
";
$stmt_absensi = $conn->prepare($sql_absensi);
$stmt_absensi->bind_param("i", $siswa_id);
$stmt_absensi->execute();
$absensi = $stmt_absensi->get_result()->fetch_assoc();

$total_absensi = ($absensi['hadir'] ?? 0) + ($absensi['sakit'] ?? 0) + ($absensi['alpha'] ?? 0);
$persen_absensi = $total_absensi > 0 ? round(($absensi['hadir'] / $total_absensi) * 100) : 0;

// =====================
// Hitung Latihan
// =====================
// Hitung total latihan
$sql_total_latihan = "SELECT COUNT(*) AS total FROM latihan";
$total_latihan = $conn->query($sql_total_latihan)->fetch_assoc()['total'] ?? 0;

// Hitung latihan yang sudah selesai oleh siswa ini
$sql_latihan_selesai = "
    SELECT COUNT(*) AS selesai
    FROM latihan_siswa
    WHERE siswa_id = ? AND status = 'selesai'
";
$stmt_latihan = $conn->prepare($sql_latihan_selesai);
$stmt_latihan->bind_param("i", $siswa_id);
$stmt_latihan->execute();
$latihan_selesai = $stmt_latihan->get_result()->fetch_assoc()['selesai'] ?? 0;

// Persentase progres
$persen_latihan = $total_latihan > 0 ? round(($latihan_selesai / $total_latihan) * 100) : 0;


// =====================
// Hitung Progress Akhir
// =====================
$progress_akhir = ($persen_absensi + $persen_latihan) / 2;
?>

<style>
.progress { height: 25px; }
.progress-bar { font-weight: bold; }
</style>

<div class="container mt-5">
    <h3>Progress Belajar</h3>

    <h5>Absensi</h5>
    <p>Hadir: <strong><?= $absensi['hadir'] ?? 0 ?></strong> | Sakit: <strong><?= $absensi['sakit'] ?? 0 ?></strong> | Alpha: <strong><?= $absensi['alpha'] ?? 0 ?></strong></p>
    <div class="progress mb-4">
        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $persen_absensi ?>%">
            <?= $persen_absensi ?>%
        </div>
    </div>

    <h5>Latihan</h5>
    <p>Total Latihan: <strong><?= $total_latihan ?></strong> | Selesai: <strong><?= $latihan_selesai ?></strong></p>
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $persen_latihan ?>%">
            <?= $persen_latihan ?>%
        </div>
    </div>

    <h4 class="mt-4">Progress Akhir</h4>
    <div class="progress">
        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progress_akhir ?>%">
            <?= $progress_akhir ?>%
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
