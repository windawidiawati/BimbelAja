<?php
// Pastikan ini baris pertama file
ob_start();
session_start();
include '../config/database.php';
include '../includes/admin_header.php';

// Ambil data kategori_materi
$kategori_result = $conn->query("SELECT * FROM kategori_materi");
$kategori_map = [];
while ($k = $kategori_result->fetch_assoc()) {
    $kategori_map[$k['id']] = $k['nama_kategori'];
}

// Ambil data kelas
$kelas_result = $conn->query("SELECT * FROM kelas");
$kelas_map = [];
while ($kls = $kelas_result->fetch_assoc()) {
    $kelas_map[$kls['id']] = $kls['nama_kelas'] . ' (' . $kls['jenjang'] . ')';
}

// Filter
$jenjang = $_POST['jenjang'] ?? '';
$mata_pelajaran = $_POST['mata_pelajaran'] ?? '';

$query = "SELECT soal.*, kategori_materi.nama_kategori, kelas.nama_kelas, kelas.jenjang 
          FROM soal 
          JOIN kategori_materi ON soal.kategori_id = kategori_materi.id 
          JOIN kelas ON soal.kelas_id = kelas.id
          WHERE 1=1";

$params = [];
$types = '';
if ($jenjang) {
    $query .= " AND kelas.jenjang = ?";
    $types .= 's';
    $params[] = $jenjang;
}
if ($mata_pelajaran) {
    $query .= " AND soal.kategori_id = ?";
    $types .= 'i';
    $params[] = $mata_pelajaran;
}

// cek kolom updated_at
$checkUpdated = $conn->query("SHOW COLUMNS FROM soal LIKE 'updated_at'");

// urutan terbaru selalu di atas
if ($checkUpdated->num_rows > 0) {
    $query .= " ORDER BY soal.updated_at DESC, soal.created_at DESC, soal.id DESC";
} else {
    $query .= " ORDER BY soal.created_at DESC, soal.id DESC";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// CRUD Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM soal WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Soal berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus soal";
    }
    ob_end_clean();
    header("Location: kelola_soal.php");
    exit;
}

// CRUD Tambah
if (isset($_POST['tambah_soal'])) {
    if (empty($_POST['pertanyaan']) || empty($_POST['opsi_a']) || empty($_POST['opsi_b']) || 
        empty($_POST['opsi_c']) || empty($_POST['opsi_d']) || empty($_POST['jawaban']) || 
        empty($_POST['kelas_id']) || empty($_POST['kategori_id'])) {
        $_SESSION['error'] = "Semua field harus diisi!";
    } else {
        $stmt = $conn->prepare("INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, tutor_id, kelas_id, kategori_id, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssiii",
            $_POST['pertanyaan'],
            $_POST['opsi_a'],
            $_POST['opsi_b'],
            $_POST['opsi_c'],
            $_POST['opsi_d'],
            $_POST['jawaban'],
            $_SESSION['user']['id'],
            $_POST['kelas_id'],
            $_POST['kategori_id']
        );
        if ($stmt->execute()) {
            $_SESSION['success'] = "Soal berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan soal: " . $conn->error;
        }
    }
    ob_end_clean();
    header("Location: kelola_soal.php");
    exit;
}

// CRUD Edit
if (isset($_POST['edit_soal'])) {
    if (empty($_POST['pertanyaan']) || empty($_POST['opsi_a']) || empty($_POST['opsi_b']) || 
        empty($_POST['opsi_c']) || empty($_POST['opsi_d']) || empty($_POST['jawaban']) || 
        empty($_POST['kelas_id']) || empty($_POST['kategori_id'])) {
        $_SESSION['error'] = "Semua field harus diisi!";
    } else {
        $sqlUpdate = "UPDATE soal SET pertanyaan = ?, opsi_a = ?, opsi_b = ?, opsi_c = ?, opsi_d = ?, jawaban = ?, kelas_id = ?, kategori_id = ?";
        if ($checkUpdated->num_rows > 0) {
            $sqlUpdate .= ", updated_at = NOW()";
        }
        $sqlUpdate .= " WHERE id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssssssiii",
            $_POST['pertanyaan'],
            $_POST['opsi_a'],
            $_POST['opsi_b'],
            $_POST['opsi_c'],
            $_POST['opsi_d'],
            $_POST['jawaban'],
            $_POST['kelas_id'],
            $_POST['kategori_id'],
            $_POST['id']
        );
        if ($stmt->execute()) {
            $_SESSION['success'] = "Soal berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui soal: " . $conn->error;
        }
    }
    ob_end_clean();
    header("Location: kelola_soal.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Soal</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    /* Supaya tabel rapih dan tidak ada scroll horizontal berlebihan */
    #soalTable {
        table-layout: fixed;
        width: 100%;
    }
    #soalTable th, #soalTable td {
        white-space: normal !important;
        word-wrap: break-word;
        vertical-align: middle;
    }
    #soalTable td:nth-child(2) {
        max-width: 300px;
    }
  </style>
</head>
<body>
<div class="content">
  <h2 class="mb-4">Kelola Soal</h2>

  <!-- Notifikasi -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Filter -->
  <div class="bg-white rounded shadow-sm p-4 mb-4">
    <form method="POST" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Jenjang</label>
        <select name="jenjang" class="form-select">
          <option value="">Semua Jenjang</option>
          <option value="SD" <?= ($jenjang == "SD") ? "selected" : "" ?>>SD</option>
          <option value="SMP" <?= ($jenjang == "SMP") ? "selected" : "" ?>>SMP</option>
          <option value="SMA" <?= ($jenjang == "SMA") ? "selected" : "" ?>>SMA</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Mata Pelajaran</label>
        <select name="mata_pelajaran" class="form-select">
          <option value="">Semua</option>
          <?php foreach ($kategori_map as $id => $nama): ?>
            <option value="<?= $id ?>" <?= ($mata_pelajaran == $id) ? "selected" : "" ?>>
              <?= htmlspecialchars($nama) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 text-end">
        <button type="submit" class="btn btn-primary px-4">Filter</button>
      </div>
    </form>
  </div>

  <!-- Button Tambah -->
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahSoalModal">Tambah Soal</button>

  <!-- Table -->
  <div class="table-responsive">
    <table id="soalTable" class="table table-striped table-bordered align-middle">
      <thead class="table-primary text-center">
        <tr>
          <th>No</th>
          <th>Pertanyaan</th>
          <th>Opsi A</th>
          <th>Opsi B</th>
          <th>Opsi C</th>
          <th>Opsi D</th>
          <th>Jawaban</th>
          <th>Kelas</th>
          <th>Mata Pelajaran</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
            <td><?= htmlspecialchars($row['opsi_a']) ?></td>
            <td><?= htmlspecialchars($row['opsi_b']) ?></td>
            <td><?= htmlspecialchars($row['opsi_c']) ?></td>
            <td><?= htmlspecialchars($row['opsi_d']) ?></td>
            <td><?= htmlspecialchars($row['jawaban']) ?></td>
            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
            <td><?= date('d M Y H:i', strtotime($row['updated_at'] ?? $row['created_at'])) ?></td>
            <td>
              <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSoalModal"
                data-id="<?= $row['id'] ?>"
                data-pertanyaan="<?= htmlspecialchars($row['pertanyaan']) ?>"
                data-opsi_a="<?= htmlspecialchars($row['opsi_a']) ?>"
                data-opsi_b="<?= htmlspecialchars($row['opsi_b']) ?>"
                data-opsi_c="<?= htmlspecialchars($row['opsi_c']) ?>"
                data-opsi_d="<?= htmlspecialchars($row['opsi_d']) ?>"
                data-jawaban="<?= $row['jawaban'] ?>"
                data-kelas_id="<?= $row['kelas_id'] ?>"
                data-kategori_id="<?= $row['kategori_id'] ?>">
                Edit
              </button>
              <a href="kelola_soal.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus soal ini?')">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahSoalModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5>Tambah Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea class="form-control mb-3" name="pertanyaan" placeholder="Pertanyaan" required></textarea>
        <input type="text" class="form-control mb-2" name="opsi_a" placeholder="Opsi A" required>
        <input type="text" class="form-control mb-2" name="opsi_b" placeholder="Opsi B" required>
        <input type="text" class="form-control mb-2" name="opsi_c" placeholder="Opsi C" required>
        <input type="text" class="form-control mb-2" name="opsi_d" placeholder="Opsi D" required>
        <select name="jawaban" class="form-select mb-2" required>
          <option value="">Pilih Jawaban</option><option>A</option><option>B</option><option>C</option><option>D</option>
        </select>
        <select name="kelas_id" class="form-select mb-2" required>
          <option value="">Pilih Kelas</option>
          <?php foreach ($kelas_map as $id=>$nama): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="kategori_id" class="form-select mb-2" required>
          <option value="">Pilih Mata Pelajaran</option>
          <?php foreach ($kategori_map as $id=>$nama): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" name="tambah_soal" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editSoalModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <input type="hidden" id="edit-id" name="id">
      <div class="modal-header">
        <h5>Edit Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea class="form-control mb-3" id="edit-pertanyaan" name="pertanyaan" required></textarea>
        <input type="text" class="form-control mb-2" id="edit-opsi_a" name="opsi_a" required>
        <input type="text" class="form-control mb-2" id="edit-opsi_b" name="opsi_b" required>
        <input type="text" class="form-control mb-2" id="edit-opsi_c" name="opsi_c" required>
        <input type="text" class="form-control mb-2" id="edit-opsi_d" name="opsi_d" required>
        <select id="edit-jawaban" name="jawaban" class="form-select mb-2" required>
          <option>A</option><option>B</option><option>C</option><option>D</option>
        </select>
        <select id="edit-kelas_id" name="kelas_id" class="form-select mb-2" required>
          <?php foreach ($kelas_map as $id=>$nama): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
          <?php endforeach; ?>
        </select>
        <select id="edit-kategori_id" name="kategori_id" class="form-select mb-2" required>
          <?php foreach ($kategori_map as $id=>$nama): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" name="edit_soal" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  $('#soalTable').DataTable({
    pageLength: 10,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"Semua"]],
    order: [], 
    responsive: true,
    autoWidth: false,
    language: {
      lengthMenu: "Tampilkan _MENU_ entri",
      search: "Cari:",
      info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
      paginate: {first:"Awal",last:"Akhir",next:"›",previous:"‹"}
    }
  });
});

// isi data ke modal edit
document.getElementById('editSoalModal').addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  document.getElementById('edit-id').value = btn.getAttribute('data-id');
  document.getElementById('edit-pertanyaan').value = btn.getAttribute('data-pertanyaan');
  document.getElementById('edit-opsi_a').value = btn.getAttribute('data-opsi_a');
  document.getElementById('edit-opsi_b').value = btn.getAttribute('data-opsi_b');
document.getElementById('edit-opsi_c').value = btn.getAttribute('data-opsi_c');
 document.getElementById('edit-opsi_d').value = btn.getAttribute('data-opsi_d'); 
 document.getElementById('edit-jawaban').value = btn.getAttribute('data-jawaban'); 
 document.getElementById('edit-kelas_id').value = btn.getAttribute('data-kelas_id'); 
 document.getElementById('edit-kategori_id').value = btn.getAttribute('data-kategori_id');
  }); 
  </script>
   <?php ob_end_flush(); ?>