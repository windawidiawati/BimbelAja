<?php
require '../vendor/autoload.php'; // Require Composer's autoload if using Composer
include '../config/database.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil data absensi tutor
$query_absensi = "SELECT at.*, u.nama AS nama_tutor, jo.tanggal AS tanggal_jadwal, 
                  jo.jam_mulai, jo.jam_selesai, km.nama_kategori 
                  FROM absensi_tutor at 
                  JOIN users u ON at.tutor_id = u.id 
                  JOIN jadwal_offline jo ON at.jadwal_id = jo.id 
                  JOIN kategori_materi km ON jo.kategori_id = km.id 
                  ORDER BY at.tanggal DESC";
$result_absensi = $conn->query($query_absensi);

// Buat HTML untuk tabel
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Tutor</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: .25em .4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
            color: #fff;
        }
        .bg-success { background-color: #198754; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <h1>Laporan Absensi Tutor</h1>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Tutor</th>
                <th>Jadwal</th>
                <th>Mata Pelajaran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

if ($result_absensi->num_rows > 0) {
    $no = 1;
    while ($row = $result_absensi->fetch_assoc()) {
        $status_class = '';
        if ($row['status'] == 'Hadir') {
            $status_class = 'bg-success';
        } elseif ($row['status'] == 'Izin') {
            $status_class = 'bg-warning';
        } else {
            $status_class = 'bg-danger';
        }

        $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
                    <td>' . $row['nama_tutor'] . '</td>
                    <td>' . date('d/m/Y', strtotime($row['tanggal_jadwal'])) . ' (' . $row['jam_mulai'] . ' - ' . $row['jam_selesai'] . ')</td>
                    <td>' . $row['nama_kategori'] . '</td>
                    <td><span class="badge ' . $status_class . '">' . $row['status'] . '</span></td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" class="text-center">Tidak ada data absensi tutor</td></tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Inisialisasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');

// Render HTML menjadi PDF
$dompdf->render();

// Streaming PDF ke browser
$dompdf->stream("laporan_absensi_tutor.pdf", ["Attachment" => false]);
?>