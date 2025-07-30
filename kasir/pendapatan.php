<?php
include '../includes/kasir_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Total Pendapatan dari langganan aktif
$query_total = mysqli_query($conn, "
    SELECT SUM(p.harga) as total_pendapatan
    FROM langganan l
    JOIN paket p ON l.paket = p.nama
    WHERE l.status = 'aktif'
");
$data_total = mysqli_fetch_assoc($query_total);

// Ambil rincian transaksi langganan aktif
$query_rincian = mysqli_query($conn, "
    SELECT l.id, u.nama AS nama_siswa, l.paket, p.harga, l.tanggal_mulai
    FROM langganan l
    JOIN users u ON l.user_id = u.id
    JOIN paket p ON l.paket = p.nama
    WHERE l.status = 'aktif'
    ORDER BY l.tanggal_mulai DESC
");
?>

<h4 class="fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Pendapatan</h4>

<!-- Ringkasan Total Pendapatan -->
<div class="card p-3 mb-4 shadow-sm border-start border-success border-4">
    <h6 class="text-muted">Total Pendapatan</h6>
    <h3 class="text-success">Rp <?= number_format($data_total['total_pendapatan'], 0, ',', '.') ?></h3>
</div>

<!-- Tabel Rincian Pendapatan -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Rincian Langganan Aktif</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nama Siswa</th>
                    <th>Paket</th>
                    <th>Harga</th>
                    <th>Tanggal Mulai</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query_rincian)) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nama_siswa'] ?></td>
                        <td><?= $row['paket'] ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
