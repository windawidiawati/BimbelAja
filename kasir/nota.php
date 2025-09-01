<?php
require '../vendor/autoload.php';  // sesuaikan path autoload composer
use Dompdf\Dompdf;

include '../config/database.php';

// Ambil user_id dari parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    die("ID user tidak valid");
}

// Ambil data user
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);

// Ambil pembayaran terbaru
$bayar_query = mysqli_query($conn, "SELECT * FROM pembayaran WHERE user_id = $user_id ORDER BY tanggal DESC LIMIT 1");
$pembayaran = mysqli_fetch_assoc($bayar_query);

if (!$user || !$pembayaran) {
    die("Data tidak ditemukan");
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Nota Pembayaran</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #2E86C1;
        }
        .subtitle {
            font-size: 14px;
            margin-top: 4px;
            color: #555;
        }
        .info, .invoice-info {
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f7f7f7;
        }
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 40px;
        }
        .kode-unik {
            font-size: 16px;
            font-weight: bold;
            color: #C0392B;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">NOTA PEMBAYARAN</div>
        <div class="subtitle">Bimbingan Belajar Online</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <th>Nama Siswa</th>
                <td>' . htmlspecialchars($user['nama']) . '</td>
            </tr>
            <tr>
                <th>Username</th>
                <td>' . htmlspecialchars($user['username']) . '</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>' . htmlspecialchars($user['email']) . '</td>
            </tr>
            <tr>
                <th>No HP</th>
                <td>' . htmlspecialchars($user['no_hp']) . '</td>
            </tr>
            <tr>
                <th>Kode Transaksi</th>
                <td>' . htmlspecialchars($pembayaran['kode_unik']) . '</td>
            </tr>
        </table>
    </div>

    <div class="invoice-info">
        <table>
            <thead>
                <tr>
                    <th>Paket</th>
                    <th>Harga</th>
                    <th>Metode Pembayaran</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . htmlspecialchars($pembayaran['paket']) . '</td>
                    <td>Rp ' . number_format($pembayaran['harga'], 0, ",", ".") . '</td>
                    <td>' . htmlspecialchars($pembayaran['metode']) . '</td>
                    <td>' . htmlspecialchars($pembayaran['status']) . '</td>
                    <td>' . htmlspecialchars($pembayaran['tanggal']) . '</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="total">
        Total: Rp ' . number_format($pembayaran['harga'], 0, ",", ".") . '
    </div>

    <div class="footer">
        Terima kasih telah menggunakan layanan kami.<br/>
        Nota ini sah dan diproses oleh sistem.
    </div>
</body>
</html>
';

// Inisialisasi Dompdf dan buat PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF ke browser (inline)
$dompdf->stream("nota_pembayaran_" . $user['nama'] . ".pdf", array("Attachment" => false));
exit;
?>
