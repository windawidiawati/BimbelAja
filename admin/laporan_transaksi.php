<?php
include '../config/database.php';
include '../includes/admin_header.php';

$error = '';

// Ambil filter dari URL
$bulan  = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun  = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

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

// Query untuk mengambil semua data (DataTables akan menangani pagination di sisi klien)
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
// Tambahkan filter pencarian jika ada
if (!empty($search)) {
    $search_query = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "(u.nama LIKE '%$search_query%' OR p.kode_unik LIKE '%$search_query%')";
}

$where = "";
if (!empty($whereClauses)) {
    $where = "WHERE " . implode(" AND ", $whereClauses);
}

// Query utama untuk mengambil semua data
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
               pk.deskripsi AS deskripsi_paket,
               l.tanggal_mulai,
               l.tanggal_berakhir,
               l.status AS status_langganan
        FROM pembayaran p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN paket pk ON p.paket_id = pk.id
        LEFT JOIN langganan l ON p.user_id = l.user_id AND p.paket_id = l.paket_id
        $where
        ORDER BY p.tanggal DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Query error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* CSS untuk tombol aksi */
        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-group .btn {
            margin-right: 5px;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        /* Styling untuk DataTables */
        .dataTables_wrapper {
            padding: 0;
        }
        .dataTables_length, .dataTables_filter {
            margin-bottom: 15px;
        }
        table.dataTable {
            border-collapse: collapse !important;
            margin-top: 0 !important;
            margin-bottom: 15px !important;
        }
        /* Untuk memastikan filter form tetap rapi */
        .filter-form .form-control {
            margin-bottom: 10px;
        }
        /* Badge status */
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; }
        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            min-width: 250px;
        }
    </style>
</head>
<body>
<div class="content">
    <div class="container mt-4">
        <h4 class="mb-4">Laporan Transaksi</h4>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="toast-container"></div>

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

        <form method="get" class="row mb-3 filter-form">
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
                    <option value="lunas" <?= ($status=='lunas')?'selected':'' ?>>Lunas</option>
                    <option value="pending" <?= ($status=='pending')?'selected':'' ?>>Pending</option>
                    <option value="ditolak" <?= ($status=='ditolak')?'selected':'' ?>>Ditolak</option>
                    <option value="menunggu_kasir" <?= ($status=='menunggu_kasir')?'selected':'' ?>>Menunggu Kasir</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama / kode transaksi" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table id="tabelTransaksi" class="table table-bordered table-striped" style="width:100%">
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
                    <?php if($result && $result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?= $row['pembayaran_id'] ?>">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jenjang_siswa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_paket'] ?? '-') ?> <small>(<?= ucfirst($row['tipe_paket'] ?? '-') ?>)</small></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="tanggal-cell"><?= !empty($row['tanggal']) ? date("d M Y", strtotime($row['tanggal'])) : '-' ?></td>
                                <td class="kode-cell"><?= htmlspecialchars($row['kode_unik'] ?? '-') ?></td>
                                <td class="metode-cell"><?= htmlspecialchars($row['metode'] ?? '-') ?></td>
                                <td class="status-cell">
                                   <span class="badge bg-<?= 
    ($row['status'] ?? '')=='lunas' ? 'success' : 
    (($row['status'] ?? '')=='pending' ? 'warning text-dark' : 
    (($row['status'] ?? '')=='ditolak' ? 'danger' : 
    (($row['status'] ?? '')=='menunggu_kasir' ? 'info text-dark' : 'secondary'))) 
?>">
    <?= ucfirst(str_replace('_',' ',$row['status'] ?? '-')) ?>
</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                            class="btn btn-warning btn-sm edit-btn"
                                            data-id="<?= $row['pembayaran_id'] ?>"
                                            data-kode="<?= htmlspecialchars($row['kode_unik'] ?? '') ?>"
                                            data-tanggal="<?= !empty($row['tanggal']) ? date("Y-m-d", strtotime($row['tanggal'])) : '' ?>"
                                            data-metode="<?= htmlspecialchars($row['metode'] ?? '') ?>"
                                            data-status="<?= htmlspecialchars($row['status'] ?? '') ?>"
                                            data-bs-toggle="modal" data-bs-target="#editModal">
                                            Edit
                                        </button>
                                        <a href="cetak_transaksi.php?id=<?= $row['pembayaran_id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           target="_blank">
                                            Cetak
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" action="update_transaksi.php" method="POST">
                <input type="hidden" name="pembayaran_id" id="edit_pembayaran_id">
                <input type="hidden" name="bulan_filter" value="<?= htmlspecialchars($bulan) ?>">
                <input type="hidden" name="tahun_filter" value="<?= htmlspecialchars($tahun) ?>">
                <input type="hidden" name="status_filter" value="<?= htmlspecialchars($status) ?>">
                <input type="hidden" name="search_filter" value="<?= htmlspecialchars($search) ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Kode Transaksi</label>
                        <input type="text" name="kode_unik" id="edit_kode_unik" class="form-control" required>
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
                            <option value="lunas">Lunas</option>
                            <option value="pending">Pending</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="menunggu_kasir">Menunggu Kasir</option>
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

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    $('#tabelTransaksi').DataTable({
        responsive: true,
        ordering: true,
        searching: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Handle klik tombol Edit
    $(document).on('click', '.edit-btn', function() {
        var $btn = $(this);
        $('#edit_pembayaran_id').val($btn.data('id') || '');
        $('#edit_kode_unik').val($btn.data('kode') || '');
        $('#edit_tanggal').val($btn.data('tanggal') || '');
        $('#edit_metode').val($btn.data('metode') || '');
        $('#edit_status').val($btn.data('status') || '');
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>