<?php
require '../vendor/autoload.php';
include '../config/database.php';

use Dompdf\Dompdf;

// Ambil filter dari URL
$kelas_id   = $_GET['kelas_id'] ?? '';
$paket_id   = $_GET['paket_id'] ?? '';
$mapel_id   = $_GET['mapel_id'] ?? '';
$tanggal    = $_GET['tanggal'] ?? '';

// Bangun query filter
$where = "WHERE 1=1 ";
if ($kelas_id) $where .= "AND k.id = '$kelas_id' ";
if ($paket_id) $where .= "AND pk.id = '$paket_id' ";
if ($mapel_id) $where .= "AND km.id = '$mapel_id' ";
if ($tanggal)  $where .= "AND DATE(j.tanggal) = '$tanggal' ";

// Query data
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

// Buat HTML untuk PDF
$html = '
<h2 style="text-align:center;">Laporan Absensi Per Pertemuan</h2>';
if ($tanggal) {
    $html .= '<p style="text-align:center;">Tanggal Pertemuan: '.date('d-m-Y', strtotime($tanggal)).'</p>';
}
$html .= '
<table border="1" cellspacing="0" cellpadding="6" width="100%">
    <thead>
        <tr style="background:#f2f2f2; text-align:center;">
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
    <tbody>';

if (mysqli_num_rows($result) > 0) {
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>
            <td align="center">'.$no++.'</td>
            <td>'.$row['nama_siswa'].'</td>
            <td>'.$row['nama_kelas'].'</td>
            <td>'.$row['nama_paket'].'</td>
            <td>'.$row['nama_mapel'].'</td>
            <td align="center">'.date('d-m-Y', strtotime($row['tanggal'])).'</td>
            <td align="center">'.$row['total_hadir'].'</td>
            <td align="center">'.$row['total_izin'].'</td>
            <td align="center">'.$row['total_alpa'].'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="9" align="center">Tidak ada data</td></tr>';
}

$html .= '
    </tbody>
</table>';

// Inisialisasi Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// Setting ukuran kertas & orientasi
$dompdf->setPaper('A4', 'portrait');

// Render ke PDF
$dompdf->render();

// Output ke browser
$dompdf->stream("laporan_absensi.pdf", array("Attachment" => false));
exit;
