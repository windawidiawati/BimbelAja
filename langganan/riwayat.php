<?php
session_start();
include '../config/database.php';
$title = "Riwayat Siswa";
include '../includes/siswa_header_langganan.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$q = "SELECT p.id as pembayaran_id, 
             pk.nama, pk.durasi, pk.satuan_durasi, 
             l.tanggal_mulai, l.tanggal_berakhir,
             p.harga, p.kode_unik, p.metode, p.tanggal, p.status
      FROM pembayaran p
      JOIN paket pk ON p.paket_id = pk.id
      LEFT JOIN langganan l ON l.user_id = p.user_id AND l.paket_id = p.paket_id
      WHERE p.user_id = ?
      ORDER BY p.tanggal DESC";

$stmt = mysqli_prepare($conn, $q);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-4">
  <h4>Riwayat Langganan</h4>
  <table class="table table-bordered mt-3">
    <thead class="table-light">
  <tr>
    <th>No</th>
    <th>Nama Paket</th>
    <th>Harga</th>
    <th>Metode</th>
    <th>Tanggal Mulai</th>
    <th>Tanggal Berakhir</th>
    <th>Durasi</th>
    <th>Status</th>
  </tr>
</thead>
    <tbody>
      <?php
          $no = 1;
          while ($row = mysqli_fetch_assoc($result)) {
              $badge = 'secondary';
              if ($row['status'] === 'pending') $badge = 'warning';
              elseif ($row['status'] === 'approved') $badge = 'success';
              elseif ($row['status'] === 'rejected') $badge = 'danger';

              echo "<tr>";
              echo "<td>" . $no++ . "</td>";
              echo "<td>" . htmlspecialchars($row['nama']) . "</td>";

              $total_harga = (int)$row['harga'] + (int)$row['kode_unik'];
              echo "<td>Rp" . number_format($total_harga, 0, ',', '.') . "</td>";

              echo "<td>" . htmlspecialchars($row['metode']) . "</td>";

              // tanggal mulai & selesai (kalau ada di langganan)
              echo "<td>" . ($row['tanggal_mulai'] ? date('d M Y', strtotime($row['tanggal_mulai'])) : '-') . "</td>";
              echo "<td>" . ($row['tanggal_berakhir'] ? date('d M Y', strtotime($row['tanggal_berakhir'])) : '-') . "</td>";


              // durasi
              echo "<td>" . $row['durasi'] . " " . $row['satuan_durasi'] . "</td>";

              echo "<td><span class='badge bg-$badge'>" . htmlspecialchars($row['status']) . "</span></td>";
              echo "</tr>";
          }
          ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
