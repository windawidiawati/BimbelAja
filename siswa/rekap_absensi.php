<?php
session_start();
include '../config/database.php';
$title = "Rekap Absensi";
include '../includes/siswa_header_langganan.php';

// Pastikan user adalah siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];

// Ambil data jadwal + absensi
$sql = "
    SELECT 
        j.id AS jadwal_id,
        j.kategori_id,
        j.kelas_id,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        a.status,
        a.created_at AS tanggal_absen
    FROM jadwal_offline j
    LEFT JOIN absensi_offline a 
        ON j.id = a.jadwal_id 
        AND a.siswa_id = ?
    ORDER BY j.tanggal DESC, j.jam_mulai DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$result = $stmt->get_result();

// Variabel rekap
$total = 0;
$hadir = 0;
$sakit = 0;
$alpha = 0;

// Ambil data dulu ke array
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total++;

    if ($row['status'] === 'hadir') {
        $hadir++;
    } elseif ($row['status'] === 'sakit') {
        $sakit++;
    } else {
        $alpha++;
    }
}
?>

<style>
    .stat-card {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
    }
    .stat-title {
        font-size: 14px;
        font-weight: bold;
        color: white;
    }
    .stat-value {
        font-size: 20px;
        font-weight: bold;
    }
    .bg-total { background-color: #17a2b8; color: #fff; }
    .bg-hadir { background-color: #28a745; color: #fff; }
    .bg-sakit { background-color: #ffc107; color: #fff; }
    .bg-alpha { background-color: #dc3545; color: #fff; }
</style>


<div class="container mt-4">
    <h3>Rekap Absensi</h3>

    <!-- Statistik Kehadiran -->
    <div class="row text-center mb-4">
        <div class="col-md-3 mb-2">
            <div class="stat-card bg-total">
                <div class="stat-title">Total Pertemuan</div>
                <div class="stat-value"><?= $total; ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="stat-card bg-hadir">
                <div class="stat-title">Hadir</div>
                <div class="stat-value"><?= $hadir; ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="stat-card bg-sakit">
                <div class="stat-title">Sakit</div>
                <div class="stat-value"><?= $sakit; ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="stat-card bg-alpha">
                <div class="stat-title">Alpha</div>
                <div class="stat-value"><?= $alpha; ?></div>
            </div>
        </div>
    </div>

    <!-- Tabel Absensi -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Status</th>
                    <th>Tanggal Absen</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($data)) {
                    $no = 1;
                    foreach ($data as $row) {
                        $statusBadge = '<span class="badge bg-secondary">Belum Absen</span>';
                        if ($row['status'] === 'hadir') {
                            $statusBadge = '<span class="badge bg-success">Hadir</span>';
                        } elseif ($row['status'] === 'sakit') {
                            $statusBadge = '<span class="badge bg-warning text-dark">Sakit</span>';
                        } elseif ($row['status'] === 'alpha') {
                            $statusBadge = '<span class="badge bg-danger">Alpha</span>';
                        }

                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['tanggal']}</td>
                                <td>{$row['jam_mulai']}</td>
                                <td>{$row['jam_selesai']}</td>
                                <td>{$statusBadge}</td>
                                <td>".($row['tanggal_absen'] ?? '-')."</td>
                              </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Belum ada jadwal</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../includes/footer.php';
?>

