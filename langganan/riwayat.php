<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$q = "SELECT * FROM pembayaran WHERE user_id = ? ORDER BY tanggal DESC";
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
        <th>Tanggal</th>
        <th>Status</th>
        <th>Bukti</th>
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
          echo "<td>" . htmlspecialchars($row['paket']) . "</td>";
          $total_harga = (int)$row['harga'] + (int)$row['kode_unik'];
            echo "<td>Rp" . number_format($total_harga, 0, ',', '.') . "</td>";

          echo "<td>" . htmlspecialchars($row['metode']) . "</td>";
          echo "<td>" . date('d M Y', strtotime($row['tanggal'])) . "</td>";
          echo "<td><span class='badge bg-$badge'>" . htmlspecialchars($row['status']) . "</span></td>";
          echo "<td>";
          if ($row['bukti_transfer']) {
              echo "<a href='../uploads/bukti_transfer/" . $row['bukti_transfer'] . "' target='_blank' class='btn btn-sm btn-primary'>Lihat</a>";
          } else {
              echo "-";
          }
          echo "</td>";
          echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>
