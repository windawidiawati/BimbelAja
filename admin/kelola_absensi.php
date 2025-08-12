<?php
include '../config/database.php';
include '../includes/admin_header.php';

// Ambil data kelas, paket, dan jadwal untuk filter
$query_kelas = "SELECT * FROM kelas ORDER BY jenjang, nama_kelas";
$result_kelas = mysqli_query($conn, $query_kelas);

$query_paket = "SELECT * FROM paket WHERE status = 'aktif'";
$result_paket = mysqli_query($conn, $query_paket);

$query_jadwal = "SELECT j.id, j.tanggal, k.nama_kategori 
                 FROM jadwal_offline j 
                 JOIN kategori_materi k ON j.kategori_id = k.id 
                 ORDER BY j.tanggal DESC";
$result_jadwal = mysqli_query($conn, $query_jadwal);

// Filter data
$kelas_filter = isset($_POST['kelas']) ? $_POST['kelas'] : '';
$paket_filter = isset($_POST['paket']) ? $_POST['paket'] : '';
$jadwal_filter = isset($_POST['jadwal']) ? $_POST['jadwal'] : '';
$tanggal_filter = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';

// Paginasi
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query dasar untuk absensi
$query_absensi = "SELECT a.*, 
                 p.nama AS paket_nama, 
                 kls.nama_kelas,
                 kls.jenjang,
                 j.tanggal AS tanggal_jadwal,
                 km.nama_kategori AS mata_pelajaran,
                 u.nama AS nama_siswa,
                 u.email,
                 u.no_hp
                 FROM absensi_offline a 
                 LEFT JOIN jadwal_offline j ON a.jadwal_id = j.id
                 LEFT JOIN kategori_materi km ON j.kategori_id = km.id
                 LEFT JOIN paket p ON j.paket_id = p.id
                 LEFT JOIN kelas kls ON j.kelas_id = kls.id
                 LEFT JOIN users u ON a.siswa_id = u.id";

$where_clause = [];
if ($kelas_filter) $where_clause[] = "j.kelas_id = '$kelas_filter'";
if ($paket_filter) $where_clause[] = "j.paket_id = '$paket_filter'";
if ($jadwal_filter) $where_clause[] = "a.jadwal_id = '$jadwal_filter'";
if ($tanggal_filter) $where_clause[] = "j.tanggal = '$tanggal_filter'";

if (!empty($where_clause)) {
    $query_absensi .= " WHERE " . implode(" AND ", $where_clause);
}

// Hitung total data
$total_query = "SELECT COUNT(*) as total FROM ($query_absensi) AS total_query";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

// Sorting dan limit
$query_absensi .= " ORDER BY j.tanggal DESC, a.created_at DESC LIMIT $limit OFFSET $offset";
$result_absensi = mysqli_query($conn, $query_absensi);
?>

<style>
.main-content { margin-left: 250px; padding: 20px; background: #fff; min-height: 100vh; }
.filter-container { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.table-container { overflow-x: auto; }
table.table { min-width: 1000px; }
.status-hadir { background-color: #d4edda; }
.status-izin { background-color: #fff3cd; }
.status-alpa { background-color: #f8d7da; }
</style>

<div class="main-content">
    <h2 class="text-center mb-4">Kelola Absensi Offline</h2>

    <!-- Filter -->
    <form method="POST" action="" class="filter-container">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="kelas">Kelas:</label>
                <select name="kelas" id="kelas" class="form-control">
                    <option value="">Semua Kelas</option>
                    <?php while ($row_kelas = mysqli_fetch_assoc($result_kelas)) { ?>
                        <option value="<?= $row_kelas['id']; ?>" <?= ($kelas_filter == $row_kelas['id']) ? 'selected' : ''; ?>>
                            <?= $row_kelas['nama_kelas']; ?> (<?= $row_kelas['jenjang']; ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="paket">Paket:</label>
                <select name="paket" id="paket" class="form-control">
                    <option value="">Semua Paket</option>
                    <?php mysqli_data_seek($result_paket, 0); while ($row_paket = mysqli_fetch_assoc($result_paket)) { ?>
                        <option value="<?= $row_paket['id']; ?>" <?= ($paket_filter == $row_paket['id']) ? 'selected' : ''; ?>>
                            <?= $row_paket['nama']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="jadwal">Jadwal:</label>
                <select name="jadwal" id="jadwal" class="form-control">
                    <option value="">Semua Jadwal</option>
                    <?php mysqli_data_seek($result_jadwal, 0); while ($row_jadwal = mysqli_fetch_assoc($result_jadwal)) { ?>
                        <option value="<?= $row_jadwal['id']; ?>" <?= ($jadwal_filter == $row_jadwal['id']) ? 'selected' : ''; ?>>
                            <?= date('d M Y', strtotime($row_jadwal['tanggal'])); ?> - <?= $row_jadwal['nama_kategori']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="tanggal">Tanggal:</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= $tanggal_filter; ?>">
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="form-group col-md-12 text-center">
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="kelola_absensi.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered bg-white shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>NO</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Paket</th>
                    <th>Mata Pelajaran</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Waktu Absen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = $offset + 1; 
                while ($row_absensi = mysqli_fetch_assoc($result_absensi)) { 
                    $status_class = 'status-' . $row_absensi['status'];

                    // Hitung total hadir/izin/alpa siswa ini
                    $rekap_query = "SELECT 
                        SUM(status = 'hadir') AS total_hadir,
                        SUM(status = 'izin') AS total_izin,
                        SUM(status = 'alpa') AS total_alpa
                        FROM absensi_offline WHERE siswa_id = '{$row_absensi['siswa_id']}'";
                    $rekap_result = mysqli_query($conn, $rekap_query);
                    $rekap = mysqli_fetch_assoc($rekap_result);
                ?>
                    <tr class="<?= $status_class; ?>">
                        <td><?= $no++; ?></td>
                        <td><?= $row_absensi['nama_siswa']; ?></td>
                        <td><?= $row_absensi['nama_kelas'] ?: '-'; ?></td>
                        <td><?= $row_absensi['paket_nama'] ?: '-'; ?></td>
                        <td><?= $row_absensi['mata_pelajaran']; ?></td>
                        <td><?= date('d M Y', strtotime($row_absensi['tanggal_jadwal'])); ?></td>
                        <td><?= ucfirst($row_absensi['status']); ?></td>
                        <td><?= date('H:i', strtotime($row_absensi['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?= $row_absensi['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal<?= $row_absensi['id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="delete_absensi.php?id=<?= $row_absensi['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus absensi ini?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Modal Detail -->
                    <div class="modal fade" id="detailModal<?= $row_absensi['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detail Siswa</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Nama:</strong> <?= $row_absensi['nama_siswa']; ?></p>
                                    <p><strong>Email:</strong> <?= $row_absensi['email']; ?></p>
                                    <p><strong>No HP:</strong> <?= $row_absensi['no_hp']; ?></p>
                                    <hr>
                                    <p><strong>Total Hadir:</strong> <?= $rekap['total_hadir']; ?> kali</p>
                                    <p><strong>Total Izin:</strong> <?= $rekap['total_izin']; ?> kali</p>
                                    <p><strong>Total Alpa:</strong> <?= $rekap['total_alpa']; ?> kali</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                            <select name="status" class="form-control" required>
                                                <option value="hadir" <?= ($row_absensi['status'] == 'hadir') ? 'selected' : ''; ?>>Hadir</option>
                                                <option value="izin" <?= ($row_absensi['status'] == 'izin') ? 'selected' : ''; ?>>Izin</option>
                                                <option value="alpa" <?= ($row_absensi['status'] == 'alpa') ? 'selected' : ''; ?>>Alpa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1) { ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?= $page-1; ?>">&laquo;</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php } ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?= $page+1; ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
    <?php } ?>
</div>

<?php include '../includes/admin_footer.php'; ?>
