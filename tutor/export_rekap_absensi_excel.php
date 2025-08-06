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
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('m');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Ambil nama kelas
$kelas_nama = '';
$res_kelas = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id='$kelas_id'");
if ($res_kelas && mysqli_num_rows($res_kelas) > 0) {
    $kelas_row = mysqli_fetch_assoc($res_kelas);
    $kelas_nama = $kelas_row['nama_kelas'];
}

// Ambil nama paket
$paket_nama = '';
$res_paket = mysqli_query($conn, "SELECT nama FROM paket WHERE id='$paket_id'");
if ($res_paket && mysqli_num_rows($res_paket) > 0) {
    $paket_row = mysqli_fetch_assoc($res_paket);
    $paket_nama = $paket_row['nama'];
}

// Ambil data siswa dan rekap absensi
$siswa_query = mysqli_query($conn, "
    SELECT u.nama, u.jenjang,
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
        AND MONTH(ao.created_at) = '$bulan'
        AND YEAR(ao.created_at) = '$tahun'
    GROUP BY u.id
    ORDER BY u.nama ASC
");

// Nama file
$bulan_nama = date('F', mktime(0, 0, 0, $bulan, 10)); // contoh: July
$filename = "rekap_absensi_{$kelas_nama}_{$paket_nama}_{$bulan_nama}_{$tahun}.xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// Output HTML table
echo "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h3 style='text-align: center;'>REKAP ABSENSI SISWA</h3>
    <p><strong>Kelas:</strong> {$kelas_nama}</p>
    <p><strong>Paket:</strong> {$paket_nama}</p>
    <p><strong>Bulan:</strong> {$bulan_nama} {$tahun}</p>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Jenjang</th>
            <th>Hadir</th>
            <th>Izin</th>
            <th>Alpa</th>
        </tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($siswa_query)) {
    echo "
        <tr>
            <td>{$no}</td>
            <td>{$row['nama']}</td>
            <td>{$row['jenjang']}</td>
            <td>{$row['jml_hadir']}</td>
            <td>{$row['jml_izin']}</td>
            <td>{$row['jml_alpa']}</td>
        </tr>";
    $no++;
}

echo "
    </table>
</body>
</html>
";
exit;
?>
