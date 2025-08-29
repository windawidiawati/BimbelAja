<?php
include '../config/database.php';
include '../includes/admin_header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$edit_id = $_GET['edit'] ?? null;
$highlight_id = $_GET['highlight'] ?? null; // ID materi yg baru diupdate
$new_id = $_GET['new'] ?? null; // ID materi yang baru dibuat
$edit_data = null;
if ($edit_id) {
  $stmt = mysqli_prepare($conn, "SELECT * FROM materi WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $edit_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $edit_data = mysqli_fetch_assoc($result);
}
?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Kelola Materi</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Materi</button>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
      <?php 
        $successMessages = [
          'tambah' => 'Materi berhasil ditambahkan!',
          'edit' => 'Materi berhasil diedit!',
          'setujui' => 'Materi berhasil disetujui!',
          'tolak' => 'Materi berhasil ditolak!',
          'hapus' => 'Materi berhasil dihapus!'
        ];
        echo htmlspecialchars($successMessages[$_GET['success']] ?? 'Operasi berhasil!');
      ?>
    </div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
      <?php 
        $errorMessages = [
          'tambah' => 'Gagal menambahkan materi. Silakan coba lagi.',
          'edit' => 'Gagal mengedit materi.',
          'setujui' => 'Gagal menyetujui materi.',
          'tolak' => 'Gagal menolak materi.',
          'hapus' => 'Gagal menghapus materi.',
          'file' => 'Format file tidak valid. Hanya PDF dan video yang diperbolehkan.',
          'ukuran' => 'Ukuran file terlalu besar. Maksimal 10MB.'
        ];
        echo htmlspecialchars($errorMessages[$_GET['error']] ?? 'Terjadi kesalahan. Silakan coba lagi.');
      ?>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered bg-white shadow-sm" id="materiTable">
      <thead class="table-primary">
        <tr>
          <th>Judul</th>
          <th>Deskripsi</th>
          <th>Kategori</th>
          <th>Kelas</th>
          <th>Paket</th>
          <th>File</th>
          <th>Tipe</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Ambil semua data (query tidak diubah)
        $query = "
          SELECT m.*, k.nama_kategori, kl.nama_kelas, p.nama AS nama_paket
          FROM materi m
          LEFT JOIN kategori_materi k ON m.kategori_id = k.id
          LEFT JOIN kelas kl ON m.kelas_id = kl.id
          LEFT JOIN paket p ON m.paket_id = p.id
          ORDER BY m.created_at DESC";
        $result = mysqli_query($conn, $query);

        // siapkan container
        $rows = [];
        $newRow = null;
        $highlightRow = null;

        if ($result === false) {
            // tampilkan pesan error ringan (development). Hapus/ubah jika sudah production.
            echo '<tr><td colspan="9" class="text-danger">Query error: ' . htmlspecialchars(mysqli_error($conn)) . '</td></tr>';
        } else {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // jika ada new_id (create) taruh terpisah
                    if ($new_id && (string)$row['id'] === (string)$new_id) {
                        $newRow = $row;
                    } elseif ($highlight_id && (string)$row['id'] === (string)$highlight_id) {
                        // jika ada highlight_id (update) taruh terpisah
                        $highlightRow = $row;
                    } else {
                        $rows[] = $row;
                    }
                }

                // fungsi kecil untuk menampilkan ringkasan deskripsi
                function short_desc($text, $len = 80) {
                    $safe = htmlspecialchars($text);
                    if (mb_strlen($safe) > $len) {
                        return mb_substr($safe, 0, $len) . '...';
                    }
                    return $safe;
                }

                // render data baru (jika ada) paling atas — hijau
                if (!empty($newRow)) {
                    ?>
                    <tr class="table-success">
                      <td><?= htmlspecialchars($newRow['judul']) ?></td>
                      <td><?= short_desc($newRow['deskripsi'], 80) ?></td>
                      <td><?= htmlspecialchars($newRow['nama_kategori'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($newRow['nama_kelas'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($newRow['nama_paket'] ?: '-') ?></td>
                      <td>
                        <?php if (!empty($newRow['file'])): ?>
                        <a href="../assets/uploads/<?= htmlspecialchars($newRow['file']) ?>" target="_blank" class="text-primary">
                          <?= ($newRow['tipe_file'] ?? '') === 'video' ? 'Tonton Video' : 'Lihat File' ?>
                        </a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(ucfirst($newRow['tipe_file'] ?? '-')) ?></td>
                      <td>
                        <span class="badge
                          <?= ($newRow['status'] ?? '') == 'diterima' ? 'bg-success' 
                              : (($newRow['status'] ?? '') == 'ditolak' ? 'bg-danger' 
                              : 'bg-warning text-dark') ?>">
                          <?= !empty($newRow['status']) ? htmlspecialchars(ucfirst($newRow['status'])) : 'Diproses' ?>
                        </span>
                      </td>
                      <td>
                        <div class="btn-group" role="group" aria-label="Aksi">
                          <a href="?edit=<?= (int)$newRow['id'] ?>" class="btn btn-warning btn-sm" title="Edit">✏</a>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$newRow['id'] ?>">
                            <button name="setujui" class="btn btn-success btn-sm" title="Setujui" onclick="return confirm('Setujui materi ini?')">✔</button>
                            <input type="hidden" name="status" value="diterima">
                          </form>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$newRow['id'] ?>">
                            <button name="tolak" class="btn btn-danger btn-sm" title="Tolak" onclick="return confirm('Tolak materi ini?')">✖</button>
                          </form>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$newRow['id'] ?>">
                            <button name="hapus" class="btn btn-secondary btn-sm" title="Hapus" onclick="return confirm('Hapus materi ini?')">🗑</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                    <?php
                }

                // render highlight (update) di bawah newRow — kuning
                if (!empty($highlightRow)) {
                    ?>
                    <tr class="table-warning">
                      <td><?= htmlspecialchars($highlightRow['judul']) ?></td>
                      <td><?= short_desc($highlightRow['deskripsi'], 80) ?></td>
                      <td><?= htmlspecialchars($highlightRow['nama_kategori'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($highlightRow['nama_kelas'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($highlightRow['nama_paket'] ?: '-') ?></td>
                      <td>
                        <?php if (!empty($highlightRow['file'])): ?>
                        <a href="../assets/uploads/<?= htmlspecialchars($highlightRow['file']) ?>" target="_blank" class="text-primary">
                          <?= ($highlightRow['tipe_file'] ?? '') === 'video' ? 'Tonton Video' : 'Lihat File' ?>
                        </a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(ucfirst($highlightRow['tipe_file'] ?? '-')) ?></td>
                      <td>
                        <span class="badge
                          <?= ($highlightRow['status'] ?? '') == 'diterima' ? 'bg-success' 
                              : (($highlightRow['status'] ?? '') == 'ditolak' ? 'bg-danger' 
                              : 'bg-warning text-dark') ?>">
                          <?= !empty($highlightRow['status']) ? htmlspecialchars(ucfirst($highlightRow['status'])) : 'Diproses' ?>
                        </span>
                      </td>
                      <td>
                        <div class="btn-group" role="group" aria-label="Aksi">
                          <a href="?edit=<?= (int)$highlightRow['id'] ?>" class="btn btn-warning btn-sm" title="Edit">✏</a>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$highlightRow['id'] ?>">
                            <button name="setujui" class="btn btn-success btn-sm" title="Setujui" onclick="return confirm('Setujui materi ini?')">✔</button>
                            <input type="hidden" name="status" value="diterima">
                          </form>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$highlightRow['id'] ?>">
                            <button name="tolak" class="btn btn-danger btn-sm" title="Tolak" onclick="return confirm('Tolak materi ini?')">✖</button>
                          </form>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$highlightRow['id'] ?>">
                            <button name="hapus" class="btn btn-secondary btn-sm" title="Hapus" onclick="return confirm('Hapus materi ini?')">🗑</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                    <?php
                }

                // Render sisanya (urutan sesuai query original)
                foreach ($rows as $row):
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($row['judul']) ?></td>
                      <td><?= short_desc($row['deskripsi'], 80) ?></td>
                      <td><?= htmlspecialchars($row['nama_kategori'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($row['nama_kelas'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($row['nama_paket'] ?: '-') ?></td>
                      <td>
                        <?php if (!empty($row['file'])): ?>
                        <a href="../assets/uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank" class="text-primary">
                          <?= ($row['tipe_file'] ?? '') === 'video' ? 'Tonton Video' : 'Lihat File' ?>
                        </a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(ucfirst($row['tipe_file'] ?? '-')) ?></td>
                      <td>
                        <span class="badge
                          <?= ($row['status'] ?? '') == 'diterima' ? 'bg-success' 
                              : (($row['status'] ?? '') == 'ditolak' ? 'bg-danger' 
                              : 'bg-warning text-dark') ?>">
                          <?= !empty($row['status']) ? htmlspecialchars(ucfirst($row['status'])) : 'Diproses' ?>
                        </span>
                      </td>
                      <td>
                        <div class="btn-group" role="group" aria-label="Aksi">
                          <a href="?edit=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm" title="Edit">✏</a>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button name="setujui" class="btn btn-success btn-sm" title="Setujui" onclick="return confirm('Setujui materi ini?')">✔</button>
                            <input type="hidden" name="status" value="diterima">
                          </form>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button name="tolak" class="btn btn-danger btn-sm" title="Tolak" onclick="return confirm('Tolak materi ini?')">✖</button>
                          </form>
                          <form action="proses_materi.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button name="hapus" class="btn btn-secondary btn-sm" title="Hapus" onclick="return confirm('Hapus materi ini?')">🗑</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                    <?php
                endforeach;
            } else {
                echo '<tr><td colspan="9" class="text-center">Belum ada materi.</td></tr>';
            }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah/Edit Materi -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="proses_materi.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahLabel">
          <?= $edit_id ? 'Edit Materi' : 'Tambah Materi' ?>
        </h5>
        <a href="kelola_materi.php" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></a>
      </div>
      <div class="modal-body">
        <?php if ($edit_id): ?><input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>"><?php endif; ?>
        <div class="mb-3">
          <label>Judul Materi</label>
          <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($edit_data['judul'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label>Deskripsi</label>
          <textarea name="deskripsi" class="form-control" required><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
          <label>Kategori</label>
          <select name="kategori_id" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <?php
            $kategori = mysqli_query($conn, "SELECT * FROM kategori_materi");
            while ($k = mysqli_fetch_assoc($kategori)):
              $selected = ($edit_data['kategori_id'] ?? '') == $k['id'] ? 'selected' : '';
            ?>
              <option value="<?= (int)$k['id'] ?>" <?= $selected ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <?php if ($edit_id): ?>
        <div class="mb-3">
          <label>Status</label>
          <select name="status" class="form-select" required>
            <?php
            $allowedStatus = ['menunggu', 'diproses', 'diterima', 'ditolak'];
            foreach ($allowedStatus as $s) {
                $selected = ($edit_data['status'] ?? '') == $s ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($s) . "' $selected>" . ucfirst($s) . "</option>";
            }
            ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="mb-3">
          <label>Kelas</label>
          <select name="kelas_id" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
            <?php
            $kelas = mysqli_query($conn, "SELECT * FROM kelas");
            while ($k = mysqli_fetch_assoc($kelas)):
              $selected = ($edit_data['kelas_id'] ?? '') == $k['id'] ? 'selected' : '';
            ?>
              <option value="<?= (int)$k['id'] ?>" <?= $selected ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>Paket</label>
          <select name="paket_id" id="paketSelect" class="form-select">
            <option value="">-- Pilih Paket --</option>
            <?php
            $paket = mysqli_query($conn, "SELECT * FROM paket");
            while ($p = mysqli_fetch_assoc($paket)):
              $selected = ($edit_data['paket_id'] ?? '') == $p['id'] ? 'selected' : '';
            ?>
              <option value="<?= (int)$p['id'] ?>" <?= $selected ?>><?= htmlspecialchars($p['nama']) ?></option>
            <?php endwhile; ?>
            <option value="0">-- Tambah Paket Baru --</option>
          </select>
        </div>
        <div class="mb-3" id="paketBaruField" style="display:none;">
          <label>Nama Paket Baru</label>
          <input type="text" name="paket_baru" class="form-control" placeholder="Masukkan nama paket baru">
        </div>
        <div class="mb-3">
          <label>File Materi <?= $edit_id ? '(Kosongkan jika tidak diubah)' : '' ?></label>
          <input type="file" name="file" class="form-control" accept=".pdf,.mp4,.mkv,.avi,.mov" <?= $edit_id ? '' : 'required' ?>>
          <small class="text-muted">Format file: PDF/MP4/MKV/AVI/MOV. Maksimal 10MB.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="<?= $edit_id ? 'edit_admin' : 'tambah_admin' ?>" class="btn btn-primary">
          <?= $edit_id ? 'Simpan Perubahan' : 'Unggah Materi' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php if ($edit_id): ?>
<!-- Auto open modal saat mode edit -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var modalEdit = new bootstrap.Modal(document.getElementById('modalTambah'));
    modalEdit.show();
  });
</script>
<?php endif; ?>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  $('#materiTable').DataTable({
    "pageLength": 10,
    "lengthMenu": [10, 25, 50, 100],
    "ordering": true,
    "searching": true,
    "language": {
      "search": "Cari:",
      "lengthMenu": "Tampilkan _MENU_ data",
      "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
      "paginate": {
        "next": "Berikutnya",
        "previous": "Sebelumnya"
      }
    }
  });

  // Tampilkan field "Nama Paket Baru" saat memilih opsi value == 0
  $('#paketSelect').on('change', function() {
    if ($(this).val() === '0') {
      $('#paketBaruField').show();
    } else {
      $('#paketBaruField').hide();
    }
  });

  // Jika sedang edit dan paket_id == 0 atau paketBaru sudah ada, tunjukkan field
  if ($('#paketSelect').val() === '0') {
    $('#paketBaruField').show();
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/admin_footer.php'; ?>
