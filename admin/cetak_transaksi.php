<?php
require '../vendor/autoload.php'; // Require Composer's autoload if using Composer
include '../config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Get transaction ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die('Invalid transaction ID');
}

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
    <title>Invoice #' . $transaksi['kode_bayar'] . '</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .invoice-title { font-size: 24px; font-weight: bold; }
        .invoice-info { margin-bottom: 30px; }
        .details { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; font-size: 18px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="invoice-title">INVOICE PEMBAYARAN</div>
        <div>Bimbingan Belajar Online</div>
    </div>

    <div class="invoice-info">
        <table width="100%">
            <tr>
                <td width="50%">
                    <strong>Kepada:</strong><br>
                    ' . htmlspecialchars($transaksi['nama_siswa']) . '<br>
                    ' . htmlspecialchars($transaksi['email_siswa']) . '<br>
                    ' . htmlspecialchars($transaksi['nohp_siswa']) . '<br>
                    Jenjang: ' . htmlspecialchars($transaksi['jenjang_siswa']) . '
                </td>
                <td width="50%" style="text-align:right">
                    <strong>No. Invoice:</strong> ' . htmlspecialchars($transaksi['kode_bayar']) . '<br>
                    <strong>Tanggal:</strong> ' . date('d F Y', strtotime($transaksi['tanggal'])) . '<br>
                    <strong>Status:</strong> ' . ucfirst($transaksi['status']) . '
                </td>
            </tr>
        </table>
    </div>

    <div class="details">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Pake</th>
                    <th>Durasi</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>' . htmlspecialchars($transaksi['nama_paket']) . '</strong><br>
                        <small>' . htmlspecialchars($transaksi['deskripsi_paket']) . '</small>
                    </td>
                    <td>' . $transaksi['durasi'] . ' ' . $transaksi['satuan_durasi'] . '</td>
                    <td>Rp ' . number_format($transaksi['harga'], 0, ',', '.') . '</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="total">
        Total: Rp ' . number_format($transaksi['harga'], 0, ',', '.') . '
    </div>

    <div class="footer">
        Terima kasih telah menggunakan layanan kami.<br>
        Invoice ini sah dan diproses oleh sistem.
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the generated PDF
$dompdf->stream('invoice_' . $transaksi['kode_bayar'] . '.pdf', ['Attachment' => true]);
?>