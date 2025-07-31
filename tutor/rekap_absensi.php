<?php
include '../includes/auth.php';
include '../includes/tutor_header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];
$kelas_id = isset($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;
$paket_id = isset($_GET['paket_id']) ? intval($_GET['paket_id']) : 0;
$filter_bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : 0;
$filter_tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Ambil daftar kelas
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY jenjang, nama_kelas");

// Ambil daftar paket
$paket_list = mysqli_query($conn, "SELECT * FROM paket ORDER BY nama");

// Ambil nama kelas
$kelas_nama = '';
if ($kelas_id > 0) {
    $kelas_res = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id='$kelas_id'");
    if ($kelas_res && mysqli_num_rows($kelas_res) > 0) {
        $kelas_row = mysqli_fetch_assoc($kelas_res);
        $kelas_nama = $kelas_row['nama_kelas'];
    }
}

$rekap = [];
if ($kelas_id > 0 && $paket_id > 0) {
    $where_bulan = '';
    if ($filter_bulan > 0) {
        $where_bulan = "AND MONTH(ao.created_at) = '$filter_bulan' AND YEAR(ao.created_at) = '$filter_tahun'";
    }

    $siswa_query = mysqli_query($conn, "
        SELECT u.id, u.nama, u.jenjang,
            SUM(CASE WHEN ao.status = 'hadir' THEN 1 ELSE 0 END) AS jml_hadir,
            SUM(CASE WHEN ao.status = 'izin' THEN 1 ELSE 0 END) AS jml_izin,
            SUM(CASE WHEN ao.status = 'alpa' THEN 1 ELSE 0 END) AS jml_alpa
        FROM users u
        JOIN langganan l ON l.user_id = u.id AND l.paket_id = '$paket_id'
        LEFT JOIN absensi_offline ao ON ao.siswa_id = u.id
        LEFT JOIN jadwal_offline jo ON ao.jadwal_id = jo.id
        WHERE u.role = 'siswa' 
        AND u.kelas = '$kelas_nama' 
        AND jo.tutor_id = '$tutor_id'
        $where_bulan
        GROUP BY u.id
        ORDER BY u.nama ASC
    ");

    while ($row = mysqli_fetch_assoc($siswa_query)) {
        $rekap[] = $row;
    }
}
?>

<div class="content">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 fw-bold text-primary">📊 Rekap Absensi</h3>

        <form method="GET" class="mb-3 row g-3">
            <div class="col-md-3">
                <label class="form-label">Pilih Kelas:</label>
                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    <?php while ($k = mysqli_fetch_assoc($kelas_list)) { ?>
                        <option value="<?= $k['id']; ?>" <?= ($kelas_id == $k['id']) ? 'selected' : ''; ?>>
                            <?= $k['nama_kelas']; ?> (<?= $k['jenjang']; ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Pilih Paket:</label>
                <select name="paket_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Paket --</option>
                    <?php mysqli_data_seek($paket_list, 0); while ($p = mysqli_fetch_assoc($paket_list)) { ?>
                        <option value="<?= $p['id']; ?>" <?= ($paket_id == $p['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Bulan:</label>
                <select name="bulan" class="form-select">
                    <option value="">-- Semua Bulan --</option>
                    <?php
                    $bulan_indonesia = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    foreach ($bulan_indonesia as $num => $nama) {
                        echo "<option value=\"$num\"" . ($filter_bulan == $num ? ' selected' : '') . ">{$nama}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Tahun:</label>
                <input type="number" name="tahun" value="<?= $filter_tahun; ?>" class="form-control">
            </div>

            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <?php if ($kelas_id > 0 && $paket_id > 0 && !empty($rekap)) { ?>
            <a href="export_rekap_absensi_excel.php?kelas_id=<?= $kelas_id; ?>&paket_id=<?= $paket_id; ?>&bulan=<?= $filter_bulan; ?>&tahun=<?= $filter_tahun; ?>" class="btn btn-outline-success w-100 mb-3">
                <i class="bi bi-file-earmark-excel"></i> Export Rekap ke Excel
            </a>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Jenjang</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($rekap as $s) {
                            echo "<tr>
                                    <td>{$no}</td>
                                    <td>{$s['nama']}</td>
                                    <td>{$s['jenjang']}</td>
                                    <td>{$s['jml_hadir']}</td>
                                    <td>{$s['jml_izin']}</td>
                                    <td>{$s['jml_alpa']}</td>
                                  </tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php } elseif ($kelas_id > 0 && $paket_id > 0) { ?>
            <div class="alert alert-warning">Belum ada data absensi untuk kelas dan paket ini.</div>
        <?php } ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
