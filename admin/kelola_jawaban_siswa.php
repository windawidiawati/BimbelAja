<?php
session_start();
include '../includes/admin_header.php';
include '../config/database.php';

// Inisialisasi variabel filter
$filter_nama = isset($_GET['filter_nama']) ? $_GET['filter_nama'] : '';
$filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';
$filter_kategori = isset($_GET['filter_kategori']) ? $_GET['filter_kategori'] : '';
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
$filter_jenjang = isset($_GET['filter_jenjang']) ? $_GET['filter_jenjang'] : '';

// Query dasar dengan join ke tabel users, latihan_siswa, latihan, soal, kelas, dan kategori_materi
$query = "SELECT
             ls.*,
             u.nama AS nama_siswa,
             l.judul AS judul_latihan,
             l.kelas_id,
             l.kategori_id,
             km.nama_kategori,
             k.nama_kelas,
             k.jenjang AS jenjang_kelas,
             COUNT(js.id) AS total_soal,
             SUM(js.benar) AS jawaban_benar
           FROM latihan_siswa ls
           JOIN users u ON ls.siswa_id = u.id
           JOIN latihan l ON ls.latihan_id = l.id
           LEFT JOIN jawaban_siswa js ON ls.latihan_id = js.latihan_id AND ls.siswa_id = js.user_id
           LEFT JOIN kategori_materi km ON l.kategori_id = km.id
           LEFT JOIN kelas k ON l.kelas_id = k.id
           WHERE u.role = 'siswa'";

// Tambahkan kondisi filter jika ada
if (!empty($filter_nama)) {
    $query .= " AND u.nama LIKE '%" . mysqli_real_escape_string($conn, $filter_nama) . "%'";
}

// Gunakan l.kelas_id untuk filter kelas
if (!empty($filter_kelas) && $filter_kelas != 'all') {
    $query .= " AND l.kelas_id = " . intval($filter_kelas);
}

if (!empty($filter_kategori) && $filter_kategori != 'all') {
    $query .= " AND l.kategori_id = " . intval($filter_kategori);
}

// Gunakan k.jenjang untuk filter jenjang
if (!empty($filter_jenjang) && $filter_jenjang != 'all') {
    $query .= " AND k.jenjang = '" . mysqli_real_escape_string($conn, $filter_jenjang) . "'";
}

if (!empty($filter_tanggal)) {
    $query .= " AND DATE(ls.waktu_mulai) = '" . mysqli_real_escape_string($conn, $filter_tanggal) . "'";
}

$query .= " GROUP BY ls.id ORDER BY ls.waktu_mulai DESC";

$result = mysqli_query($conn, $query);

// Cek jika query gagal
if (!$result) {
    die("Error in main query: " . mysqli_error($conn));
}

// Hitung statistik - Query terpisah untuk memastikan data akurat
$stats_query = "SELECT
                 COUNT(DISTINCT ls.id) as total_latihan,
                 SUM(js.benar) as jawaban_benar,
                 COUNT(js.id) as total_jawaban,
                 CASE
                     WHEN COUNT(js.id) > 0 THEN ROUND((SUM(js.benar) / COUNT(js.id)) * 100, 2)
                     ELSE 0
                 END as persentase_benar
                 FROM latihan_siswa ls
                 JOIN users u ON ls.siswa_id = u.id
                 JOIN latihan l ON ls.latihan_id = l.id
                 LEFT JOIN jawaban_siswa js ON ls.latihan_id = js.latihan_id AND ls.siswa_id = js.user_id
                 LEFT JOIN kelas k ON l.kelas_id = k.id
                 WHERE u.role = 'siswa'";

// Tambahkan kondisi filter yang sama untuk statistik
if (!empty($filter_nama)) {
    $stats_query .= " AND u.nama LIKE '%" . mysqli_real_escape_string($conn, $filter_nama) . "%'";
}
if (!empty($filter_kelas) && $filter_kelas != 'all') {
    $stats_query .= " AND l.kelas_id = " . intval($filter_kelas);
}

if (!empty($filter_kategori) && $filter_kategori != 'all') {
    $stats_query .= " AND l.kategori_id = " . intval($filter_kategori);
}

if (!empty($filter_jenjang) && $filter_jenjang != 'all') {
    $stats_query .= " AND k.jenjang = '" . mysqli_real_escape_string($conn, $filter_jenjang) . "'";
}

if (!empty($filter_tanggal)) {
    $stats_query .= " AND DATE(ls.waktu_mulai) = '" . mysqli_real_escape_string($conn, $filter_tanggal) . "'";
}

$stats_result = mysqli_query($conn, $stats_query);
if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
} else {
    $stats = [];
    die("Error in stats query: " . mysqli_error($conn));
}

$total_latihan = $stats['total_latihan'] ?? 0;
$jawaban_benar = $stats['jawaban_benar'] ?? 0;
$total_jawaban = $stats['total_jawaban'] ?? 0;
$jawaban_salah = $total_jawaban - $jawaban_benar;
$persentase_benar = $stats['persentase_benar'] ?? 0;

// Fetch data for filter dropdowns (jenjang, kelas, kategori)
$jenjang_result = mysqli_query($conn, "SELECT DISTINCT jenjang FROM kelas");
$kelas_result = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas");
$kategori_result = mysqli_query($conn, "SELECT id, nama_kategori FROM kategori_materi");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jawaban Siswa - BimbelAja</title>
    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    
    <style>
        .content-wrapper {
            min-height: 100vh;
            background-color: #f8f9fc;
        }
        
        .card-statistic {
            border-left: 4px solid;
        }
        
        .card-statistic.border-left-primary {
            border-left-color: #4e73df !important;
        }
        
        .card-statistic.border-left-success {
            border-left-color: #1cc88a !important;
        }
        
        .card-statistic.border-left-danger {
            border-left-color: #e74a3b !important;
        }
        
        .card-statistic.border-left-info {
            border-left-color: #36b9cc !important;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: white;
            min-height: calc(100vh - 56px);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body id="page-top">

    <div id="wrapper">

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <div class="main-content">
                    <div class="container-fluid">

                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 text-gray-800">Kelola Hasil Latihan Siswa</h1>
                        </div>

                        <div class="row mb-4">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card card-statistic border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Latihan</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_latihan; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-list fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card card-statistic border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Jawaban Benar</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $jawaban_benar; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card card-statistic border-left-danger shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    Jawaban Salah</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $jawaban_salah; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card card-statistic border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                    Persentase Benar</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $persentase_benar; ?>%</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-percent fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="filter_nama">Nama Siswa</label>
                                                <input type="text" class="form-control" id="filter_nama" name="filter_nama"
                                                       value="<?php echo htmlspecialchars($filter_nama); ?>" placeholder="Cari nama siswa">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="filter_jenjang">Jenjang</label>
                                                <select class="form-control" id="filter_jenjang" name="filter_jenjang">
                                                    <option value="all">Semua Jenjang</option>
                                                    <?php
                                                    if ($jenjang_result && mysqli_num_rows($jenjang_result) > 0) {
                                                        mysqli_data_seek($jenjang_result, 0);
                                                        while ($row = mysqli_fetch_assoc($jenjang_result)):
                                                    ?>
                                                        <option value="<?php echo $row['jenjang']; ?>"
                                                            <?php if($filter_jenjang == $row['jenjang']) echo 'selected'; ?>>
                                                            <?php echo htmlspecialchars($row['jenjang']); ?>
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
                                                <label for="filter_kelas">Kelas</label>
                                                <select class="form-control" id="filter_kelas" name="filter_kelas">
                                                    <option value="all">Semua Kelas</option>
                                                    <?php
                                                    if ($kelas_result && mysqli_num_rows($kelas_result) > 0) {
                                                        mysqli_data_seek($kelas_result, 0);
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
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label style="visibility: hidden;">Aksi</label>
                                                <div>
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="fas fa-filter"></i> Filter
                                                    </button>
                                                    <a href="kelola_jawaban.php" class="btn btn-secondary btn-block mt-1">
                                                        <i class="fas fa-sync"></i> Reset
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Data Hasil Latihan Siswa</h6>
                                <div>
                                    <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                <th>Kelas/Jenjang</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Judul Latihan</th>
                                                <th>Total Soal</th>
                                                <th>Jawaban Benar</th>
                                                <th>Nilai</th>
                                                <th>Waktu Mulai</th>
                                                <th>Waktu Selesai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            if ($result && mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)):
                                                    $total_soal = $row['total_soal'] ?? 0;
                                                    $jawaban_benar = $row['jawaban_benar'] ?? 0;
                                                    $nilai = $total_soal > 0 ? round(($jawaban_benar / $total_soal) * 100, 2) : 0;
                                            ?>
                                                    <tr>
                                                        <td><?php echo $no++; ?></td>
                                                        <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['nama_kelas']) && !empty($row['jenjang_kelas'])) {
                                                                echo htmlspecialchars($row['nama_kelas']) . " (" . htmlspecialchars($row['jenjang_kelas']) . ")";
                                                            } else {
                                                                echo "-";
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($row['judul_latihan']); ?></td>
                                                        <td class="text-center"><?php echo $total_soal; ?></td>
                                                        <td class="text-center"><?php echo $jawaban_benar; ?></td>
                                                        <td class="font-weight-bold <?php echo $nilai >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $nilai; ?>
                                                        </td>
                                                        <td><?php echo date('d M Y H:i', strtotime($row['waktu_mulai'])); ?></td>
                                                        <td><?php echo !empty($row['waktu_selesai']) ? date('d M Y H:i', strtotime($row['waktu_selesai'])) : '-'; ?></td>
                                                    </tr>
                                            <?php
                                                endwhile;
                                            } else {
                                                echo '<tr><td colspan="10" class="text-center">Tidak ada data hasil latihan siswa</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php include '../includes/admin_footer.php'; ?>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
    function exportToExcel() {
        // Simple Excel export using table data
        let table = document.getElementById('dataTable');
        let html = table.outerHTML;
        let url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
        let downloadLink = document.createElement('a');
        downloadLink.href = url;
        downloadLink.download = 'hasil_latihan_siswa_' + new Date().toISOString().slice(0, 10) + '.xls';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    
    // Initialize DataTables
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true
        });
    });
    </script>

</body>
</html>