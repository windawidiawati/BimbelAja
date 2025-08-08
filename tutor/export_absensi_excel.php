<?php
include '../config/database.php';

$kelas_id = isset($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;
$jadwal_id = isset($_GET['jadwal_id']) ? intval($_GET['jadwal_id']) : 0;

// Ambil nama kelas
$kelas_nama = '-';
$kelas_query = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id = '$kelas_id'");
if ($kelas_data = mysqli_fetch_assoc($kelas_query)) {
    $kelas_nama = $kelas_data['nama_kelas'];
}

// Ambil data jadwal
$jadwal_info = '-';
$jadwal_query = mysqli_query($conn, "SELECT tanggal, jam_mulai, jam_selesai FROM jadwal_offline WHERE id = '$jadwal_id'");
if ($jadwal_data = mysqli_fetch_assoc($jadwal_query)) {
    $tanggal = date('d-m-Y', strtotime($jadwal_data['tanggal']));
    $jam_mulai = substr($jadwal_data['jam_mulai'], 0, 5);
    $jam_selesai = substr($jadwal_data['jam_selesai'], 0, 5);
    $jadwal_info = "$tanggal | $jam_mulai - $jam_selesai";
}

// Header untuk file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=absensi_offline_kelas_$kelas_id.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Tampilkan judul
echo "<table border='0' cellpadding='3'>";
echo "<tr><td colspan='4'><strong>Data Absensi Offline</strong></td></tr>";
echo "<tr><td colspan='4'>Kelas: <strong>$kelas_nama</strong></td></tr>";
echo "<tr><td colspan='4'>Jadwal: <strong>$jadwal_info</strong></td></tr>";
echo "<tr><td colspan='4'></td></tr>"; // Spasi
echo "</table>";

// Tabel data absensi
echo "<table border='1'>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Jenjang</th>
            <th>Status</th>
        </tr>";

$query = mysqli_query($conn, "
    SELECT u.nama, u.jenjang, COALESCE(ao.status, 'alpa') AS status
    FROM users u
    LEFT JOIN absensi_offline ao ON ao.siswa_id = u.id AND ao.jadwal_id = '$jadwal_id'
    WHERE u.role = 'siswa' AND u.kelas_id = '$kelas_id'
    ORDER BY u.nama ASC
");

$no = 1;
while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
            <td>$no</td>
            <td>{$row['nama']}</td>
            <td>{$row['jenjang']}</td>
            <td>{$row['status']}</td>
          </tr>";
    $no++;
}

echo "</table>";
exit;
