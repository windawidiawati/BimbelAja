<?php
ob_start();
session_start();
include '../config/database.php';
include '../includes/admin_header.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit;
}

// Ambil data pembayaran + user (tanpa LIMIT karena DataTables yg atur)
$query = "SELECT p.*, u.username, u.nama 
          FROM pembayaran p 
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.tanggal DESC";
$result = $conn->query($query);

// Proses hapus pembayaran
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM pembayaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: verifikasi_pembayaran.php");
    exit;
}

// Proses update data pembayaran
if (isset($_POST['edit_status']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $paket = $_POST['paket'];
    $harga = str_replace(['.', 'Rp', ' '], '', $_POST['harga']); // normalisasi angka

    $stmt = $conn->prepare("UPDATE pembayaran SET paket = ?, harga = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sisi", $paket, $harga, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: verifikasi_pembayaran.php");
    exit;
}
?>

<div class="content px-3 pt-3">
    <div class="card shadow">
        <div class="card-header bg-white">
            <h3 class="card-title mb-0"><i class="bi bi-credit-card me-2"></i> Data Verifikasi Pembayaran</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="pembayaranTable" class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['paket']) ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td class="status-<?= $row['status'] ?>"><?= strtoupper($row['status']) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-edit"
                                            data-id="<?= $row['id'] ?>"
                                            data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                            data-username="<?= htmlspecialchars($row['username']) ?>"
                                            data-paket="<?= htmlspecialchars($row['paket']) ?>"
                                            data-harga="<?= number_format($row['harga'], 0, ',', '.') ?>"
                                            data-status="<?= $row['status'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal">
                                            Edit
                                        </button>
                                        <a href="?action=delete&id=<?= $row['id'] ?>"
                                           onclick="return confirm('Yakin ingin menghapus pembayaran ini?')"
                                           class="btn btn-sm btn-danger">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">Tidak ada data pembayaran</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label for="edit-nama" class="form-label">Nama</label>
                    <input type="text" id="edit-nama" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="edit-username" class="form-label">Username</label>
                    <input type="text" id="edit-username" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="edit-paket" class="form-label">Paket</label>
                    <input type="text" name="paket" id="edit-paket" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit-harga" class="form-label">Harga (Rp)</label>
                    <input type="text" name="harga" id="edit-harga" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit-status" class="form-label">Status</label>
                    <select name="status" id="edit-status" class="form-select" required>
                        <option value="pending">Pending</option>
                        <option value="lunas">Lunas</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_status" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#pembayaranTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "order": [[6, "desc"]], // Kolom tanggal paling baru di atas
    });

    // Modal edit data
    $(document).on('click', '.btn-edit', function() {
        $('#edit-id').val($(this).data('id'));
        $('#edit-nama').val($(this).data('nama'));
        $('#edit-username').val($(this).data('username'));
        $('#edit-paket').val($(this).data('paket'));
        $('#edit-harga').val($(this).data('harga'));
        $('#edit-status').val($(this).data('status'));
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>
