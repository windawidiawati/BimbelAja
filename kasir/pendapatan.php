<?php
include '../includes/kasir_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Ambil filter tanggal dari form (default: bulan ini)
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

// Sanitasi input sederhana
$tgl_mulai = date('Y-m-d', strtotime($tgl_mulai));
$tgl_akhir = date('Y-m-d', strtotime($tgl_akhir));

// Query pendapatan per paket (ditaruh di atas)
$query_per_paket = mysqli_query($conn, "
    SELECT p.nama AS paket, COUNT(l.id) AS jumlah_langganan, SUM(p.harga) AS total_pendapatan
    FROM langganan l
    JOIN paket p ON l.paket = p.nama
    WHERE l.status = 'aktif' 
    AND l.tanggal_mulai BETWEEN '$tgl_mulai' AND '$tgl_akhir'
    GROUP BY p.nama
    ORDER BY total_pendapatan DESC
");

// Query total pendapatan
$query_total = mysqli_query($conn, "
    SELECT SUM(p.harga) as total_pendapatan
    FROM langganan l
    JOIN paket p ON l.paket = p.nama
    WHERE l.status = 'aktif' 
    AND l.tanggal_mulai BETWEEN '$tgl_mulai' AND '$tgl_akhir'
");
$data_total = mysqli_fetch_assoc($query_total);

// Query rincian langganan aktif
$query_rincian = mysqli_query($conn, "
    SELECT l.id, u.nama AS nama_siswa, l.paket, p.harga, l.tanggal_mulai
    FROM langganan l
    JOIN users u ON l.user_id = u.id
    JOIN paket p ON l.paket = p.nama
    WHERE l.status = 'aktif' 
    AND l.tanggal_mulai BETWEEN '$tgl_mulai' AND '$tgl_akhir'
    ORDER BY l.tanggal_mulai DESC
");
?>

<h4 class="fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Pendapatan</h4>

<!-- Form Filter Tanggal -->
<form method="GET" class="row g-3 mb-4">
    <div class="col-auto">
        <label for="tgl_mulai" class="col-form-label">Dari</label>
    </div>
    <div class="col-auto">
        <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai) ?>">
    </div>
    <div class="col-auto">
        <label for="tgl_akhir" class="col-form-label">Sampai</label>
    </div>
    <div class="col-auto">
        <input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>

<!-- Tabel Pendapatan per Paket (ditaruh paling atas) -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Pendapatan per Paket</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Paket</th>
                    <th>Jumlah Langganan</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query_per_paket) > 0): ?>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query_per_paket)) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['paket']) ?></td>
                            <td><?= $row['jumlah_langganan'] ?></td>
                            <td>Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Tidak ada data paket dalam periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Ringkasan Total Pendapatan -->
<div class="card p-3 mb-4 shadow-sm border-start border-success border-4">
    <h6 class="text-muted">Total Pendapatan</h6>
    <h3 class="text-success">Rp <?= number_format($data_total['total_pendapatan'] ?? 0, 0, ',', '.') ?></h3>
</div>

<!-- Tabel Rincian Pendapatan dengan nomor urut, tanpa kolom ID -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Rincian Langganan Aktif</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Nama Siswa</th>
                    <th>Paket</th>
                    <th>Harga</th>
                    <th>Tanggal Mulai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query_rincian) > 0): ?>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query_rincian)) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                            <td><?= htmlspecialchars($row['paket']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Tidak ada data langganan dalam periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
