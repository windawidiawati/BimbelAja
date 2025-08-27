<?php
include '../includes/auth.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];
$jadwal_id = isset($_GET['jadwal_id']) ? intval($_GET['jadwal_id']) : 0;

// Ambil daftar jadwal tutor
$jadwal_list = mysqli_query($conn, "
    SELECT jo.id, jo.tanggal, jo.jam_mulai, jo.jam_selesai, 
           k.nama_kelas, km.nama_kategori
    FROM jadwal_offline jo
    LEFT JOIN kelas k ON jo.kelas_id = k.id
    LEFT JOIN kategori_materi km ON jo.kategori_id = km.id
    WHERE jo.tutor_id = '$tutor_id'
    ORDER BY jo.tanggal ASC, jo.jam_mulai ASC
");

// Ambil detail jadwal terpilih
$jadwal_detail = null;
if ($jadwal_id > 0) {
    $res = mysqli_query($conn, "
        SELECT jo.*, k.nama_kelas, km.nama_kategori 
        FROM jadwal_offline jo
        LEFT JOIN kelas k ON jo.kelas_id = k.id
        LEFT JOIN kategori_materi km ON jo.kategori_id = km.id
        WHERE jo.id = '$jadwal_id' AND jo.tutor_id = '$tutor_id'
    ");
    $jadwal_detail = mysqli_fetch_assoc($res);
}

// Simpan absensi tutor
if (isset($_POST['simpan_absensi']) && $jadwal_id > 0) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $tanggal = $jadwal_detail['tanggal'];

    // Cek apakah absensi sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM absensi_tutor WHERE jadwal_id='$jadwal_id' AND tutor_id='$tutor_id'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE absensi_tutor SET status='$status' WHERE jadwal_id='$jadwal_id' AND tutor_id='$tutor_id'");
    } else {
        mysqli_query($conn, "INSERT INTO absensi_tutor (jadwal_id, tutor_id, status, tanggal) 
                             VALUES ('$jadwal_id','$tutor_id','$status','$tanggal')");
    }

    echo "<script>alert('Absensi tutor berhasil disimpan!'); window.location='absensi_tutor.php';</script>";
    exit;
}

include '../includes/tutor_header.php';
?>

<div class="content p-4">
    <div class="card shadow-sm p-4 mb-4">
        <h3 class="mb-3 fw-bold text-primary">📋 Absensi Tutor</h3>

        <!-- Pilih Jadwal -->
        <form method="GET" class="mb-4">
            <label class="form-label">Pilih Jadwal:</label>
            <select name="jadwal_id" class="form-select w-50" onchange="this.form.submit()">
                <option value="">-- Pilih Jadwal --</option>
                <?php while ($j = mysqli_fetch_assoc($jadwal_list)) { ?>
                    <option value="<?= $j['id']; ?>" <?= ($jadwal_id == $j['id']) ? 'selected' : ''; ?>>
                        <?= date('d M Y', strtotime($j['tanggal'])) . " | " . $j['jam_mulai'] . "-" . $j['jam_selesai'] . " | " . $j['nama_kelas'] . " - " . $j['nama_kategori']; ?>
                    </option>
                <?php } ?>
            </select>
        </form>

        <!-- Form Absensi -->
        <?php if ($jadwal_detail) { ?>
            <div class="card p-3 shadow-sm mb-3">
                <p><strong>Kelas:</strong> <?= htmlspecialchars($jadwal_detail['nama_kelas']); ?></p>
                <p><strong>Mata Pelajaran:</strong> <?= htmlspecialchars($jadwal_detail['nama_kategori']); ?></p>
                <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($jadwal_detail['tanggal'])); ?></p>
                <p><strong>Jam:</strong> <?= $jadwal_detail['jam_mulai'] . " - " . $jadwal_detail['jam_selesai']; ?></p>
            </div>

            <form method="POST" onsubmit="return confirm('Yakin simpan absensi tutor?');">
                <label class="form-label">Pilih Status Kehadiran:</label>
              <select name="status" class="form-control" required>
                <option value="">-- Pilih Status Kehadiran --</option>
                <option value="Hadir">Hadir</option>
                <option value="Izin">Izin</option>
                <option value="Alpha">Alpha</option>
            </select>

                <button type="submit" name="simpan_absensi" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan Absensi
                </button>
            </form>
        <?php } elseif ($jadwal_id > 0) { ?>
            <div class="alert alert-danger">❌ Jadwal tidak ditemukan.</div>
        <?php } ?>
    </div>
</div>

<?php include '../includes/tutor_footer.php'; ?>
