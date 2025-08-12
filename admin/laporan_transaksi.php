<?php
include '../config/database.php';
include '../includes/admin_header.php';

$error = '';

// Ambil filter dari URL
$bulan  = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun  = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Ringkasan Pendapatan
$total_hari_ini  = 0;
$total_bulan_ini = 0;
$total_tahun_ini = 0;

$q_hari = mysqli_query($conn, "SELECT SUM(harga) AS total FROM pembayaran WHERE DATE(tanggal) = CURDATE() AND status = 'sukses'");
if ($q_hari) $total_hari_ini = mysqli_fetch_assoc($q_hari)['total'] ?? 0;

$q_bulan = mysqli_query($conn, "SELECT SUM(harga) AS total FROM pembayaran WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE()) AND status = 'sukses'");
if ($q_bulan) $total_bulan_ini = mysqli_fetch_assoc($q_bulan)['total'] ?? 0;

$q_tahun = mysqli_query($conn, "SELECT SUM(harga) AS total FROM pembayaran WHERE YEAR(tanggal) = YEAR(CURDATE()) AND status = 'sukses'");
if ($q_tahun) $total_tahun_ini = mysqli_fetch_assoc($q_tahun)['total'] ?? 0;

// Bangun filter dinamis
$whereClauses = [];
if (!empty($bulan)) {
    $whereClauses[] = "MONTH(p.tanggal) = " . intval($bulan);
}
if (!empty($tahun)) {
    $whereClauses[] = "YEAR(p.tanggal) = " . intval($tahun);
}
if (!empty($status)) {
    $whereClauses[] = "p.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "(u.nama LIKE '%$safeSearch%' OR p.kode_bayar LIKE '%$safeSearch%')";
}

$where = "";
if (!empty($whereClauses)) {
    $where = "WHERE " . implode(" AND ", $whereClauses);
}

// Hitung total data untuk pagination
$total_rows = 0;
$total_pages = 1;
$total_query = "SELECT COUNT(*) AS total
                FROM pembayaran p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN paket pk ON p.paket_id = pk.id
                LEFT JOIN langganan l ON p.user_id = l.user_id AND p.paket_id = l.paket_id
                $where";
$res_total = mysqli_query($conn, $total_query);
if ($res_total) {
    $total_rows = mysqli_fetch_assoc($res_total)['total'] ?? 0;
    $total_pages = max(1, ceil($total_rows / $per_page));
}

// Query utama
$sql = "SELECT p.id AS pembayaran_id, p.*, 
               u.nama AS nama_siswa, 
               u.email AS email_siswa, 
               u.no_hp AS nohp_siswa,
               u.jenjang AS jenjang_siswa,
               pk.nama AS nama_paket, 
               pk.durasi, 
               pk.satuan_durasi, 
               pk.jenjang AS jenjang_paket,
               pk.tipe AS tipe_paket,
               pk.harga AS harga_paket,
               l.tanggal_mulai,
               l.tanggal_berakhir,
               l.status AS status_langganan
        FROM pembayaran p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN paket pk ON p.paket_id = pk.id
        LEFT JOIN langganan l ON p.user_id = l.user_id AND p.paket_id = l.paket_id
        $where
        ORDER BY p.tanggal DESC
        LIMIT $offset, $per_page";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Query error: " . mysqli_error($conn);
}
?>

<div class="content">
    <div class="container mt-4">
        <h4 class="mb-4">Laporan Transaksi</h4>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Ringkasan -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Hari Ini</h6>
                        <p>Rp <?= number_format($total_hari_ini, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Total Bulan Ini</h6>
                        <p>Rp <?= number_format($total_bulan_ini, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Total Tahun Ini</h6>
                        <p>Rp <?= number_format($total_tahun_ini, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <form method="get" class="row mb-3">
            <div class="col-md-2">
                <select name="bulan" class="form-control">
                    <option value="">Semua Bulan</option>
                    <?php for ($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($bulan==$i)?'selected':'' ?>>
                            <?= date('F', mktime(0,0,0,$i,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="tahun" class="form-control">
                    <option value="">Semua Tahun</option>
                    <?php for ($i=date('Y'); $i>=2020; $i--): ?>
                        <option value="<?= $i ?>" <?= ($tahun==$i)?'selected':'' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                                    <select name="status" class="form-control">
                                            <option value="">Semua Status</option>
                                            <option value="lunas" <?= $status == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                            <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="ditolak" <?= $status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                            <option value="menunggu_kasir" <?= $status == 'menunggu_kasir' ? 'selected' : '' ?>>Menunggu Kasir</option>
                                        </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama / kode transaksi" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Jenjang</th>
                        <th>Paket</th>
                        <th>Harga</th>
                        <th>Tanggal</th>
                        <th>Kode Transaksi</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): $no = $offset + 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jenjang_siswa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_paket'] ?? '-') ?> <small>(<?= ucfirst($row['tipe_paket'] ?? '-') ?>)</small></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td><?= !empty($row['tanggal']) ? date("d M Y", strtotime($row['tanggal'])) : '-' ?></td>
                                <td><?= htmlspecialchars($row['kode_bayar'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['metode'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($row['status'] ?? '')=='sukses' ? 'success' : (($row['status'] ?? '')=='pending' ? 'warning text-dark' : 'danger') ?>">
                                        <?= ucfirst($row['status'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" 
                                        class="btn btn-warning btn-sm edit-btn"
                                        data-id="<?= $row['pembayaran_id'] ?>"
                                        data-kode="<?= htmlspecialchars($row['kode_bayar'] ?? '') ?>"
                                        data-tanggal="<?= !empty($row['tanggal']) ? date("Y-m-d", strtotime($row['tanggal'])) : '' ?>"
                                        data-metode="<?= htmlspecialchars($row['metode'] ?? '') ?>"
                                        data-status="<?= htmlspecialchars($row['status'] ?? '') ?>"
                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?= ($page==$i)?'active':'' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="update_transaksi.php">
                <input type="hidden" name="pembayaran_id" id="edit_pembayaran_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Kode Transaksi</label>
                        <input type="text" name="kode_bayar" id="edit_kode_bayar" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Metode</label>
                        <select name="metode" id="edit_metode" class="form-control" required>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="E-Wallet">E-Wallet</option>
                            <option value="Tunai">Tunai</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="sukses">Sukses</option>
                            <option value="gagal">Gagal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_pembayaran_id').value = this.dataset.id;
            document.getElementById('edit_kode_bayar').value = this.dataset.kode;
            document.getElementById('edit_tanggal').value = this.dataset.tanggal;
            document.getElementById('edit_metode').value = this.dataset.metode;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>
