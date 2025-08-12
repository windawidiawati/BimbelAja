<?php
require '../vendor/autoload.php'; // pastikan PHPSpreadsheet sudah di-install via Composer
include '../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$kelas_filter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$paket_filter = isset($_GET['paket']) ? $_GET['paket'] : '';

// Ambil data absensi
$query = "SELECT a.*, p.nama AS paket_nama, k.nama_kelas 
          FROM absensi_offline a 
          LEFT JOIN paket p ON a.paket_id = p.id 
          LEFT JOIN kelas k ON p.kelas = k.nama_kelas";

if ($kelas_filter || $paket_filter) {
    $query .= " WHERE 1=1";
    if ($kelas_filter) $query .= " AND k.id = '$kelas_filter'";
    if ($paket_filter) $query .= " AND a.paket_id = '$paket_filter'";
}

$result = mysqli_query($conn, $query);

// Buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->fromArray([
    'No', 'Jadwal ID', 'Paket', 'Siswa ID', 'Status', 'Catatan', 'Tanggal'
], NULL, 'A1');

// Isi data
$rowNumber = 2;
$no = 1;
while ($data = mysqli_fetch_assoc($result)) {
    $sheet->fromArray([
        $no++,
        $data['jadwal_id'],
        $data['paket_nama'],
        $data['siswa_id'],
        ucfirst($data['status']),
        $data['catatan'],
        $data['created_at']
    ], NULL, 'A' . $rowNumber++);
}

// Unduh sebagai file Excel
$filename = "data_absensi.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
