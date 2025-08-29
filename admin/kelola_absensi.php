<?php
include '../config/database.php';
include '../includes/admin_header.php';

// --- PROSES UPDATE ABSENSI ---
if (isset($_POST['update_absensi'])) {
    $id        = $_POST['id'];
    $siswa_id  = $_POST['siswa_id'];
    $kelas_id  = $_POST['kelas_id'];
    $paket_id  = $_POST['paket_id'];
    $mapel_id  = $_POST['mapel_id'];
    $tanggal   = $_POST['tanggal'];
    $status    = $_POST['status'];
    $catatan   = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';

    // ambil jadwal_id dari absensi_offline
    $jadwal_q = mysqli_query($conn, "SELECT jadwal_id FROM absensi_offline WHERE id = '$id'");
    $jadwal_row = mysqli_fetch_assoc($jadwal_q);
    $jadwal_id = $jadwal_row['jadwal_id'];

    // update absensi_offline
    $update_absensi = mysqli_query($conn, "
        UPDATE absensi_offline 
        SET siswa_id = '$siswa_id',
            status   = '$status',
            catatan  = '$catatan'
        WHERE id = '$id'
    ");

    // update jadwal_offline
    $update_jadwal = mysqli_query($conn, "
        UPDATE jadwal_offline 
        SET kelas_id    = '$kelas_id',
            paket_id    = '$paket_id',
            kategori_id = '$mapel_id',
            tanggal     = '$tanggal'
        WHERE id = '$jadwal_id'
    ");

    if ($update_absensi && $update_jadwal) {
        echo "<script>alert('Absensi berhasil diperbarui!');window.location='kelola_absensi.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui absensi!');</script>";
    }
}

// --- PROSES DELETE ABSENSI ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $delete = mysqli_query($conn, "DELETE FROM absensi_offline WHERE id = '$id'");
    if ($delete) {
        echo "<script>alert('Absensi berhasil dihapus!');window.location='kelola_absensi.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus absensi!');</script>";
    }
}

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

// Query dasar untuk absensi
$query_absensi = "SELECT a.*, 
                 p.nama AS paket_nama, 
                 kls.nama_kelas,
                 kls.jenjang,
                 j.tanggal AS tanggal_jadwal,
                 km.nama_kategori AS mata_pelajaran,
                 u.nama AS nama_siswa,
                 u.email,
                 u.no_hp,
                 j.kelas_id,
                 j.paket_id,
                 j.kategori_id
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

// Sorting
$query_absensi .= " ORDER BY j.tanggal DESC, a.created_at DESC";
$result_absensi = mysqli_query($conn, $query_absensi);
?>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
.main-content { margin-left: 250px; padding: 20px; background: #fff; min-height: 100vh; }
.filter-container { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.status-hadir { background-color: #28a745; color: #fff; }   /* Hijau */
.status-izin  { background-color: #ffc107; color: #000; }   /* Kuning */
.status-alpa  { background-color: #dc3545; color: #fff; }   /* Merah */

</style>

<div class="main-content">
    <h2 class="text-center mb-4">Kelola Absensi </h2>

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
        <table id="absensiTable" class="table table-bordered bg-white shadow-sm">
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
                $no = 1; 
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
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row_absensi['id']; ?>"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row_absensi['id']; ?>"><i class="fas fa-edit"></i></button>
                            <a href="?delete_id=<?= $row_absensi['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus absensi ini?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>

                    <!-- Modal Detail -->
                    <div class="modal fade" id="detailModal<?= $row_absensi['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detail Absensi</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Nama:</strong> <?= $row_absensi['nama_siswa']; ?></p>
                                    <p><strong>Email:</strong> <?= $row_absensi['email']; ?></p>
                                    <p><strong>No HP:</strong> <?= $row_absensi['no_hp']; ?></p>
                                    <p><strong>Kelas:</strong> <?= $row_absensi['nama_kelas'] ?: '-'; ?> (<?= $row_absensi['jenjang']; ?>)</p>
                                    <p><strong>Paket:</strong> <?= $row_absensi['paket_nama'] ?: '-'; ?></p>
                                    <p><strong>Mata Pelajaran:</strong> <?= $row_absensi['mata_pelajaran']; ?></p>
                                    <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($row_absensi['tanggal_jadwal'])); ?></p>
                                    <p><strong>Status:</strong> <?= ucfirst($row_absensi['status']); ?></p>
                                    <p><strong>Waktu Absen:</strong> <?= date('H:i', strtotime($row_absensi['created_at'])); ?></p>
                                    <hr>
                                    <p><strong>Total Hadir:</strong> <?= $rekap['total_hadir']; ?> kali</p>
                                    <p><strong>Total Izin:</strong> <?= $rekap['total_izin']; ?> kali</p>
                                    <p><strong>Total Alpa:</strong> <?= $rekap['total_alpa']; ?> kali</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $row_absensi['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Absensi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $row_absensi['id']; ?>">
                                        <input type="hidden" name="siswa_id" value="<?= $row_absensi['siswa_id']; ?>">

                                        <!-- Pilih Kelas -->
                                        <div class="form-group mb-2">
                                            <label>Kelas:</label>
                                            <select name="kelas_id" class="form-control" required>
                                                <?php
                                                $kelas_q = mysqli_query($conn, "SELECT id, nama_kelas, jenjang FROM kelas ORDER BY jenjang, nama_kelas");
                                                while ($k = mysqli_fetch_assoc($kelas_q)) {
                                                    $selected = ($k['id'] == $row_absensi['kelas_id']) ? 'selected' : '';
                                                    echo "<option value='{$k['id']}' $selected>{$k['nama_kelas']} ({$k['jenjang']})</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Pilih Paket -->
                                        <div class="form-group mb-2">
                                            <label>Paket:</label>
                                            <select name="paket_id" class="form-control" required>
                                                <?php
                                                $paket_q = mysqli_query($conn, "SELECT id, nama FROM paket WHERE status='aktif'");
                                                while ($p = mysqli_fetch_assoc($paket_q)) {
                                                    $selected = ($p['id'] == $row_absensi['paket_id']) ? 'selected' : '';
                                                    echo "<option value='{$p['id']}' $selected>{$p['nama']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Pilih Mata Pelajaran -->
                                        <div class="form-group mb-2">
                                            <label>Mata Pelajaran:</label>
                                            <select name="mapel_id" class="form-control" required>
                                                <?php
                                                $mapel_q = mysqli_query($conn, "SELECT id, nama_kategori FROM kategori_materi ORDER BY nama_kategori");
                                                while ($m = mysqli_fetch_assoc($mapel_q)) {
                                                    $selected = ($m['id'] == $row_absensi['kategori_id']) ? 'selected' : '';
                                                    echo "<option value='{$m['id']}' $selected>{$m['nama_kategori']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Tanggal -->
                                        <div class="form-group mb-2">
                                            <label>Tanggal:</label>
                                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d', strtotime($row_absensi['tanggal_jadwal'])); ?>" required>
                                        </div>

                                        <!-- Status -->
                                        <div class="form-group mb-2">
                                            <label>Status:</label>
                                            <select name="status" class="form-control" required>
                                                <option value="hadir" <?= ($row_absensi['status'] == 'hadir') ? 'selected' : ''; ?>>Hadir</option>
                                                <option value="izin" <?= ($row_absensi['status'] == 'izin') ? 'selected' : ''; ?>>Izin</option>
                                                <option value="alpa" <?= ($row_absensi['status'] == 'alpa') ? 'selected' : ''; ?>>Alpa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="update_absensi" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#absensiTable').DataTable({
        "ordering": true,
        "paging": true,
        "info": true,
        "lengthChange": true,
        "pageLength": 10
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>
