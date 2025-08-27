<?php
include '../config/database.php';
include '../includes/admin_header.php';

// Ambil data kelas dan paket untuk filter
$query_kelas = "SELECT DISTINCT kelas FROM paket WHERE status = 'aktif'";
$result_kelas = mysqli_query($conn, $query_kelas);

$query_paket = "SELECT * FROM paket WHERE status = 'aktif'";
$result_paket = mysqli_query($conn, $query_paket);

// Filter data
$kelas_filter = isset($_POST['kelas']) ? $_POST['kelas'] : '';
$paket_filter = isset($_POST['paket']) ? $_POST['paket'] : '';

// Paginasi
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$total_query = "SELECT COUNT(*) as total FROM absensi_offline a 
                LEFT JOIN paket p ON a.paket_id = p.id";
if ($kelas_filter || $paket_filter) {
    $total_query .= " WHERE 1=1";
    if ($kelas_filter) $total_query .= " AND p.kelas = '$kelas_filter'";
    if ($paket_filter) $total_query .= " AND a.paket_id = '$paket_filter'";
}
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

// Ambil data absensi
$query_absensi = "SELECT a.*, p.nama AS paket_nama 
                  FROM absensi_offline a 
                  LEFT JOIN paket p ON a.paket_id = p.id";
if ($kelas_filter || $paket_filter) {
    $query_absensi .= " WHERE 1=1";
    if ($kelas_filter) $query_absensi .= " AND p.kelas = '$kelas_filter'";
    if ($paket_filter) $query_absensi .= " AND a.paket_id = '$paket_filter'";
}
$query_absensi .= " LIMIT $limit OFFSET $offset";
$result_absensi = mysqli_query($conn, $query_absensi);
?>

<style>
    .main-content {
        margin-left: 250px; /* lebar sidebar */
        padding: 20px;
        background: #fff;
        min-height: 100vh;
    }
    .filter-container {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .table-container {
        overflow-x: auto;
    }
    table.table {
        min-width: 900px; /* supaya kolom rapi */
    }
</style>

<div class="main-content">
    <h2 class="text-center mb-4">Kelola Absensi</h2>

    <form method="POST" action="" class="filter-container">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="kelas">Pilih Kelas:</label>
                <select name="kelas" id="kelas" class="form-control">
                    <option value="">Semua Kelas</option>
                    <?php while ($row_kelas = mysqli_fetch_assoc($result_kelas)) { ?>
                        <option value="<?= $row_kelas['kelas']; ?>" <?= ($kelas_filter == $row_kelas['kelas']) ? 'selected' : ''; ?>>
                            <?= $row_kelas['kelas']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-5">
                <label for="paket">Pilih Paket:</label>
                <select name="paket" id="paket" class="form-control">
                    <option value="">Semua Paket</option>
                    <?php while ($row_paket = mysqli_fetch_assoc($result_paket)) { ?>
                        <option value="<?= $row_paket['id']; ?>" <?= ($paket_filter == $row_paket['id']) ? 'selected' : ''; ?>>
                            <?= $row_paket['nama']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>

    <div class="table-container">
        <table class="table table-bordered text-center">
            <thead class="thead-light">
                <tr>
                    <th>NO</th>
                    <th>Jadwal </th>
                    <th>Paket</th>
                    <th>Siswa </th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_absensi = mysqli_fetch_assoc($result_absensi)) { ?>
                    <tr>
                        <td><?= $row_absensi['id']; ?></td>
                        <td><?= $row_absensi['jadwal_id']; ?></td>
                        <td><?= $row_absensi['paket_nama']; ?></td>
                        <td><?= $row_absensi['siswa_id']; ?></td>
                        <td><?= ucfirst($row_absensi['status']); ?></td>
                        <td><?= $row_absensi['catatan']; ?></td>
                        <td><?= $row_absensi['created_at']; ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $row_absensi['id']; ?>">Edit</button>
                            <a href="delete_absensi.php?id=<?= $row_absensi['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus?');">Hapus</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $row_absensi['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="update_absensi.php">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Absensi</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $row_absensi['id']; ?>">
                                        <div class="form-group">
                                            <label>Status:</label>
                                            <select name="status" class="form-control">
                                                <option value="hadir" <?= ($row_absensi['status'] == 'hadir') ? 'selected' : ''; ?>>Hadir</option>
                                                <option value="izin" <?= ($row_absensi['status'] == 'izin') ? 'selected' : ''; ?>>Izin</option>
                                                <option value="alpa" <?= ($row_absensi['status'] == 'alpa') ? 'selected' : ''; ?>>Alpa</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Catatan:</label>
                                            <textarea name="catatan" class="form-control"><?= $row_absensi['catatan']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Paginasi -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?= $i; ?>&kelas=<?= $kelas_filter; ?>&paket=<?= $paket_filter; ?>"><?= $i; ?></a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>

<?php include '../includes/admin_footer.php'; ?>
