<?php
include '../includes/admin_header.php';
include '../config/database.php';

// Handle Hapus Jadwal
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM jadwal_offline WHERE id = $id");
    echo "<script>alert('Jadwal berhasil dihapus'); window.location.href='kelola_jadwal_offline.php';</script>";
}

// Handle Filter
$kelas = $_GET['kelas'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$kategori = $_GET['kategori'] ?? '';

// Query Filter
$query = "SELECT j.*, u.nama AS nama_tutor, k.nama_kategori, c.nama_kelas 
          FROM jadwal_offline j
          JOIN users u ON j.tutor_id = u.id
          JOIN kategori_materi k ON j.kategori_id = k.id
          JOIN kelas c ON j.kelas_id = c.id
          WHERE 1=1";
if (!empty($kelas)) $query .= " AND j.kelas_id = '$kelas'";
if (!empty($tanggal)) $query .= " AND j.tanggal = '$tanggal'";
if (!empty($kategori)) $query .= " AND j.kategori_id = '$kategori'";

$result = $conn->query($query);

// Data dropdown
$kelas_result = $conn->query("SELECT * FROM kelas");
$kategori_result = $conn->query("SELECT * FROM kategori_materi");
$tutor_result = $conn->query("SELECT * FROM users WHERE role = 'tutor'");
?>

 <div class="content">
  <div class="container-fluid pt-4 px-4">
      <h2 class="mb-4">Kelola Jadwal Kelas Offline</h2>

      <!-- Filter -->
      <form method="GET" class="mb-4">
          <div class="row g-3 align-items-center">
              <div class="col-md-3">
                  <select name="kelas" class="form-control">
                      <option value="">Pilih Kelas</option>
                      <?php while($row = $kelas_result->fetch_assoc()): ?>
                          <option value="<?= $row['id'] ?>" <?= ($kelas == $row['id']) ? 'selected' : '' ?>>
                              <?= $row['nama_kelas'] ?>
                          </option>
                      <?php endwhile; ?>
                  </select>
              </div>
              <div class="col-md-3">
                  <input type="date" name="tanggal" value="<?= $tanggal ?>" class="form-control" />
              </div>
              <div class="col-md-3">
                  <select name="kategori" class="form-control">
                      <option value="">Pilih Mata Pelajaran</option>
                      <?php while($row = $kategori_result->fetch_assoc()): ?>
                          <option value="<?= $row['id'] ?>" <?= ($kategori == $row['id']) ? 'selected' : '' ?>>
                              <?= $row['nama_kategori'] ?>
                          </option>
                      <?php endwhile; ?>
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

      <!-- Tabel Responsive -->
      <div class="table-responsive mb-5">
          <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-primary">
                  <tr>
                      <th>No</th>
                      <th>Tutor</th>
                      <th>Kelas</th>
                      <th>Mata Pelajaran</th>
                      <th>Tanggal</th>
                      <th>Jam</th>
                      <th>Keterangan</th>
                      <th>File Materi</th>
                      <th>Aksi</th>
                  </tr>
              </thead>
              <tbody>
                  <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                      <tr>
                          <td><?= $no++ ?></td>
                          <td><?= $row['nama_tutor'] ?></td>
                          <td><?= $row['nama_kelas'] ?></td>
                          <td><?= $row['nama_kategori'] ?></td>
                          <td><?= $row['tanggal'] ?></td>
                          <td><?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?></td>
                          <td><?= $row['keterangan'] ?></td>
                          <td>
                              <?= $row['materi_file'] ? "<a href='../uploads/{$row['materi_file']}' target='_blank'>Lihat</a>" : "-" ?>
                          </td>
                          <td class="text-center">
                              <a href="edit_jadwal_offline.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                              <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus jadwal ini?')" class="btn btn-danger btn-sm">Hapus</a>
                          </td>
                      </tr>
                  <?php endwhile; ?>
              </tbody>
          </table>
      </div>
  </div>
</div>


<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="tambah_jadwal_offline.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Jadwal Offline</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
            <label>Kelas</label>
            <select name="kelas_id" class="form-control" required>
                <?php
                $kelas_result = $conn->query("SELECT * FROM kelas");
                while($row = $kelas_result->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama_kelas'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-2">
            <label>Mata Pelajaran</label>
            <select name="kategori_id" class="form-control" required>
                <?php
                $kategori_result = $conn->query("SELECT * FROM kategori_materi");
                while($row = $kategori_result->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama_kategori'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-2">
            <label>Tutor</label>
            <select name="tutor_id" class="form-control" required>
                <?php
                $tutor_result = $conn->query("SELECT * FROM users WHERE role = 'tutor'");
                while($row = $tutor_result->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
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
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
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

<?php include '../includes/admin_footer.php'; ?>
