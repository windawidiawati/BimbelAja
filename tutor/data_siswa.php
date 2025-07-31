<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Daftar kelas
$daftar_kelas = [
    'Kelas 1 SD', 'Kelas 2 SD', 'Kelas 3 SD', 'Kelas 4 SD', 'Kelas 5 SD', 'Kelas 6 SD',
    'Kelas 7 SMP', 'Kelas 8 SMP', 'Kelas 9 SMP',
    'Kelas 10 SMA IPA', 'Kelas 11 SMA IPA', 'Kelas 12 SMA IPA',
    'Kelas 10 SMA IPS', 'Kelas 11 SMA IPS', 'Kelas 12 SMA IPS'
];

// Ambil semua paket
$paket_result = mysqli_query($conn, "SELECT id, nama FROM paket");
$daftar_paket = [];
while ($p = mysqli_fetch_assoc($paket_result)) {
    $daftar_paket[] = $p;
}

// Filter dari URL
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_paket = isset($_GET['paket_id']) ? $_GET['paket_id'] : '';
?>

<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 fw-bold text-primary">📋 Data Siswa</h3>

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
                <label class="form-label">Filter Paket:</label>
                <select name="paket_id" class="form-select">
                    <option value="">Semua Paket</option>
                    <?php foreach ($daftar_paket as $paket) { ?>
                        <option value="<?= $paket['id']; ?>" <?= ($filter_paket == $paket['id']) ? 'selected' : ''; ?>>
                            <?= $paket['nama']; ?>
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
        $query = "
            SELECT 
                u.id AS user_id,
                MAX(u.nama) AS nama,
                MAX(u.kelas) AS kelas,
                MAX(u.jenjang) AS jenjang,
                MAX(p.nama) AS nama_paket
            FROM users u
            LEFT JOIN langganan l ON l.user_id = u.id
            LEFT JOIN paket p ON l.paket_id = p.id
            WHERE u.role = 'siswa'
        ";

        if ($filter_kelas !== '') {
            $query .= " AND u.kelas = '" . mysqli_real_escape_string($conn, $filter_kelas) . "'";
        }

        if ($filter_paket !== '') {
            $query .= " AND p.id = '" . mysqli_real_escape_string($conn, $filter_paket) . "'";
        }

        $query .= " GROUP BY u.id ORDER BY nama ASC";
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
                        <th>Paket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $nama_paket = $row['nama_paket'] ?? '-';
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['nama']}</td>
                                <td>{$row['kelas']}</td>
                                <td>{$row['jenjang']}</td>
                                <td>{$nama_paket}</td>
                              </tr>";
                        $no++;
                    }

                    if ($no === 1) {
                        echo "<tr><td colspan='5'>Tidak ada data untuk filter ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
