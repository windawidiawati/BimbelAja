<?php
require '../vendor/autoload.php'; // Require Composer's autoload if using Composer
include '../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Dompdf\Dompdf;
use Dompdf\Options;

// Get transaction ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die('Invalid transaction ID');
}

// Query to get company settings from pengaturan table
$settingsQuery = "SELECT * FROM pengaturan WHERE id = 1";
$settingsResult = mysqli_query($conn, $settingsQuery);
$settings = $settingsResult->fetch_assoc();

// Query to get transaction details
$sql = "SELECT p.id AS pembayaran_id, p.*, 
               u.nama AS nama_siswa, 
               u.email AS email_siswa, 
               u.no_hp AS nohp_siswa,
               u.jenjang AS jenjang_siswa,
               pk.nama AS nama_paket, 
               pk.durasi, 
               pk.satuan_durasi, 
               pk.jenjang AS jenjang_paket,
               pk.tipe AS tipe_paket,
               pk.harga AS harga_paket,
               pk.deskripsi AS deskripsi_paket,
               l.tanggal_mulai,
               l.tanggal_berakhir,
               l.status AS status_langganan
        FROM pembayaran p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN paket pk ON p.paket_id = pk.id
        LEFT JOIN langganan l ON p.user_id = l.user_id AND p.paket_id = l.paket_id
        WHERE p.id = $id";
$result = mysqli_query($conn, $sql);

if (!$result || $result->num_rows == 0) {
    die('Transaction not found');
}

$transaksi = $result->fetch_assoc();

// DEBUG selesai, lanjutkan proses generate PDF

// Create PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

// HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #' . $transaksi['kode_unik'] . '</title>
    <style>
        body { 
            font-family: Helvetica, Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .brand-logo {
            max-height: 60px;
            max-width: 120px;
        }
        .brand-info {
            display: flex;
            flex-direction: column;
        }
        .brand-name {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
            margin: 0;
            padding: 0;
        }
        .brand-tagline {
            font-size: 12px;
            color: #666;
            margin: 0;
            padding: 0;
        }
        .company {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-title { 
            font-size: 24px; 
            font-weight: bold; 
            text-align: center; 
            margin: 30px 0 10px 0;
            color: #0d6efd;
        }
        .invoice-subtitle {
            font-size: 14px;
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .invoice-info { 
            margin-bottom: 30px; 
            font-size: 12px; 
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .details { 
            margin-bottom: 25px; 
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            font-size: 12px;
        }
        .table th, .table td { 
            border: 1px solid #dee2e6; 
            padding: 12px; 
            text-align: left; 
        }
        .table th { 
            background-color: #0d6efd; 
            color: white; 
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .total { 
            text-align: right; 
            font-weight: bold; 
            font-size: 18px; 
            color: #0d6efd;
            margin-bottom: 30px;
        }
        .footer { 
            margin-top: 50px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .ttd { 
            margin-top: 60px; 
            text-align: right; 
            font-size: 12px; 
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 3px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
        }
        .status-verified {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="brand">
            ' . ($settings['logo_path'] ? '<img src="' . htmlspecialchars($settings['logo_path']) . '" class="brand-logo" alt="">' : '') . '
            <div class="brand-info">
                <div class="brand-name">' . htmlspecialchars($settings['nama']) . '</div>
                <div class="brand-tagline">Bimbingan Belajar Online Terpercaya</div>
            </div>
        </div>
        <div class="company">
            <strong>' . htmlspecialchars($settings['nama']) . '</strong><br>
            ' . htmlspecialchars($settings['alamat']) . '<br>
            Telp: ' . htmlspecialchars($settings['telepon']) . '<br>
            Email: ' . htmlspecialchars($settings['email']) . '
        </div>
    </div>

    <div class="invoice-title">INVOICE PEMBAYARAN</div>
    <div class="invoice-subtitle">Bimbingan Belajar Online Berkualitas</div>

    <!-- INFO -->
    <div class="invoice-info">
        <table width="100%">
            <tr>
                <td width="50%" style="vertical-align: top;">
                    <strong>Kepada:</strong><br>
                    ' . htmlspecialchars($transaksi['nama_siswa']) . '<br>
                    ' . htmlspecialchars($transaksi['email_siswa']) . '<br>
                    ' . htmlspecialchars($transaksi['nohp_siswa']) . '<br>
                    Jenjang: ' . htmlspecialchars($transaksi['jenjang_siswa']) . '
                </td>
                <td width="50%" style="text-align: right; vertical-align: top;">
                    <strong>No. Invoice:</strong> ' . htmlspecialchars($transaksi['kode_unik']) . '<br>
                    <strong>Tanggal Transaksi:</strong> ' . date('d F Y', strtotime($transaksi['tanggal'])) . '<br>
                    <strong>Status:</strong> 
                    <span class="status-badge ' . ($transaksi['status'] == 'verified' ? 'status-verified' : 'status-pending') . '">
                        ' . ucfirst($transaksi['status']) . '
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- DETAIL PAKET -->
    <div class="details">
        <h3 style="color: #0d6efd; margin-bottom: 15px;">Detail Paket Belajar</h3>
        <table class="table">
            <thead>
                <tr>
                    <th width="50%">Nama Paket</th>
                    <th width="20%">Durasi</th>
                    <th width="30%">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="color: #0d6efd;">' . htmlspecialchars($transaksi['nama_paket']) . '</strong><br>
                        <small style="color: #666;">' . htmlspecialchars($transaksi['deskripsi_paket']) . '</small>
                    </td>
                    <td>' . $transaksi['durasi'] . ' ' . $transaksi['satuan_durasi'] . '</td>
                    <td>Rp ' . number_format($transaksi['harga'], 0, ',', '.') . '</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- INFO LANGANAN -->
    ' . (isset($transaksi['tanggal_mulai']) ? '
    <div class="info-box">
        <strong>Info Langganan:</strong><br>
        Periode: ' . date('d M Y', strtotime($transaksi['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($transaksi['tanggal_berakhir'])) . '<br>
        Status Langganan: ' . ucfirst($transaksi['status_langganan']) . '
    </div>
    ' : '') . '

    <!-- TOTAL -->
    <div class="total">
        Total Pembayaran: Rp ' . number_format($transaksi['harga'], 0, ',', '.') . '
    </div>

    <!-- TTD -->
    <div class="ttd">
        Jakarta, ' . date('d F Y') . '<br>
        Hormat Kami,<br><br><br><br>
        <strong>' . htmlspecialchars($settings['nama']) . '</strong><br>
        <em>Admin System</em>
    </div>

    <div class="footer">
        Terima kasih telah mempercayai ' . htmlspecialchars($settings['nama']) . ' untuk pendidikan yang lebih baik.<br>
        Invoice ini sah dan diproses secara elektronik oleh sistem ' . htmlspecialchars($settings['nama']) . '.<br>
        © ' . date('Y') . ' ' . htmlspecialchars($settings['nama']) . ' - All Rights Reserved
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the generated PDF
$dompdf->stream('invoice_' . $transaksi['kode_unik'] . '.pdf', ['Attachment' => true]);
?>