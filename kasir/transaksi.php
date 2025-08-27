<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Ambil filter metode jika ada
$filter_metode = isset($_GET['metode']) ? $_GET['metode'] : '';

// Query dasar
$sql = "
    SELECT p.*, u.nama 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id
";

// Tambah WHERE jika filter metode dipilih
if (!empty($filter_metode)) {
    $sql .= " WHERE p.metode = '" . mysqli_real_escape_string($conn, $filter_metode) . "'";
}

$sql .= " ORDER BY p.id DESC";

$query = mysqli_query($conn, $sql);

$hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

include '../includes/kasir_header.php';
?>

<div class="container mt-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</h4>

    <!-- Filter -->
    <form method="GET" class="row mb-4 g-2 align-items-end">
        <div class="col-md-3">
            <label for="metode" class="form-label">Filter Metode Pembayaran</label>
            <select name="metode" id="metode" class="form-select">
                <option value="">-- Semua Metode --</option>
                <option value="tunai" <?= $filter_metode === 'tunai' ? 'selected' : '' ?>>Tunai</option>
                <option value="transfer" <?= $filter_metode === 'transfer' ? 'selected' : '' ?>>Transfer</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
            <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Reset</a>
        </div>
    </form>

    <!-- Tabel Transaksi -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                            <?php
                            $tanggal_format = '-';
                            if (!empty($row['tanggal'])) {
                                $day = $hari[date('l', strtotime($row['tanggal']))];
                                $tanggal_format = $day . ', ' . date('d-m-Y', strtotime($row['tanggal']));
                            }

                            $badgeClass = ($row['status'] === 'lunas') ? 'bg-success' 
                                        : (($row['status'] === 'pending' || $row['status'] === 'menunggu_kasir') ? 'bg-warning text-dark'
                                        : ($row['status'] === 'ditolak' ? 'bg-danger' : 'bg-secondary'));
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['paket']) ?></td>
                                <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="text-capitalize small"><?= $row['metode'] ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= $tanggal_format ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($query) === 0): ?>
                            <tr><td colspan="7">Tidak ada data transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
