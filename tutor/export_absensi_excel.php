<?php
include '../includes/auth.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];
$kelas_id = isset($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;
$jadwal_id = isset($_GET['jadwal_id']) ? intval($_GET['jadwal_id']) : 0;

if ($kelas_id <= 0 || $jadwal_id <= 0) {
    die("Parameter kelas_id dan jadwal_id tidak valid!");
}

// Ambil nama kelas & mapel
$kelas_nama = '';
$mapel_nama = '';
$kelas_res = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id='$kelas_id'");
if ($kelas_res && mysqli_num_rows($kelas_res) > 0) {
    $kelas_row = mysqli_fetch_assoc($kelas_res);
    $kelas_nama = $kelas_row['nama_kelas'];
}
$mapel_res = mysqli_query($conn, "
    SELECT km.nama_kategori AS mapel 
    FROM jadwal_offline jo
    LEFT JOIN kategori_materi km ON jo.kategori_id = km.id
    WHERE jo.id = '$jadwal_id'
");
if ($mapel_res && mysqli_num_rows($mapel_res) > 0) {
    $mapel_row = mysqli_fetch_assoc($mapel_res);
    $mapel_nama = $mapel_row['mapel'];
}

// Ambil data absensi
$siswa_result = [];
$siswa_query = mysqli_query($conn, "
    SELECT u.nama, u.jenjang,
        COALESCE(ao.status, 'alpa') AS status
    FROM users u
    LEFT JOIN absensi_offline ao ON ao.siswa_id = u.id AND ao.jadwal_id = '$jadwal_id'
    WHERE u.role = 'siswa' AND u.kelas = '$kelas_nama'
    ORDER BY u.nama ASC
");

while ($row = mysqli_fetch_assoc($siswa_query)) {
    $siswa_result[] = $row;
}

// Set header untuk Excel
$filename = "absensi_{$kelas_nama}_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// Cetak data
echo "No\tNama Siswa\tJenjang\tKelas\tMata Pelajaran\tStatus\n";
$no = 1;
foreach ($siswa_result as $siswa) {
    echo $no++ . "\t" . $siswa['nama'] . "\t" . $siswa['jenjang'] . "\t" . $kelas_nama . "\t" . $mapel_nama . "\t" . $siswa['status'] . "\n";
}
exit;
?>
