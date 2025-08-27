<?php
include '../includes/admin_header.php';
include '../config/database.php';

// --- HANDLE CREATE (Tambah Jadwal Baru) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_jadwal'])) {
    $kelas_id    = intval($_POST['kelas_id']);
    $kategori_id = intval($_POST['kategori_id']);
    $paket_id    = intval($_POST['paket_id']);
    $tutor_id    = intval($_POST['tutor_id']);
    $tanggal     = $conn->real_escape_string($_POST['tanggal']);
    $jam_mulai   = $conn->real_escape_string($_POST['jam_mulai']);
    $jam_selesai = $conn->real_escape_string($_POST['jam_selesai']);

    $materi_file_to_save = '';
    if (!empty($_FILES['materi_file']['name'])) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['materi_file']['name']);
        if (move_uploaded_file($_FILES['materi_file']['tmp_name'], $upload_dir . $filename)) {
            $materi_file_to_save = $conn->real_escape_string($filename);
        }
    }

    $sql = "INSERT INTO jadwal_offline 
            (kelas_id, kategori_id, paket_id, tutor_id, tanggal, jam_mulai, jam_selesai, materi_file) 
            VALUES ('$kelas_id', '$kategori_id', '$paket_id', '$tutor_id', '$tanggal', '$jam_mulai', '$jam_selesai', '$materi_file_to_save')";

    if ($conn->query($sql)) {
        echo "<script>alert('Jadwal berhasil ditambahkan'); window.location.href='kelola_jadwal_offline.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan jadwal: " . addslashes($conn->error) . "');</script>";
    }
    exit;
}

// --- HANDLE HAPUS JADWAL ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $q = $conn->query("SELECT materi_file FROM jadwal_offline WHERE id = $id");
    if ($q && $q->num_rows) {
        $r = $q->fetch_assoc();
        if (!empty($r['materi_file']) && file_exists(__DIR__ . '/../uploads/' . $r['materi_file'])) {
            @unlink(__DIR__ . '/../uploads/' . $r['materi_file']);
        }
    }
    $conn->query("DELETE FROM jadwal_offline WHERE id = $id");
    echo "<script>alert('Jadwal berhasil dihapus'); window.location.href='kelola_jadwal_offline.php';</script>";
    exit;
}

// --- HANDLE UPDATE JADWAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_jadwal'])) {
    $id          = intval($_POST['id']);
    $kelas_id    = intval($_POST['kelas_id']);
    $kategori_id = intval($_POST['kategori_id']);
    $paket_id    = intval($_POST['paket_id']);
    $tutor_id    = intval($_POST['tutor_id']);
    $tanggal     = $conn->real_escape_string($_POST['tanggal']);
    $jam_mulai   = $conn->real_escape_string($_POST['jam_mulai']);
    $jam_selesai = $conn->real_escape_string($_POST['jam_selesai']);
    $existing_file = $_POST['existing_materi_file'] ?? '';

    $materi_file_to_save = $existing_file;
    if (isset($_FILES['materi_file']) && !empty($_FILES['materi_file']['name'])) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['materi_file']['name']);
        if (move_uploaded_file($_FILES['materi_file']['tmp_name'], $upload_dir . $filename)) {
            if (!empty($existing_file) && file_exists($upload_dir . $existing_file)) {
                @unlink($upload_dir . $existing_file);
            }
            $materi_file_to_save = $conn->real_escape_string($filename);
        }
    }

    $sql = "UPDATE jadwal_offline SET 
                kelas_id='$kelas_id',
                kategori_id='$kategori_id',
                paket_id='$paket_id',
                tutor_id='$tutor_id',
                tanggal='$tanggal',
                jam_mulai='$jam_mulai',
                jam_selesai='$jam_selesai',
                materi_file='$materi_file_to_save'
            WHERE id = $id";

    if ($conn->query($sql)) {
        echo "<script>alert('Jadwal berhasil diupdate'); window.location.href='kelola_jadwal_offline.php';</script>";
    } else {
        echo "<script>alert('Gagal update jadwal: " . addslashes($conn->error) . "');</script>";
    }
    exit;
}

// --- DATA DROPDOWN ---
$kelas_list = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetch_all(MYSQLI_ASSOC);
$kategori_list = $conn->query("SELECT * FROM kategori_materi ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
$paket_list = $conn->query("SELECT * FROM paket ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
$tutor_list = $conn->query("SELECT * FROM users WHERE role = 'tutor' ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

// --- FILTER ---
$kelas = $_GET['kelas'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$kategori = $_GET['kategori'] ?? '';

// --- QUERY DATA (ORDER BY terbaru dulu) ---
$query = "SELECT j.*, u.nama AS nama_tutor, k.nama_kategori, c.nama_kelas, p.nama AS nama_paket
          FROM jadwal_offline j
          JOIN users u ON j.tutor_id = u.id
          JOIN kategori_materi k ON j.kategori_id = k.id
          JOIN kelas c ON j.kelas_id = c.id
          JOIN paket p ON j.paket_id = p.id
          WHERE 1=1";
if (!empty($kelas)) $query .= " AND j.kelas_id = '" . $conn->real_escape_string($kelas) . "'";
if (!empty($tanggal)) $query .= " AND j.tanggal = '" . $conn->real_escape_string($tanggal) . "'";
if (!empty($kategori)) $query .= " AND j.kategori_id = '" . $conn->real_escape_string($kategori) . "'";
$query .= " ORDER BY j.id DESC"; // data terbaru tampil paling atas
$result = $conn->query($query);
?>

<div class="content">
  <div class="container-fluid pt-4 px-4" style="margin-top: 50px;">
    <h2 class="mb-4">Kelola Jadwal Kelas Offline</h2>

    <!-- Filter -->
    <form method="GET" class="mb-4">
      <div class="row g-3 align-items-center">
        <div class="col-md-3">
          <select name="kelas" class="form-control">
            <option value="">Pilih Kelas</option>
            <?php foreach($kelas_list as $k): ?>
              <option value="<?= $k['id'] ?>" <?= ($kelas == $k['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama_kelas']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-control" />
        </div>
        <div class="col-md-3">
          <select name="kategori" class="form-control">
            <option value="">Pilih Mata Pelajaran</option>
            <?php foreach($kategori_list as $kat): ?>
              <option value="<?= $kat['id'] ?>" <?= ($kategori == $kat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($kat['nama_kategori']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="kelola_jadwal_offline.php" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>

    <!-- Tombol Tambah -->
    <div class="mb-3">
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Jadwal</button>
    </div>

    <!-- Tabel (dengan DataTables) -->
    <div class="table-responsive mb-5">
      <table id="jadwalTable" class="table table-bordered bg-white shadow-sm">
        <thead class="table-primary">
          <tr>
            <th>No</th>
            <th>Tutor</th>
            <th>Kelas</th>
            <th>Mata Pelajaran</th>
            <th>Paket</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>File Materi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; if ($result && $result->num_rows>0): while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama_tutor']) ?></td>
              <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
              <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
              <td><?= htmlspecialchars($row['nama_paket']) ?></td>
              <td><?= htmlspecialchars($row['tanggal']) ?></td>
              <td><?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?></td>
              <td><?= $row['materi_file'] ? "<a href='../uploads/".htmlspecialchars($row['materi_file'])."' target='_blank'>Lihat</a>" : "-" ?></td>
              <td>
                <button 
                  class="btn btn-warning btn-sm btnEdit"
                  data-id="<?= $row['id'] ?>"
                  data-kelas="<?= $row['kelas_id'] ?>"
                  data-kategori="<?= $row['kategori_id'] ?>"
                  data-paket="<?= $row['paket_id'] ?>"
                  data-tutor="<?= $row['tutor_id'] ?>"
                  data-tanggal="<?= $row['tanggal'] ?>"
                  data-jammulai="<?= $row['jam_mulai'] ?>"
                  data-jamselesai="<?= $row['jam_selesai'] ?>"
                  data-materifile="<?= htmlspecialchars($row['materi_file']) ?>"
                >Edit</button>
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus jadwal ini?')" class="btn btn-danger btn-sm">Hapus</a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="create_jadwal" value="1">
      <div class="modal-header"><h5 class="modal-title">Tambah Jadwal Offline</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label>Kelas</label>
          <select name="kelas_id" class="form-control" required>
            <option value="">-- Pilih Kelas --</option>
            <?php foreach($kelas_list as $k): ?>
              <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Mata Pelajaran</label>
          <select name="kategori_id" class="form-control" required>
            <option value="">-- Pilih Mata Pelajaran --</option>
            <?php foreach($kategori_list as $kat): ?>
              <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Paket</label>
          <select name="paket_id" class="form-control" required>
            <option value="">-- Pilih Paket --</option>
            <?php foreach($paket_list as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Tutor</label>
          <select name="tutor_id" class="form-control" required>
            <option value="">-- Pilih Tutor --</option>
            <?php foreach($tutor_list as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Tanggal</label>
          <input type="date" name="tanggal" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Jam Mulai</label>
          <input type="time" name="jam_mulai" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Jam Selesai</label>
          <input type="time" name="jam_selesai" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>File Materi (opsional)</label>
          <input type="file" name="materi_file" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Simpan</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="update_jadwal" value="1">
      <input type="hidden" name="id" id="edit_id">
      <input type="hidden" name="existing_materi_file" id="edit_existing_file">
      <div class="modal-header"><h5 class="modal-title">Edit Jadwal Offline</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label>Kelas</label>
          <select name="kelas_id" id="edit_kelas" class="form-control" required>
            <?php foreach($kelas_list as $k): ?>
              <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Mata Pelajaran</label>
          <select name="kategori_id" id="edit_kategori" class="form-control" required>
            <?php foreach($kategori_list as $kat): ?>
              <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Paket</label>
          <select name="paket_id" id="edit_paket" class="form-control" required>
            <?php foreach($paket_list as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Tutor</label>
          <select name="tutor_id" id="edit_tutor" class="form-control" required>
            <?php foreach($tutor_list as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Tanggal</label>
          <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Jam Mulai</label>
          <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Jam Selesai</label>
          <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>File Materi (opsional)</label>
          <div id="currentFile" class="mb-1"></div>
          <input type="file" name="materi_file" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Update</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- DataTables & initialization (jQuery included because DataTables needs it) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    // Inisialisasi DataTable
    var table = $('#jadwalTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: true,
        order: [[0, 'asc']], // default ordering by the No column (nomor)
        columnDefs: [
            { orderable: false, targets: [7,8] } // file materi dan aksi tidak bisa diorder
        ]
    });

    // Event delegation untuk tombol Edit supaya tetap berfungsi setelah table di-render ulang DataTables
    $(document).on('click', '.btnEdit', function () {
        var $btn = $(this);
        $('#edit_id').val($btn.data('id'));
        $('#edit_kelas').val($btn.data('kelas'));
        $('#edit_kategori').val($btn.data('kategori'));
        $('#edit_paket').val($btn.data('paket'));
        $('#edit_tutor').val($btn.data('tutor'));
        $('#edit_tanggal').val($btn.data('tanggal'));
        var jm = ($btn.data('jammulai') || '').toString();
        var js = ($btn.data('jamselesai') || '').toString();
        $('#edit_jam_mulai').val(jm.substring(0,5));
        $('#edit_jam_selesai').val(js.substring(0,5));
        $('#edit_existing_file').val($btn.data('materifile') || '');

        var mf = $btn.data('materifile') || '';
        if (mf) {
            $('#currentFile').html('File sekarang: <a href="../uploads/' + encodeURIComponent(mf) + '" target="_blank">' + mf + '</a>');
        } else {
            $('#currentFile').html('<em>Tidak ada file</em>');
        }

        // Tampilkan modal edit (Bootstrap 5)
        var modalEl = document.getElementById('modalEdit');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>
