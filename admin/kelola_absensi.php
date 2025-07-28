<?php
include '../includes/admin_header.php';
include '../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle filter input
$kelas_filter = $_GET['kelas'] ?? '';
$tanggal_filter = $_GET['tanggal'] ?? '';
$mapel_filter = $_GET['mapel'] ?? '';

// Ambil data filter (dropdown)
$kelasList = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
$mapelList = $conn->query("SELECT * FROM mapel ORDER BY nama_mapel ASC");

// Query absensi
$query = "
SELECT 
    ao.id,
    s.nama AS nama_siswa,
    k.nama_kelas,
    m.nama_mapel,
    j.tanggal,
    j.jam_mulai,
    j.jam_selesai,
    ao.status,
    ao.catatan
FROM absensi_offline ao
JOIN siswa s ON ao.siswa_id = s.id
JOIN jadwal j ON ao.jadwal_id = j.id
JOIN kelas k ON j.kelas_id = k.id
JOIN mapel m ON j.mapel_id = m.id
WHERE 1 = 1
";

$query = "
SELECT 
    ao.id,
    s.nama AS nama_siswa,
    k.nama_kelas,
    m.nama_mapel,
    j.tanggal,
    j.jam_mulai,
    j.jam_selesai,
    ao.status,
    ao.catatan
FROM absensi_offline ao
JOIN siswa s ON ao.siswa_id = s.id
JOIN jadwal j ON ao.jadwal_id = j.id
JOIN kelas k ON j.kelas_id = k.id
JOIN mapel m ON j.mapel_id = m.id
WHERE 1=1
";

if (!empty($kelas_filter)) {
    $kelas_filter = $conn->real_escape_string($kelas_filter);
    $query .= " AND k.id = '$kelas_filter'";
}
if (!empty($tanggal_filter)) {
    $tanggal_filter = $conn->real_escape_string($tanggal_filter);
    $query .= " AND j.tanggal = '$tanggal_filter'";
}
if (!empty($mapel_filter)) {
    $mapel_filter = $conn->real_escape_string($mapel_filter);
    $query .= " AND m.id = '$mapel_filter'";
}

$query .= " ORDER BY ao.created_at DESC";

$result = $conn->query($query);

?>


<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Kelola Absensi Siswa</h2>

            <!-- Filter -->
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <select name="kelas" class="form-select">
                        <option value="">Pilih Kelas</option>
                        <?php while ($k = $kelasList->fetch_assoc()) : ?>
                            <option value="<?= $k['id'] ?>" <?= $kelas_filter == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal_filter) ?>">
                </div>
                <div class="col-md-3">
                    <select name="mapel" class="form-select">
                        <option value="">Pilih Mata Pelajaran</option>
                        <?php while ($m = $mapelList->fetch_assoc()) : ?>
                            <option value="<?= $m['id'] ?>" <?= $mapel_filter == $m['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nama_mapel']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="kelola_absensi.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <!-- Tabel Absensi -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0) : $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_mapel']) ?></td>
                                    <td><?= $row['tanggal'] ?></td>
                                    <td><?= $row['jam_mulai'] ?> - <?= $row['jam_selesai'] ?></td>
                                    <td><span class="badge bg-<?= $row['status'] == 'hadir' ? 'success' : ($row['status'] == 'izin' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span></td>
                                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                                    <td>
                                        <a href="edit_absensi.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus_absensi.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr><td colspan="9" class="text-center">Tidak ada data absensi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
