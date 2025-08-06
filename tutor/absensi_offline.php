<?php
include '../includes/auth.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];
$kelas_id = isset($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;
$paket_id = isset($_GET['paket_id']) ? intval($_GET['paket_id']) : 0;
$jadwal_id = isset($_GET['jadwal_id']) ? intval($_GET['jadwal_id']) : 0;

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

// Ambil daftar jadwal offline (per kelas) 
// Tambahkan filter NOT EXISTS agar jadwal yang sudah diabsen semua tidak muncul
$jadwal_list = [];
$mapel_nama = '';
if ($kelas_id > 0) {
    $jadwal_res = mysqli_query($conn, "
        SELECT jo.id, jo.tanggal, jo.jam_mulai, jo.jam_selesai, km.nama_kategori AS mapel
        FROM jadwal_offline jo
        LEFT JOIN kategori_materi km ON jo.kategori_id = km.id
        WHERE jo.tutor_id = '$tutor_id' 
        AND jo.kelas_id = '$kelas_id'
        " . ($paket_id > 0 ? " AND jo.paket_id = '$paket_id'" : "") . "

          AND NOT EXISTS (
              SELECT 1
              FROM absensi_offline ao
              JOIN users u ON ao.siswa_id = u.id
              WHERE ao.jadwal_id = jo.id
                AND u.role = 'siswa'
                AND u.kelas = '$kelas_nama'
          )
        ORDER BY jo.tanggal ASC, jo.jam_mulai ASC
    ");
    while ($row = mysqli_fetch_assoc($jadwal_res)) {
        $jadwal_list[] = $row;
        if ($row['id'] == $jadwal_id) {
            $mapel_nama = $row['mapel'];
        }
    }
}

// Proses update absensi
if (isset($_POST['update_absensi']) && $jadwal_id > 0) {
    foreach ($_POST['status'] as $siswa_id => $status) {
        $status = mysqli_real_escape_string($conn, $status);
        $cek = mysqli_query($conn, "SELECT id FROM absensi_offline WHERE jadwal_id='$jadwal_id' AND siswa_id='$siswa_id'");
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE absensi_offline SET status='$status' WHERE jadwal_id='$jadwal_id' AND siswa_id='$siswa_id'");
        } else {
            mysqli_query($conn, "INSERT INTO absensi_offline (jadwal_id, siswa_id, status) VALUES ('$jadwal_id', '$siswa_id', '$status')");
        }
    }
    echo "<script>alert('Absensi berhasil disimpan'); window.location='absensi_offline.php?kelas_id=$kelas_id';</script>";
    exit;
}

$siswa_result = [];
if ($kelas_id > 0 && $jadwal_id > 0 && $paket_id > 0) {
    // $siswa_query = mysqli_query($conn, "
    //     SELECT u.id, u.nama, u.jenjang,
    //     COALESCE(ao.status, 'alpa') AS status
    //     FROM users u
    //     LEFT JOIN absensi_offline ao ON ao.siswa_id = u.id AND ao.jadwal_id = '$jadwal_id'
    //     JOIN langganan l ON l.user_id = u.id
    //     WHERE u.role = 'siswa'
    //       AND u.kelas = '$kelas_nama'
    //       AND l.paket_id = '$paket_id'
    //     ORDER BY u.nama ASC
    // ");
    $siswa_query = mysqli_query($conn, "
    SELECT u.id, u.nama, u.jenjang,
    COALESCE(ao.status, 'alpa') AS status
    FROM users u
    LEFT JOIN absensi_offline ao ON ao.siswa_id = u.id AND ao.jadwal_id = '$jadwal_id'
    JOIN langganan l ON l.user_id = u.id AND l.paket_id = '$paket_id'
    WHERE u.role = 'siswa'
      AND u.kelas = '$kelas_nama'
    ORDER BY u.nama ASC
");

    while ($row = mysqli_fetch_assoc($siswa_query)) {
        $siswa_result[] = $row;
    }
}


include '../includes/tutor_header.php';
?>

<div class="content p-4">
    <div class="card shadow-sm p-4 mb-5">
        <h3 class="mb-4 fw-bold text-primary">📋 Absensi Offline</h3>

        <!-- Pilih Kelas -->
        <form method="GET" class="mb-3">
            <label class="form-label">Pilih Kelas:</label>
            <select name="kelas_id" class="form-select w-50" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                <?php while ($k = mysqli_fetch_assoc($kelas_list)) { ?>
                    <option value="<?= $k['id']; ?>" <?= ($kelas_id == $k['id']) ? 'selected' : ''; ?>>
                        <?= $k['nama_kelas']; ?> (<?= $k['jenjang']; ?>)
                    </option>
                <?php } ?>
            </select>
        </form>

            <!-- <label class="form-label">Pilih Paket:</label>
        <select name="paket_id" class="form-select w-50" onchange="this.form.submit()">
            <option value="">-- Semua Paket --</option>
            <?php while ($p = mysqli_fetch_assoc($paket_list)) { ?>
                <option value="<?= $p['id']; ?>" <?= ($paket_id == $p['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($p['nama']) ?>
                </option>
            <?php } ?>
        </select>
            </form> -->

            <!-- Pilih Paket -->
<form method="GET" class="mb-3">
    <input type="hidden" name="kelas_id" value="<?= $kelas_id; ?>">
    <label class="form-label">Pilih Paket:</label>
    <select name="paket_id" class="form-select w-50" onchange="this.form.submit()">
        <option value="">-- Pilih Paket --</option>
        <?php mysqli_data_seek($paket_list, 0); while ($p = mysqli_fetch_assoc($paket_list)) { ?>
            <option value="<?= $p['id']; ?>" <?= ($paket_id == $p['id']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($p['nama']) ?>
            </option>
        <?php } ?>
    </select>
</form>


        <!-- Pilih Jadwal -->
        <?php if ($kelas_id > 0 && !empty($jadwal_list)) { ?>
            <form method="GET" class="mb-4">
    <input type="hidden" name="kelas_id" value="<?= $kelas_id; ?>">
    <input type="hidden" name="paket_id" value="<?= $paket_id; ?>">
    <label class="form-label">Pilih Jadwal:</label>
    <select name="jadwal_id" class="form-select w-50" onchange="this.form.submit()">
        <option value="">-- Pilih Jadwal --</option>
        <?php foreach ($jadwal_list as $j) { ?>
            <option value="<?= $j['id']; ?>" <?= ($jadwal_id == $j['id']) ? 'selected' : ''; ?>>
                <?= date('d M Y', strtotime($j['tanggal'])) . " (" . $j['jam_mulai'] . " - " . $j['jam_selesai'] . ") - " . $j['mapel']; ?>
            </option>
        <?php } ?>
    </select>
</form>

        <?php } elseif ($kelas_id > 0) { ?>
            <div class="alert alert-info">Tidak ada jadwal yang perlu diabsen untuk kelas ini.</div>
        <?php } ?>

        <!-- Tabel Absensi -->
        <?php if ($jadwal_id > 0) { ?>
            <?php if (empty($siswa_result)) { ?>
                <div class="alert alert-warning">Belum ada siswa di kelas ini.</div>
            <?php } else { ?>
                <form method="POST" onsubmit="return confirm('Yakin simpan absensi?');">
                    <a href="export_absensi_excel.php?kelas_id=<?= $kelas_id; ?>&jadwal_id=<?= $jadwal_id; ?>" 
                       class="btn btn-outline-success w-100 mb-3">
                       <i class="bi bi-file-earmark-excel"></i> Export ke Excel
                    </a>
                    <table class="table table-bordered text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Jenjang</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($siswa_result as $siswa) {
                                echo "<tr>
                                        <td>{$no}</td>
                                        <td>{$siswa['nama']}</td>
                                        <td>{$siswa['jenjang']}</td>
                                        <td>{$kelas_nama}</td>
                                        <td>{$mapel_nama}</td>
                                        <td>
                                            <select name='status[{$siswa['id']}]' class='form-select'>
                                                <option value='hadir' " . ($siswa['status'] == 'hadir' ? 'selected' : '') . ">Hadir</option>
                                                <option value='izin' " . ($siswa['status'] == 'izin' ? 'selected' : '') . ">Izin</option>
                                                <option value='alpa' " . ($siswa['status'] == 'alpa' ? 'selected' : '') . ">Alpa</option>
                                            </select>
                                        </td>
                                    </tr>";
                                $no++;
                            }
                            ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-warning w-100 mb-2" onclick="setAllHadir()">Tandai Semua Hadir</button>
                    <button type="submit" name="update_absensi" class="btn btn-success w-100">
                        <i class="bi bi-save"></i> Simpan Absensi
                    </button>
                </form>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<script>
function setAllHadir() {
    document.querySelectorAll('select[name^="status"]').forEach(sel => sel.value = 'hadir');
}
</script>

<?php include '../includes/tutor_footer.php'; ?>
