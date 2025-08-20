<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Ambil semua kelas untuk dropdown filter
$kelas_result = mysqli_query($conn, "SELECT id, nama_kelas, jenjang FROM kelas ORDER BY jenjang, nama_kelas");
$daftar_kelas = [];
while ($k = mysqli_fetch_assoc($kelas_result)) {
    $daftar_kelas[] = $k;
}

// Ambil semua paket untuk dropdown filter
$paket_result = mysqli_query($conn, "SELECT id, nama FROM paket");
$daftar_paket = [];
while ($p = mysqli_fetch_assoc($paket_result)) {
    $daftar_paket[] = $p;
}

// Filter dari URL
$filter_kelas = isset($_GET['kelas_id']) ? trim($_GET['kelas_id']) : '';
$filter_paket = isset($_GET['paket_id']) ? trim($_GET['paket_id']) : '';

// Bangun query utama
$query = "
    SELECT 
        u.id AS user_id,
        u.nama,
        k.nama_kelas,
        k.jenjang,
        p.nama AS nama_paket
    FROM users u
    LEFT JOIN (
        SELECT l1.*
        FROM langganan l1
        INNER JOIN (
            SELECT user_id, MAX(tanggal_berakhir) AS max_tanggal
            FROM langganan
            WHERE status = 'Aktif' AND tanggal_berakhir >= CURDATE()
            GROUP BY user_id
        ) l2 ON l1.user_id = l2.user_id AND l1.tanggal_berakhir = l2.max_tanggal
    ) l ON l.user_id = u.id
    LEFT JOIN kelas k ON u.kelas_id = k.id
    LEFT JOIN paket p ON l.paket_id = p.id
    WHERE u.role = 'siswa'
";

if ($filter_kelas !== '') {
    $escaped_kelas = mysqli_real_escape_string($conn, $filter_kelas);
    $query .= " AND u.kelas_id = '{$escaped_kelas}'";
}

if ($filter_paket !== '') {
    $escaped_paket = mysqli_real_escape_string($conn, $filter_paket);
    $query .= " AND p.id = '{$escaped_paket}'";
}

$query .= " ORDER BY u.nama ASC";
$result = mysqli_query($conn, $query);
?>

<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 fw-bold text-primary">📋 Data Siswa</h3>

        <!-- Filter -->
        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter Kelas:</label>
                <select name="kelas_id" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($daftar_kelas as $kelas) { ?>
                        <option value="<?= htmlspecialchars($kelas['id']); ?>" <?= ($filter_kelas === $kelas['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kelas['nama_kelas'] . ' - ' . $kelas['jenjang']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Filter Paket:</label>
                <select name="paket_id" class="form-select">
                    <option value="">Semua Paket</option>
                    <?php foreach ($daftar_paket as $paket) { ?>
                        <option value="<?= htmlspecialchars($paket['id']); ?>" <?= ($filter_paket === $paket['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($paket['nama']); ?>
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

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jenjang</th>
                        <th>Paket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $nama_paket = $row['nama_paket'] ?? '-';
                            $nama_kelas = $row['nama_kelas'] ?? '-';
                            $jenjang = $row['jenjang'] ?? '-';
                            echo "<tr>
                                    <td>{$no}</td>
                                    <td>" . htmlspecialchars($row['nama']) . "</td>
                                    <td>" . htmlspecialchars($nama_kelas) . "</td>
                                    <td>" . htmlspecialchars($jenjang) . "</td>
                                    <td>" . htmlspecialchars($nama_paket) . "</td>
                                  </tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='5'>Tidak ada data untuk filter ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
