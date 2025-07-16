<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

if ($_SESSION['user']['role'] !== 'tutor' && $_SESSION['user']['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

// Daftar kelas lengkap
$daftar_kelas = [
  'Kelas 1 SD', 'Kelas 2 SD', 'Kelas 3 SD', 'Kelas 4 SD', 'Kelas 5 SD', 'Kelas 6 SD',
  'Kelas 7 SMP', 'Kelas 8 SMP', 'Kelas 9 SMP',
  'Kelas 10 SMA IPA', 'Kelas 11 SMA IPA', 'Kelas 12 SMA IPA',
  'Kelas 10 SMA IPS', 'Kelas 11 SMA IPS', 'Kelas 12 SMA IPS'
];

// Ambil filter dari URL
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_kategori = isset($_GET['kategori_id']) ? $_GET['kategori_id'] : '';
?>

<div class="container mt-5 mb-5">
  <h3 class="mb-4 fw-bold">Data Siswa & Nilai Per Mata Pelajaran</h3>

  <!-- Filter Form -->
  <form method="GET" class="row g-3 mb-4 align-items-end">
    <div class="col-md-4">
      <label class="form-label">Filter Kelas:</label>
      <select name="kelas" class="form-select">
        <option value="">Semua Kelas</option>
        <?php foreach ($daftar_kelas as $kelas_option) { ?>
          <option value="<?= $kelas_option; ?>" <?= ($filter_kelas == $kelas_option) ? 'selected' : ''; ?>>
            <?= $kelas_option; ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Filter Mata Pelajaran:</label>
      <select name="kategori_id" class="form-select">
        <option value="">Semua Mapel</option>
        <?php
        $kategori_result = mysqli_query($conn, "SELECT * FROM kategori_materi");
        while ($row = mysqli_fetch_assoc($kategori_result)) { ?>
          <option value="<?= $row['id']; ?>" <?= ($filter_kategori == $row['id']) ? 'selected' : ''; ?>>
            <?= $row['nama_kategori']; ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <div class="col-md-4">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-funnel-fill"></i> Terapkan Filter
      </button>
    </div>
  </form>

  <?php
  // Query utama nilai siswa
  $query = "
    SELECT 
      u.id AS user_id,
      u.nama,
      u.kelas,
      u.jenjang,
      km.nama_kategori,
      COUNT(js.id) AS total_dikerjakan,
      SUM(js.benar) AS total_benar
    FROM users u
    LEFT JOIN jawaban_siswa js ON u.id = js.user_id
    LEFT JOIN soal s ON js.soal_id = s.id
    LEFT JOIN kategori_materi km ON s.kategori_id = km.id
    WHERE u.role = 'siswa'
  ";

  // Tambah filter ke query
  if ($filter_kelas !== '') {
    $query .= " AND u.kelas = '" . mysqli_real_escape_string($conn, $filter_kelas) . "'";
  }
  if ($filter_kategori !== '') {
    $query .= " AND s.kategori_id = '" . mysqli_real_escape_string($conn, $filter_kategori) . "'";
  }

  $query .= " GROUP BY u.id, s.kategori_id ORDER BY u.nama ASC";
  $result = mysqli_query($conn, $query);
  ?>

  <!-- Tabel Data -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
      <thead class="table-primary text-center">
        <tr>
          <th>No</th>
          <th>Nama Siswa</th>
          <th>Kelas</th>
          <th>Jenjang</th>
          <th>Mata Pelajaran</th>
          <th>Nilai</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          $nama_kategori = $row['nama_kategori'] ?? '-';
          $nilai = ($row['total_dikerjakan'] > 0)
            ? round(($row['total_benar'] / $row['total_dikerjakan']) * 100, 2)
            : 'Belum Ada';

          echo "<tr class='align-middle'>";
          echo "<td class='text-center'>{$no}</td>";
          echo "<td>{$row['nama']}</td>";
          echo "<td>{$row['kelas']}</td>";
          echo "<td>{$row['jenjang']}</td>";
          echo "<td>{$nama_kategori}</td>";
          echo "<td class='text-center'>{$nilai}</td>";
          echo "</tr>";

          $no++;
        }

        if ($no === 1) {
          echo "<tr><td colspan='6' class='text-center'>Tidak ada data untuk filter ini.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
