<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor' && $_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

// Daftar kelas lengkap
$daftar_kelas = [
  'Kelas 1 SD', 'Kelas 2 SD', 'Kelas 3 SD', 'Kelas 4 SD', 'Kelas 5 SD', 'Kelas 6 SD',
  'Kelas 7 SMP', 'Kelas 8 SMP', 'Kelas 9 SMP',
  'Kelas 10 SMA IPA', 'Kelas 11 SMA IPA', 'Kelas 12 SMA IPA',
  'Kelas 10 SMA IPS', 'Kelas 11 SMA IPS', 'Kelas 12 SMA IPS'
];

// Ambil filter dari URL
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_kategori = isset($_GET['kategori_id']) ? $_GET['kategori_id'] : '';
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

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <i class="bi bi-mortarboard-fill me-2"></i>BimbelAja
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="unggah_materi.php"><i class="bi bi-upload me-2"></i>Unggah Materi</a>
    <a href="buat_soal.php"><i class="bi bi-pencil-square me-2"></i>Buat Soal</a>
    <a href="jadwal_kelas.php"><i class="bi bi-calendar-event me-2"></i>Jadwal Kelas</a>
    <a href="forum.php"><i class="bi bi-chat-dots me-2"></i>Forum</a>
    <a href="data_siswa.php" class="active" style="background-color:#0b5ed7;"><i class="bi bi-people me-2"></i>Data Siswa</a>
</div>

<!-- Konten -->
<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 fw-bold text-primary">📊 Data Siswa & Nilai Per Mata Pelajaran</h3>

        <!-- Filter -->
        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter Kelas:</label>
                <select name="kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($daftar_kelas as $kelas_option) { ?>
                        <option value="<?= $kelas_option; ?>" <?= ($filter_kelas == $kelas_option) ? 'selected' : ''; ?>>
                            <?= $kelas_option; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Filter Mata Pelajaran:</label>
                <select name="kategori_id" class="form-select">
                    <option value="">Semua Mapel</option>
                    <?php
                    $kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi");
                    while ($row = mysqli_fetch_assoc($kategori_result)) { ?>
                        <option value="<?= $row['id']; ?>" <?= ($filter_kategori == $row['id']) ? 'selected' : ''; ?>>
                            <?= $row['nama_kategori']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel-fill"></i> Terapkan Filter
                </button>
            </div>
        </form>

        <?php
        // Query utama nilai siswa
        $query = "
            SELECT 
                u.id AS user_id,
                u.nama,
                u.kelas,
                u.jenjang,
                km.nama_kategori,
                COUNT(js.id) AS total_dikerjakan,
                SUM(js.benar) AS total_benar
            FROM users u
            LEFT JOIN jawaban_siswa js ON u.id = js.user_id
            LEFT JOIN soal s ON js.soal_id = s.id
            LEFT JOIN kategori_materi km ON s.kategori_id = km.id
            WHERE u.role = 'siswa'
        ";

        if ($filter_kelas !== '') {
            $query .= " AND u.kelas = '" . mysqli_real_escape_string($conn, $filter_kelas) . "'";
        }
        if ($filter_kategori !== '') {
            $query .= " AND s.kategori_id = '" . mysqli_real_escape_string($conn, $filter_kategori) . "'";
        }

        $query .= " GROUP BY u.id, s.kategori_id ORDER BY u.nama ASC";
        $result = mysqli_query($conn, $query);
        ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jenjang</th>
                        <th>Mata Pelajaran</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $nama_kategori = $row['nama_kategori'] ?? '-';
                        $nilai = ($row['total_dikerjakan'] > 0)
                            ? round(($row['total_benar'] / $row['total_dikerjakan']) * 100, 2)
                            : 'Belum Ada';

                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['nama']}</td>
                                <td>{$row['kelas']}</td>
                                <td>{$row['jenjang']}</td>
                                <td>{$nama_kategori}</td>
                                <td>{$nilai}</td>
                              </tr>";

                        $no++;
                    }

                    if ($no === 1) {
                        echo "<tr><td colspan='6'>Tidak ada data untuk filter ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
