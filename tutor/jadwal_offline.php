<?php
include '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'tutor' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
include '../includes/tutor_header.php';
include '../config/database.php';

$tutor_id = $_SESSION['user']['id'];

// Ambil daftar kelas & mapel
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
$paketList = mysqli_query($conn, "SELECT * FROM paket ORDER BY nama ASC");
$mapel_result = mysqli_query($conn, "SELECT * FROM kategori_materi ORDER BY nama_kategori ASC");

// Filter utama
$filter_kelas = $_GET['kelas_id'] ?? '';
$filter_mapel = $_GET['kategori_id'] ?? '';

$success_msg = "";
$error_msg = "";

// Export ke Excel
// if (isset($_POST['export_excel'])) {
//     header("Content-Type: application/vnd.ms-excel");
//     header("Content-Disposition: attachment; filename=jadwal_offline_" . date('Y-m-d') . ".xls");
//     header("Pragma: no-cache");
//     header("Expires: 0");

//     $export_query = "SELECT jo.*, k.nama_kelas, km.nama_kategori
//                      FROM jadwal_offline jo
//                      LEFT JOIN kelas k ON jo.kelas_id=k.id
//                      LEFT JOIN kategori_materi km ON jo.kategori_id=km.id
//                      WHERE jo.tutor_id='$tutor_id'";
//     if ($filter_kelas) $export_query .= " AND jo.kelas_id='$filter_kelas'";
//     if ($filter_mapel) $export_query .= " AND jo.kategori_id='$filter_mapel'";
//     $export_query .= " ORDER BY jo.tanggal ASC, jo.jam_mulai ASC";

//     $res_export = mysqli_query($conn, $export_query);

//     echo "No\tKelas\tMapel\tTanggal\tJam Mulai\tJam Selesai\tMateri\n";
//     $no = 1;
//     while ($row = mysqli_fetch_assoc($res_export)) {
//         echo $no++ . "\t" . $row['nama_kelas'] . "\t" . $row['nama_kategori'] . "\t" .
//             $row['tanggal'] . "\t" . $row['jam_mulai'] . "\t" . $row['jam_selesai'] . "\t" .
//             $row['materi_file'] . "\n";
//     }
//     exit;
// }

// Variabel default form
$form_mode = "tambah";
$edit_id = $edit_kelas = $edit_mapel = $edit_tanggal = $edit_mulai = $edit_selesai = $edit_file = "";

// Tambah jadwal offline
if (isset($_POST['tambah'])) {
    $paket_id = isset($_POST['paket_id']) ? (int)$_POST['paket_id'] : 0;
    $kelas_id = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $materi_file = '';



//jadwal bentrok
$tutor_id = $_SESSION['user']['id'];
$kategori_id = $_POST['kategori_id'];
$kelas_id = $_POST['kelas_id'];
$tanggal = $_POST['tanggal'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
// $keterangan = $_POST['keterangan'];

// Cek apakah ada jadwal bentrok
$cek = mysqli_query($conn, "
    SELECT * FROM jadwal_offline 
    WHERE tutor_id = $tutor_id 
    AND tanggal = '$tanggal'
    AND (
        ('$jam_mulai' BETWEEN jam_mulai AND jam_selesai)
        OR ('$jam_selesai' BETWEEN jam_mulai AND jam_selesai)
        OR (jam_mulai BETWEEN '$jam_mulai' AND '$jam_selesai')
    )
");

if (mysqli_num_rows($cek) > 0) {
    echo "<script>alert('Jadwal bentrok dengan jadwal lain! Silakan pilih waktu lain.');history.back();</script>";
    exit;
}
    
// Cek apakah siswa (kelas) sudah punya jadwal di waktu yang sama
$cek_siswa_bentrok = mysqli_query($conn, "
    SELECT * FROM jadwal_offline 
    WHERE kelas_id = '$kelas_id'
    AND tanggal = '$tanggal'
    AND (
        ('$jam_mulai' BETWEEN jam_mulai AND jam_selesai)
        OR ('$jam_selesai' BETWEEN jam_mulai AND jam_selesai)
        OR (jam_mulai BETWEEN '$jam_mulai' AND '$jam_selesai')
    )
");

if (mysqli_num_rows($cek_siswa_bentrok) > 0) {
    echo "<script>alert('Siswa sudah memiliki jadwal di waktu tersebut! Silakan pilih waktu lain.');history.back();</script>";
    exit;
}


    // Upload materi
    if (!empty($_FILES['materi_file']['name'])) {
        $allowed_types = ['pdf','ppt','pptx','doc','docx'];
        $ext = strtolower(pathinfo($_FILES['materi_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            $error_msg = "Format file tidak diperbolehkan.";
        } else {
            $target_dir = "../uploads/materi_offline/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES["materi_file"]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["materi_file"]["tmp_name"], $target_file)) {
                $materi_file = $file_name;
            }
        }
    }

    if (empty($error_msg)) {
        mysqli_query($conn, "INSERT INTO jadwal_offline (tutor_id, kelas_id, paket_id, kategori_id, tanggal, jam_mulai, jam_selesai, materi_file)
                             VALUES ('$tutor_id','$kelas_id', '$paket_id', '$kategori_id','$tanggal','$jam_mulai','$jam_selesai','$materi_file')");
        $success_msg = "Jadwal offline berhasil ditambahkan.";
    }
}

// Hapus jadwal
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $cek_file = mysqli_query($conn, "SELECT materi_file FROM jadwal_offline WHERE id='$id'");
    $row_file = mysqli_fetch_assoc($cek_file);
    if (!empty($row_file['materi_file']) && file_exists("../uploads/materi_offline/" . $row_file['materi_file'])) {
        unlink("../uploads/materi_offline/" . $row_file['materi_file']);
    }
    mysqli_query($conn, "DELETE FROM jadwal_offline WHERE id='$id'");
    $success_msg = "Jadwal offline berhasil dihapus.";
}

// Ambil data untuk edit
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM jadwal_offline WHERE id='$edit_id'");
    $data_edit = mysqli_fetch_assoc($res);
    if ($data_edit) {
        $form_mode = "edit";
        $edit_kelas = $data_edit['kelas_id'];
        $edit_mapel = $data_edit['kategori_id'];
        $edit_tanggal = $data_edit['tanggal'];
        $edit_mulai = $data_edit['jam_mulai'];
        $edit_selesai = $data_edit['jam_selesai'];
        $edit_file = $data_edit['materi_file'];
    }
}

// Update jadwal
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $kelas_id = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $materi_file = $_POST['old_file'];

    if (!empty($_FILES['materi_file']['name'])) {
        $allowed_types = ['pdf','ppt','pptx','doc','docx'];
        $ext = strtolower(pathinfo($_FILES['materi_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_types)) {
            $target_dir = "../uploads/materi_offline/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES["materi_file"]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["materi_file"]["tmp_name"], $target_file)) {
                if (!empty($materi_file) && file_exists($target_dir . $materi_file)) {
                    unlink($target_dir . $materi_file);
                }
                $materi_file = $file_name;
            }
        } else {
            $error_msg = "Format file tidak valid.";
        }
    }

    if (empty($error_msg)) {
        mysqli_query($conn, "UPDATE jadwal_offline SET 
                            kelas_id='$kelas_id',
                            kategori_id='$kategori_id',
                            tanggal='$tanggal',
                            jam_mulai='$jam_mulai',
                            jam_selesai='$jam_selesai',
                            materi_file='$materi_file'
                            WHERE id='$id'");
        $success_msg = "Jadwal offline berhasil diperbarui.";
        $form_mode = "tambah";
    }
}



// Query jadwal offline
$query = "SELECT jo.*, k.nama_kelas, p.nama, km.nama_kategori
          FROM jadwal_offline jo
          LEFT JOIN kelas k ON jo.kelas_id=k.id
          LEFT JOIN kategori_materi km ON jo.kategori_id=km.id
          LEFT JOIN paket p ON jo.paket_id = p.id
        --   ORDER BY p.nama ASC
          WHERE jo.tutor_id='$tutor_id'";
if ($filter_kelas) $query .= " AND jo.kelas_id='$filter_kelas'";
if ($filter_mapel) $query .= " AND jo.kategori_id='$filter_mapel'";
$query .= " ORDER BY jo.tanggal ASC, jo.jam_mulai ASC";
$result = mysqli_query($conn, $query);
?>

<div class="content">
    <div class="container py-4">
        <h3 class="fw-bold text-primary mb-3"><?= ($form_mode == "edit") ? "✏️ Edit Jadwal Offline" : "➕ Tambah Jadwal Offline" ?></h3>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg; ?></div>
        <?php elseif ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg; ?></div>
        <?php endif; ?>

        <!-- Form Jadwal Offline -->
        <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm mb-4">
            <?php if ($form_mode == "edit") { ?>
                <input type="hidden" name="id" value="<?= $edit_id; ?>">
                <input type="hidden" name="old_file" value="<?= $edit_file; ?>">
            <?php } ?>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Kelas:</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">Pilih Kelas</option>
                        <?php mysqli_data_seek($kelas_result, 0); while ($k = mysqli_fetch_assoc($kelas_result)) { ?>
                            <option value="<?= $k['id']; ?>" <?= ($edit_kelas == $k['id']) ? 'selected' : ''; ?>><?= $k['nama_kelas']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                <label for="paket" class="form-label">Paket</label>
                <select name="paket_id" class="form-select" required>
                    <option value="">-- Pilih Paket --</option>
                    <?php while ($row = mysqli_fetch_assoc($paketList)) : ?>
                        <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                    <?php endwhile; ?>
                </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mata Pelajaran:</label>
                    <select name="kategori_id" class="form-select" required>
                        <option value="">Pilih Mapel</option>
                        <?php mysqli_data_seek($mapel_result, 0); while ($m = mysqli_fetch_assoc($mapel_result)) { ?>
                            <option value="<?= $m['id']; ?>" <?= ($edit_mapel == $m['id']) ? 'selected' : ''; ?>><?= $m['nama_kategori']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal:</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= $edit_tanggal; ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Jam Mulai:</label>
                    <input type="time" name="jam_mulai" class="form-control" value="<?= $edit_mulai; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jam Selesai:</label>
                    <input type="time" name="jam_selesai" class="form-control" value="<?= $edit_selesai; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Upload Materi (PDF/PPT/DOC):</label>
                    <input type="file" name="materi_file" class="form-control" accept=".pdf,.ppt,.pptx,.doc,.docx">
                    <?php if ($edit_file) { echo "<small>File saat ini: $edit_file</small>"; } ?>
                </div>
            </div>
            <div class="text-end">
                <?php if ($form_mode == "edit") { ?>
                    <button type="submit" name="update" class="btn btn-success"><i class="bi bi-check-circle"></i> Update</button>
                    <a href="jadwal_offline.php" class="btn btn-secondary">Batal</a>
                <?php } else { ?>
                    <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah</button>
                <?php } ?>
            </div>
        </form>

        <!-- Filter -->
        <div class="card shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3">🔍 Filter Jadwal</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <select name="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        <?php mysqli_data_seek($kelas_result, 0); while ($k = mysqli_fetch_assoc($kelas_result)) { ?>
                            <option value="<?= $k['id']; ?>" <?= ($filter_kelas == $k['id']) ? 'selected' : ''; ?>><?= $k['nama_kelas']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="kategori_id" class="form-select">
                        <option value="">Semua Mapel</option>
                        <?php mysqli_data_seek($mapel_result, 0); while ($m = mysqli_fetch_assoc($mapel_result)) { ?>
                            <option value="<?= $m['id']; ?>" <?= ($filter_mapel == $m['id']) ? 'selected' : ''; ?>><?= $m['nama_kategori']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
    <label for="paket_id" class="form-label">Paket</label>
    <select name="paket_id" class="form-select" required>
        <option value="">-- Pilih Paket --</option>
        <?php
        $query_paket = mysqli_query($conn, "SELECT * FROM paket");
        while ($paket = mysqli_fetch_assoc($query_paket)) {
            echo "<option value='{$paket['id']}'>{$paket['nama_paket']}</option>";
        }
        ?>
    </select>
</div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter</button>
                </div>
            </form>
        </div>

        <!-- Export Excel -->
        <!-- <form method="POST" class="mb-3">
            <button type="submit" name="export_excel" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export ke Excel
            </button>
        </form> -->

        <!-- Tabel Jadwal Offline -->
        <div class="card shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3">📋 Jadwal Offline</h5>
              <!-- Notes -->
            <div class="alert alert-warning mb-3" role="alert">
                <strong>Catatan:</strong> Jadwal yang dibuat di sini akan otomatis muncul di menu <em>Absensi</em>.
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Kelas</th>
                            <th>Paket</th>
                            <th>Mapel</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Materi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $materi_link = $row['materi_file'] ? 
                                "<a href='../uploads/materi_offline/{$row['materi_file']}' target='_blank' class='btn btn-sm btn-info'><i class='bi bi-download'></i></a>" : "-";
                            echo "<tr>
                                    <td>{$no}</td>
                                    <td>{$row['nama_kelas']}</td>
                                    <td>" . htmlspecialchars(isset($row['nama']) ? $row['nama'] : '-') . "</td>
                                    <td>{$row['nama_kategori']}</td>
                                    <td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>
                                    <td>{$row['jam_mulai']} - {$row['jam_selesai']}</td>
                                    <td>{$materi_link}</td>
                                    <td>
                                        <a href='?edit={$row['id']}' class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></a>
                                        <a href='?hapus={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Hapus jadwal ini?')\"><i class='bi bi-trash'></i></a>
                                    </td>
                                  </tr>";
                            $no++;
                        }
                        if ($no == 1) {
                            echo "<tr><td colspan='7'>Tidak ada jadwal offline.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

<!-- Jadwal Offline Per Hari -->
<div class="card shadow-sm p-4 mt-5">
    <h5 class="fw-bold mb-3">📅 Jadwal Offline Per Hari</h5>
    
    <!-- Filter kelas per hari -->
    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <select name="filter_kelas_perhari" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                <?php 
                mysqli_data_seek($kelas_result, 0); 
                $filter_kelas_perhari = $_GET['filter_kelas_perhari'] ?? '';
                while ($k = mysqli_fetch_assoc($kelas_result)) { 
                    $selected = ($filter_kelas_perhari == $k['id']) ? 'selected' : '';
                    echo "<option value='{$k['id']}' $selected>{$k['nama_kelas']}</option>";
                } 
                ?>
            </select>
        </div>
    </form>

    <?php
    // Ambil semua jadwal
   $jadwal_perhari_query = "
    SELECT jo.tanggal, k.nama_kelas, p.nama, km.nama_kategori, jo.jam_mulai, jo.jam_selesai
    FROM jadwal_offline jo
    JOIN kelas k ON jo.kelas_id = k.id
    JOIN kategori_materi km ON jo.kategori_id = km.id
    LEFT JOIN paket p ON jo.paket_id = p.id
    WHERE jo.tutor_id = '$tutor_id'
";
if ($filter_kelas_perhari) {
    $jadwal_perhari_query .= " AND jo.kelas_id = '$filter_kelas_perhari'";
}
$jadwal_perhari_query .= " ORDER BY p.nama ASC, jo.tanggal ASC, jo.jam_mulai ASC";

    $jadwal_perhari = mysqli_query($conn, $jadwal_perhari_query);

    $hari_indo = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $hari_ini = date('Y-m-d');
    $besok = date('Y-m-d', strtotime('+1 day'));

    $jadwal_today = [];
    $jadwal_tomorrow = [];
    $jadwal_others = [];

    while ($row = mysqli_fetch_assoc($jadwal_perhari)) {
        if ($row['tanggal'] == $hari_ini) {
            $jadwal_today[] = $row;
        } elseif ($row['tanggal'] == $besok) {
            $jadwal_tomorrow[] = $row;
        } else {
            $jadwal_others[] = $row;
        }
    }

    $all_jadwal = array_merge($jadwal_today, $jadwal_tomorrow, $jadwal_others);

    if (count($all_jadwal) == 0) {
        echo "<div class='alert alert-warning'>Tidak ada jadwal offline.</div>";
    } else {
        $current_day = '';
        $current_date = '';
        $current_class = '';

        foreach ($all_jadwal as $row) {
            $hari = $hari_indo[date('l', strtotime($row['tanggal']))];
            $tanggal = date('d-m-Y', strtotime($row['tanggal']));
            $kelas = $row['nama_kelas'];

            if ($row['tanggal'] != $current_date) {
                if ($current_class != '') echo "</tbody></table>";
                echo "<h6 class='mt-4 text-primary fw-bold'>📆 $hari ($tanggal)</h6>";
                $current_day = $hari;
                $current_date = $row['tanggal'];
                $current_class = '';
            }

            if ($kelas != $current_class) {
                if ($current_class != '') echo "</tbody></table>";
                echo "<h6 class='mt-3 text-success fw-bold'>Kelas: $kelas</h6>";
                echo "<table class='table table-bordered text-center'>
                        <thead class='table-secondary'>
                            <tr>
                                <th>Jam</th>
                                <th>Mapel</th>
                            </tr>
                        </thead>
                        <tbody>";
                $current_class = $kelas;
            }

            echo "<tr>
                    <td>{$row['jam_mulai']} - {$row['jam_selesai']}</td>
                    <td>{$row['nama_kategori']}</td>
                  </tr>";
        }

        if ($current_class != '') echo "</tbody></table>";
    }
    ?>
</div>


<?php include '../includes/tutor_footer.php'; ?>
