<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Hapus data transaksi
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM pembayaran WHERE id=$id");
    header("Location: transaksi.php?hapus_sukses=1");
    exit;
}

// Update status (verifikasi / tolak)
if (isset($_GET['verifikasi'])) {
    $id = intval($_GET['verifikasi']);
    mysqli_query($conn, "UPDATE pembayaran SET status='lunas' WHERE id=$id");
}
if (isset($_GET['tolak'])) {
    $id = intval($_GET['tolak']);
    mysqli_query($conn, "UPDATE pembayaran SET status='ditolak' WHERE id=$id");
}

// Search & Filter
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$metodeFilter = $_GET['metode'] ?? '';

$where = [];
if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $where[] = "(u.nama LIKE '%$search%' OR p.paket LIKE '%$search%')";
}
if ($statusFilter) {
    $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
    $where[] = "p.status = '$statusFilter'";
}
if ($metodeFilter) {
    $metodeFilter = mysqli_real_escape_string($conn, $metodeFilter);
    $where[] = "p.metode = '$metodeFilter'";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$limit = 8;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembayaran p JOIN users u ON p.user_id=u.id $whereSQL");
$totalRows = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRows / $limit);

// Ambil data
$query = "
    SELECT p.*, u.nama AS nama_siswa 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id
    $whereSQL
    ORDER BY p.tanggal DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
?>

<?php include '../includes/header.php'; ?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
    }
    .sidebar {
        width: 220px;
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
        padding: 12px 18px;
        text-decoration: none;
        font-size: 15px;
    }
    .sidebar a:hover {
        background-color: #0b5ed7;
    }
    .sidebar .logo {
        text-align: center;
        margin-bottom: 20px;
        color: white;
        font-size: 18px;
        font-weight: bold;
    }
    .content {
        margin-left: 220px;
        padding: 15px;
    }
    table {
        font-size: 14px;
    }
    .table th, .table td {
        padding: 8px;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <i class="bi bi-cash-stack me-2"></i>BimbelAja
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="checkout_tunai.php"><i class="bi bi-cash-coin me-2"></i>Checkout Tunai</a>
    <a href="data_siswa.php"><i class="bi bi-people me-2"></i>Data Siswa</a>
    <a href="transaksi.php" class="active"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</a>
</div>

<div class="content">
    <h4 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff me-2"></i>Riwayat Transaksi</h4>

    <?php if (isset($_GET['hapus_sukses'])): ?>
        <div class="alert alert-success">Transaksi berhasil dihapus.</div>
    <?php endif; ?>

    <!-- Filter & Search -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Cari nama/paket" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Status</option>
                <option value="lunas" <?= $statusFilter == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="ditolak" <?= $statusFilter == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="metode" class="form-select">
                <option value="">Metode</option>
                <option value="tunai" <?= $metodeFilter == 'tunai' ? 'selected' : '' ?>>Tunai</option>
                <option value="transfer_bank" <?= $metodeFilter == 'transfer_bank' ? 'selected' : '' ?>>Transfer</option>
                <option value="e_wallet" <?= $metodeFilter == 'e_wallet' ? 'selected' : '' ?>>E-Wallet</option>
                <option value="qris" <?= $metodeFilter == 'qris' ? 'selected' : '' ?>>QRIS</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
        </div>
    </form>

    <!-- Tabel -->
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Siswa</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalRows == 0): ?>
                            <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
                        <?php else: ?>
                            <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                    <td><?= htmlspecialchars($row['paket']) ?></td>
                                    <td>Rp<?= number_format($row['harga']) ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($row['metode']) ?></span></td>
                                    <td>
                                        <?php
                                            $status = $row['status'];
                                            $badgeClass = match($status) {
                                                'lunas' => 'success',
                                                'pending' => 'warning text-dark',
                                                'ditolak' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['tanggal']))) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <a href="?verifikasi=<?= $row['id'] ?>" class="btn btn-sm btn-success">✔</a>
                                            <a href="?tolak=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✖</a>
                                        <?php endif; ?>
                                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-hapus">🗑</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $statusFilter ?>&metode=<?= $metodeFilter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({
                title: 'Hapus transaksi?',
                text: "Data ini tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
