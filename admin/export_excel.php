<?php
require_once '../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_transaksi_".date('Ymd').".xls");

// Ambil parameter filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query data dengan filter
$query = "SELECT p.*, u.nama as nama_siswa, pk.nama as nama_paket 
          FROM pembayaran p
          JOIN users u ON p.user_id = u.id
          JOIN paket pk ON p.paket_id = pk.id
          WHERE 1=1";

if (!empty($bulan)) {
    $query .= " AND MONTH(p.tanggal) = '$bulan'";
}
if (!empty($tahun)) {
    $query .= " AND YEAR(p.tanggal) = '$tahun'";
}
if (!empty($status)) {
    $query .= " AND p.status = '$status'";
}
if (!empty($search)) {
    $query .= " AND (u.nama LIKE '%$search%' OR p.kode_bayar LIKE '%$search%')";
}

$query .= " ORDER BY p.tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<table border="1">
    <tr>
        <th colspan="8" style="text-align: center; font-size: 16px;">LAPORAN TRANSAKSI BIMBEL</th>
    </tr>
    <tr>
        <th colspan="8" style="text-align: center;">Periode: 
            <?= !empty($bulan) ? date('F', mktime(0, 0, 0, $bulan, 1)) : 'Semua Bulan' ?> 
            <?= !empty($tahun) ? $tahun : 'Semua Tahun' ?>
        </th>
    </tr>
    <tr>
        <th>No</th>
        <th>Kode Transaksi</th>
        <th>Tanggal</th>
        <th>Siswa</th>
        <th>Paket</th>
        <th>Harga</th>
        <th>Metode</th>
        <th>Status</th>
    </tr>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['kode_bayar'] ?? 'N/A' ?></td>
        <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
        <td><?= $row['nama_siswa'] ?></td>
        <td><?= $row['nama_paket'] ?></td>
        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
        <td><?= ucfirst($row['metode']) ?></td>
        <td><?= ucfirst($row['status']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>