<?php
include '../config/database.php';
include '../includes/admin_header.php';

$error = '';

// Query join langganan + users + paket + kelas
$sql = "SELECT l.id AS langganan_id, l.tanggal_mulai, l.tanggal_berakhir, l.status,
               u.id AS user_id, u.nama, u.email, u.jenjang, u.kelas_id,
               k.nama_kelas,
               p.nama AS nama_paket, p.durasi, p.satuan_durasi, p.tipe
        FROM langganan l
        LEFT JOIN users u ON l.user_id = u.id
        LEFT JOIN kelas k ON u.kelas_id = k.id
        LEFT JOIN paket p ON l.paket_id = p.id
        ORDER BY l.tanggal_mulai DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Query error: " . mysqli_error($conn);
}
?>

<div class="content">
  <div class="container mt-4">
    <h4 class="mb-4">Data Siswa & Langganan</h4>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered" id="siswaTable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Email</th>
            <th>Jenjang</th>
            <th>Kelas</th>
            <th>Paket</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Berakhir</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if($result && $result->num_rows > 0): $no=1; ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['jenjang'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_paket'] ?? '-') ?> 
                    <small>(<?= ucfirst($row['tipe'] ?? '-') ?>)</small>
                </td>
                <td><?= !empty($row['tanggal_mulai']) ? date("d M Y", strtotime($row['tanggal_mulai'])) : '-' ?></td>
                <td><?= !empty($row['tanggal_berakhir']) ? date("d M Y", strtotime($row['tanggal_berakhir'])) : '-' ?></td>
                <td>
                  <span class="badge bg-<?= 
                    ($row['status'] ?? '')=='aktif' ? 'success' : 
                    (($row['status'] ?? '')=='nonaktif' ? 'secondary' : 'warning') 
                  ?>">
                    <?= ucfirst($row['status'] ?? '-') ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  $('#siswaTable').DataTable({
    "pageLength": 10,
    "lengthMenu": [10, 25, 50, 100],
    "ordering": true,
    "searching": true
  });
});
</script>

<?php include '../includes/admin_footer.php'; ?>  
