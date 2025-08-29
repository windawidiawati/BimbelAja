<?php
session_start();
require_once '../includes/admin_header.php';
require_once '../config/database.php';

// Fungsi untuk menangani form tambah absensi tutor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_absensi'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $tutor_id = $_POST['tutor_id'];
    $status = $_POST['status'];
    $tanggal = $_POST['tanggal'];
    
    $query = "INSERT INTO absensi_tutor (jadwal_id, tutor_id, status, tanggal) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $error_message = "Gagal menyiapkan statement: " . $conn->error;
    } else {
        $stmt->bind_param("iiss", $jadwal_id, $tutor_id, $status, $tanggal);
        if ($stmt->execute()) {
            $success_message = "Absensi tutor berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan absensi tutor: " . $stmt->error;
        }
    }
}

// Fungsi untuk menangani form edit absensi tutor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_absensi'])) {
    $id = $_POST['id'];
    $jadwal_id = $_POST['jadwal_id'];
    $tutor_id = $_POST['tutor_id'];
    $status = $_POST['status'];
    $tanggal = $_POST['tanggal'];
    
    $query = "UPDATE absensi_tutor SET jadwal_id=?, tutor_id=?, status=?, tanggal=? 
              WHERE id=?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $error_message = "Gagal menyiapkan statement: " . $conn->error;
    } else {
        $stmt->bind_param("iissi", $jadwal_id, $tutor_id, $status, $tanggal, $id);
        if ($stmt->execute()) {
            $success_message = "Absensi tutor berhasil diupdate!";
        } else {
            $error_message = "Gagal mengupdate absensi tutor: " . $stmt->error;
        }
    }
}

// Fungsi untuk menghapus absensi tutor
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    $query = "DELETE FROM absensi_tutor WHERE id=?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $error_message = "Gagal menyiapkan statement: " . $conn->error;
    } else {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success_message = "Absensi tutor berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus absensi tutor: " . $stmt->error;
        }
    }
}

// Ambil data absensi tutor untuk tabel
$query_absensi = "SELECT at.*, u.nama AS nama_tutor, jo.tanggal AS tanggal_jadwal, 
                  jo.jam_mulai, jo.jam_selesai, km.nama_kategori 
                  FROM absensi_tutor at 
                  JOIN users u ON at.tutor_id = u.id 
                  JOIN jadwal_offline jo ON at.jadwal_id = jo.id 
                  JOIN kategori_materi km ON jo.kategori_id = km.id 
                  ORDER BY at.tanggal DESC";
$result_absensi = $conn->query($query_absensi);
if ($result_absensi === false) {
    die("Query absensi gagal: " . $conn->error);
}

// Ambil data tutor untuk dropdown di modals
$query_tutor = "SELECT id, nama FROM users WHERE role='tutor'";
$result_tutor = $conn->query($query_tutor);
if ($result_tutor === false) {
    die("Query tutor gagal: " . $conn->error);
}

// Ambil data jadwal offline untuk dropdown di modals
$query_jadwal = "SELECT jo.id, jo.tanggal, jo.jam_mulai, jo.jam_selesai, jo.kategori_id, km.nama_kategori
                 FROM jadwal_offline jo
                 JOIN kategori_materi km ON jo.kategori_id = km.id
                 ORDER BY jo.tanggal DESC";
$result_jadwal = $conn->query($query_jadwal);
if ($result_jadwal === false) {
    die("Query jadwal gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/admin_header.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kelola Absensi Tutor</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahAbsensiModal">
                        <i class="fas fa-plus"></i> Tambah Absensi
                    </button>
                    <a href="export_absensi_tutor.php" class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
                        <i class="fas fa-download"></i> Export PDF
                    </a>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="absensiTable">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>Tanggal</th>
                            <th>Tutor</th>
                            <th>Jadwal</th>
                            <th>Mata Pelajaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_absensi->num_rows > 0): ?>
                            <?php $no = 1; while ($row = $result_absensi->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo $row['nama_tutor']; ?></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($row['tanggal_jadwal'])); ?><br>
                                        <?php echo $row['jam_mulai'] . ' - ' . $row['jam_selesai']; ?>
                                    </td>
                                    <td><?php echo $row['nama_kategori']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            if ($row['status'] == 'Hadir') echo 'success';
                                            elseif ($row['status'] == 'Izin') echo 'warning';
                                            else echo 'danger';
                                        ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAbsensiModal" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-jadwal_id="<?php echo $row['jadwal_id']; ?>"
                                                data-tutor_id="<?php echo $row['tutor_id']; ?>"
                                                data-status="<?php echo $row['status']; ?>"
                                                data-tanggal="<?php echo $row['tanggal']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus absensi ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data absensi tutor</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="tambahAbsensiModal" tabindex="-1" aria-labelledby="tambahAbsensiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahAbsensiModalLabel">Tambah Absensi Tutor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="jadwal_id" class="form-label">Jadwal</label>
                        <select class="form-select" id="jadwal_id" name="jadwal_id" required>
                            <option value="">Pilih Jadwal</option>
                            <?php 
                            $result_jadwal->data_seek(0); // Reset pointer
                            while ($jadwal = $result_jadwal->fetch_assoc()): ?>
                                <option value="<?php echo $jadwal['id']; ?>">
                                    <?php echo date('d/m/Y', strtotime($jadwal['tanggal'])) . ' - ' . $jadwal['jam_mulai'] . ' - ' . $jadwal['jam_selesai'] . ' (' . $jadwal['nama_kategori'] . ')'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tutor_id" class="form-label">Tutor</label>
                        <select class="form-select" id="tutor_id" name="tutor_id" required>
                            <option value="">Pilih Tutor</option>
                            <?php 
                            $result_tutor->data_seek(0); // Reset pointer
                            while ($tutor = $result_tutor->fetch_assoc()): ?>
                                <option value="<?php echo $tutor['id']; ?>"><?php echo $tutor['nama']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Absensi</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_absensi" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAbsensiModal" tabindex="-1" aria-labelledby="editAbsensiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAbsensiModalLabel">Edit Absensi Tutor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_jadwal_id" class="form-label">Jadwal</label>
                        <select class="form-select" id="edit_jadwal_id" name="jadwal_id" required>
                            <option value="">Pilih Jadwal</option>
                            <?php 
                            $result_jadwal->data_seek(0); // Reset pointer
                            while ($jadwal = $result_jadwal->fetch_assoc()): ?>
                                <option value="<?php echo $jadwal['id']; ?>">
                                    <?php echo date('d/m/Y', strtotime($jadwal['tanggal'])) . ' - ' . $jadwal['jam_mulai'] . ' - ' . $jadwal['jam_selesai'] . ' (' . $jadwal['nama_kategori'] . ')'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tutor_id" class="form-label">Tutor</label>
                        <select class="form-select" id="edit_tutor_id" name="tutor_id" required>
                            <option value="">Pilih Tutor</option>
                            <?php 
                            $result_tutor->data_seek(0); // Reset pointer
                            while ($tutor = $result_tutor->fetch_assoc()): ?>
                                <option value="<?php echo $tutor['id']; ?>"><?php echo $tutor['nama']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tanggal" class="form-label">Tanggal Absensi</label>
                        <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_absensi" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
// Script untuk mengisi data ke modal edit
document.addEventListener('DOMContentLoaded', function() {
    var editAbsensiModal = document.getElementById('editAbsensiModal');
    editAbsensiModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        
        document.getElementById('edit_id').value = button.getAttribute('data-id');
        document.getElementById('edit_jadwal_id').value = button.getAttribute('data-jadwal_id');
        document.getElementById('edit_tutor_id').value = button.getAttribute('data-tutor_id');
        document.getElementById('edit_status').value = button.getAttribute('data-status');
        document.getElementById('edit_tanggal').value = button.getAttribute('data-tanggal');
    });
});

// Inisialisasi DataTables
$(document).ready(function () {
    $('#absensiTable').DataTable();
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>