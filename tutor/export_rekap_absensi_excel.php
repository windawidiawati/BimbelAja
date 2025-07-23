<?php
include '../includes/auth.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];
$kelas_id = isset($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;

$kelas_nama = '';
$kelas_res = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id='$kelas_id'");
if ($kelas_res && mysqli_num_rows($kelas_res) > 0) {
    $kelas_row = mysqli_fetch_assoc($kelas_res);
    $kelas_nama = $kelas_row['nama_kelas'];
}

// Ambil data rekap
$siswa_query = mysqli_query($conn, "
    SELECT u.nama, u.jenjang,
        SUM(CASE WHEN ao.status = 'hadir' THEN 1 ELSE 0 END) AS jml_hadir,
        SUM(CASE WHEN ao.status = 'izin' THEN 1 ELSE 0 END) AS jml_izin,
        SUM(CASE WHEN ao.status = 'alpa' THEN 1 ELSE 0 END) AS jml_alpa
    FROM users u
    LEFT JOIN absensi_offline ao ON ao.siswa_id = u.id
    LEFT JOIN jadwal_offline jo ON ao.jadwal_id = jo.id
    WHERE u.role = 'siswa' AND u.kelas = '$kelas_nama' AND jo.tutor_id = '$tutor_id'
    GROUP BY u.id
    ORDER BY u.nama ASC
");

$filename = "rekap_absensi_{$kelas_nama}_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

echo "No\tNama Siswa\tJenjang\tHadir\tIzin\tAlpa\n";
$no = 1;
while ($row = mysqli_fetch_assoc($siswa_query)) {
    echo $no++ . "\t" . $row['nama'] . "\t" . $row['jenjang'] . "\t" . $row['jml_hadir'] . "\t" . $row['jml_izin'] . "\t" . $row['jml_alpa'] . "\n";
}
exit;
?>
