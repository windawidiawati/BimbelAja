<?php include '../config/database.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Verifikasi Pembayaran</title></head>
<body>
<h2>Daftar Pembayaran</h2>
<table border="1">
  <tr>
    <th>ID</th><th>User ID</th><th>Paket</th><th>Harga</th><th>Status</th><th>Tanggal</th><th>Aksi</th>
  </tr>
  <?php
  $query = "SELECT * FROM pembayaran ORDER BY tanggal DESC";
  $result = $conn->query($query);
  while ($row = $result->fetch_assoc()) {
    echo "<tr>
      <td>{$row['id']}</td>
      <td>{$row['user_id']}</td>
      <td>{$row['paket']}</td>
      <td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
      <td>{$row['status']}</td>
      <td>{$row['tanggal']}</td>
      <td>
        <a href='aksi_verifikasi.php?id={$row['id']}&aksi=lunas'>Lunas</a> |
        <a href='aksi_verifikasi.php?id={$row['id']}&aksi=ditolak'>Tolak</a>
      </td>
    </tr>";
  }
  ?>
</table>
</body>
</html>
