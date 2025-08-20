<?php
include '../config/database.php';
include '../includes/admin_header.php';

// Ambil filter dari URL
$kelas_id   = $_GET['kelas_id'] ?? '';
$paket_id   = $_GET['paket_id'] ?? '';
$mapel_id   = $_GET['mapel_id'] ?? '';
$tanggal    = $_GET['tanggal'] ?? '';

// Ambil data dropdown filter
$kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");
$paket = mysqli_query($conn, "SELECT * FROM paket ORDER BY nama");
$mapel = mysqli_query($conn, "SELECT * FROM kategori_materi ORDER BY nama_kategori");

// Bangun query filter
$where = "WHERE 1=1 ";
if ($kelas_id) $where .= "AND k.id = '$kelas_id' ";
if ($paket_id) $where .= "AND pk.id = '$paket_id' ";
if ($mapel_id) $where .= "AND km.id = '$mapel_id' ";
if ($tanggal)  $where .= "AND DATE(j.tanggal) = '$tanggal' ";

// Query utama
$sql = "
SELECT 
    s.nama AS nama_siswa, 
    k.nama_kelas, 
    pk.nama AS nama_paket, 
    km.nama_kategori AS nama_mapel,
    COUNT(CASE WHEN a.status = 'hadir' THEN 1 END) AS total_hadir,
    COUNT(CASE WHEN a.status = 'izin' THEN 1 END) AS total_izin,
    COUNT(CASE WHEN a.status = 'alpa' THEN 1 END) AS total_alpa,
    j.tanggal
FROM absensi_offline a
JOIN users s ON a.siswa_id = s.id AND s.role = 'siswa'
JOIN jadwal_offline j ON a.jadwal_id = j.id
JOIN kelas k ON j.kelas_id = k.id
JOIN paket pk ON j.paket_id = pk.id
JOIN kategori_materi km ON j.kategori_id = km.id
$where
GROUP BY s.nama, k.nama_kelas, pk.nama, km.nama_kategori, j.tanggal
ORDER BY s.nama, j.tanggal
";

$result = mysqli_query($conn, $sql);
?>

<div class="content">
    <div class="container mt-4">
        <h4 class="mb-4">Laporan Absensi Per Pertemuan</h4>

        <!-- Filter -->
        <form method="get" class="row mb-3">
            <div class="col-md-2">
                <select name="kelas_id" class="form-control">
                    <option value="">Semua Kelas</option>
                    <?php while($row = mysqli_fetch_assoc($kelas)): ?>
                        <option value="<?= $row['id'] ?>" <?= $kelas_id==$row['id']?'selected':'' ?>>
                            <?= $row['nama_kelas'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="paket_id" class="form-control">
                    <option value="">Semua Paket</option>
                    <?php while($row = mysqli_fetch_assoc($paket)): ?>
                        <option value="<?= $row['id'] ?>" <?= $paket_id==$row['id']?'selected':'' ?>>
                            <?= $row['nama'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="mapel_id" class="form-control">
                    <option value="">Semua Mapel</option>
                    <?php while($row = mysqli_fetch_assoc($mapel)): ?>
                        <option value="<?= $row['id'] ?>" <?= $mapel_id==$row['id']?'selected':'' ?>>
                            <?= $row['nama_kategori'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="tanggal" class="form-control" value="<?= $tanggal ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
            </div>
            <div class="col-md-2">
                <a href="cetak_absensi.php?kelas_id=<?= $kelas_id ?>&paket_id=<?= $paket_id ?>&mapel_id=<?= $mapel_id ?>&tanggal=<?= $tanggal ?>" 
                   class="btn btn-success w-100" target="_blank">Cetak</a>
            </div>
        </form>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Paket</th>
                        <th>Mapel</th>
                        <th>Tanggal Pertemuan</th>
                        <th>Total Hadir</th>
                        <th>Total Izin</th>
                        <th>Total Alpa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0):
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                            <td><?= htmlspecialchars($row['nama_mapel']) ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['total_hadir'] ?></td>
                            <td><?= $row['total_izin'] ?></td>
                            <td><?= $row['total_alpa'] ?></td>
                        </tr>
                    <?php endwhile;
                    else: ?>
                        <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
