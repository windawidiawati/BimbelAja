<?php
include '../includes/admin_header.php';
include '../config/database.php';

// Inisialisasi variabel filter
$filter_nama = isset($_GET['filter_nama']) ? $_GET['filter_nama'] : '';
$filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';
$filter_kategori = isset($_GET['filter_kategori']) ? $_GET['filter_kategori'] : '';
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';

// Query dasar dengan join ke tabel users, soal, kelas, dan kategori_materi
$query = "SELECT 
            js.*, 
            u.nama AS nama_siswa, 
            u.kelas AS kelas_siswa,
            u.jenjang AS jenjang_siswa,
            s.pertanyaan, 
            s.jawaban AS kunci_jawaban,
            s.kelas_id,
            s.kategori_id,
            km.nama_kategori,
            k.nama_kelas,
            k.jenjang AS jenjang_kelas
          FROM jawaban_siswa js
          JOIN users u ON js.user_id = u.id
          JOIN soal s ON js.soal_id = s.id
          LEFT JOIN kategori_materi km ON s.kategori_id = km.id
          LEFT JOIN kelas k ON s.kelas_id = k.id
          WHERE u.role = 'siswa'";

// Tambahkan kondisi filter jika ada
if (!empty($filter_nama)) {
    $query .= " AND u.nama LIKE '%$filter_nama%'";
}

if (!empty($filter_kelas) && $filter_kelas != 'all') {
    $query .= " AND s.kelas_id = $filter_kelas";
}

if (!empty($filter_kategori) && $filter_kategori != 'all') {
    $query .= " AND s.kategori_id = $filter_kategori";
}

if (!empty($filter_tanggal)) {
    $query .= " AND DATE(js.tanggal) = '$filter_tanggal'";
}

$query .= " ORDER BY js.tanggal DESC";

$result = mysqli_query($conn, $query);

// Ambil data kelas dan kategori untuk dropdown filter
$kelas_query = "SELECT * FROM kelas ORDER BY jenjang, nama_kelas";
$kelas_result = mysqli_query($conn, $kelas_query);

$kategori_query = "SELECT * FROM kategori_materi ORDER BY nama_kategori";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<!-- Begin Page Content - Pastikan struktur ini sesuai dengan template -->
<div class="content">
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Kelola Jawaban Siswa</h1>

        <!-- Filter Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_nama">Nama Siswa</label>
                                <input type="text" class="form-control" id="filter_nama" name="filter_nama" 
                                       value="<?php echo htmlspecialchars($filter_nama); ?>" placeholder="Cari nama siswa">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filter_kelas">Kelas</label>
                                <select class="form-control" id="filter_kelas" name="filter_kelas">
                                    <option value="all">Semua Kelas</option>
                                    <?php 
                                    if ($kelas_result && mysqli_num_rows($kelas_result) > 0) {
                                        while ($row = mysqli_fetch_assoc($kelas_result)): 
                                    ?>
                                        <option value="<?php echo $row['id']; ?>" 
                                            <?php if($filter_kelas == $row['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($row['nama_kelas']); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_kategori">Mata Pelajaran</label>
                                <select class="form-control" id="filter_kategori" name="filter_kategori">
                                    <option value="all">Semua Mata Pelajaran</option>
                                    <?php 
                                    if ($kategori_result && mysqli_num_rows($kategori_result) > 0) {
                                        mysqli_data_seek($kategori_result, 0);
                                        while ($row = mysqli_fetch_assoc($kategori_result)): 
                                    ?>
                                        <option value="<?php echo $row['id']; ?>" 
                                            <?php if($filter_kategori == $row['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filter_tanggal">Tanggal</label>
                                <input type="date" class="form-control" id="filter_tanggal" name="filter_tanggal" 
                                       value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label style="visibility: hidden;">Aksi</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="kelola_jawaban.php" class="btn btn-secondary">
                                        <i class="fas fa-sync"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Jawaban Siswa</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas/Jenjang</th>
                                <th>Mata Pelajaran</th>
                                <th>Pertanyaan</th>
                                <th>Jawaban Siswa</th>
                                <th>Kunci Jawaban</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)): 
                                    $status = $row['benar'] ? '<span class="badge badge-success">Benar</span>' : '<span class="badge badge-danger">Salah</span>';
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($row['kelas_siswa']) && !empty($row['jenjang_siswa'])) {
                                            echo "Kelas " . htmlspecialchars($row['kelas_siswa']) . " " . htmlspecialchars($row['jenjang_siswa']);
                                        } else if (!empty($row['nama_kelas']) && !empty($row['jenjang_kelas'])) {
                                            echo htmlspecialchars($row['nama_kelas']) . " (" . htmlspecialchars($row['jenjang_kelas']) . ")";
                                        } else {
                                            echo "-";
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                                    <td>
                                        <?php 
                                        // Tampilkan sebagian teks pertanyaan
                                        $pertanyaan = strip_tags($row['pertanyaan']);
                                        echo strlen($pertanyaan) > 50 ? substr($pertanyaan, 0, 50) . '...' : $pertanyaan;
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['jawaban'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['kunci_jawaban'] ?? '-'); ?></td>
                                    <td><?php echo $status; ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['tanggal'])); ?></td>
                                </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="9" class="text-center">Tidak ada data jawaban siswa</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

<?php include '../includes/admin_footer.php'; ?>