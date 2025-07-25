<?php
include '../config/database.php';
include '../includes/auth.php';
include '../includes/admin_header.php';



// Ambil data
$sql_users = "SELECT role, COUNT(*) AS total FROM users WHERE role IN ('siswa', 'tutor') GROUP BY role";
$result_users = $conn->query($sql_users);
$jumlah_pengguna = ['siswa' => 0, 'tutor' => 0];
while ($row = $result_users->fetch_assoc()) {
    $jumlah_pengguna[$row['role']] = $row['total'];
}
$jumlah_siswa = $jumlah_pengguna['siswa'];
$jumlah_tutor = $jumlah_pengguna['tutor'];

$sql_pembayaran = "SELECT status, COUNT(*) AS total FROM pembayaran GROUP BY status";
$result_pembayaran = $conn->query($sql_pembayaran);
$stat_pembayaran = ['lunas' => 0, 'ditolak' => 0, 'pending' => 0];
while ($row = $result_pembayaran->fetch_assoc()) {
    $stat_pembayaran[$row['status']] = $row['total'];
}

$sql_kelas_online = "SELECT u.nama AS tutor, COUNT(*) AS total_kelas 
                     FROM kelas_online ko
                     JOIN users u ON ko.tutor_id = u.id
                     GROUP BY ko.tutor_id";
$result_kelas = $conn->query($sql_kelas_online);
$tutor_labels = [];
$tutor_kelas = [];
while ($row = $result_kelas->fetch_assoc()) {
    $tutor_labels[] = $row['tutor'];
    $tutor_kelas[] = $row['total_kelas'];
}
?>

<!-- Mulai konten -->
<div class="content">

  <div class="container mt-4">
      <h3 class="text-center mb-4">📊 Statistik Sistem</h3>

      <div class="row justify-content-center">
          <div class="col-sm-6 col-md-5 col-lg-4 mb-3">
              <div class="card text-white bg-primary shadow-sm text-center">
                  <div class="card-body">
                      <h5 class="card-title">Total Siswa</h5>
                      <h3><?= $jumlah_siswa ?></h3>
                  </div>
              </div>
          </div>
          <div class="col-sm-6 col-md-5 col-lg-4 mb-3">
              <div class="card text-white bg-success shadow-sm text-center">
                  <div class="card-body">
                      <h5 class="card-title">Total Tutor</h5>
                      <h3><?= $jumlah_tutor ?></h3>
                  </div>
              </div>
          </div>
      </div>

      <!-- Grafik Pembayaran -->
      <div class="card mb-4 shadow-sm mx-auto" style="max-width: 800px;">
          <div class="card-header bg-info text-white">
              Grafik Status Pembayaran
          </div>
          <div class="card-body d-flex justify-content-center">
              <div style="width: 100%; max-width: 400px;">
                  <canvas id="pembayaranChart" style="max-height: 300px;"></canvas>
              </div>
          </div>
      </div>

      <!-- Grafik Kelas Online -->
      <div class="card mb-4 shadow-sm mx-auto" style="max-width: 800px;">
          <div class="card-header bg-secondary text-white">
              Jumlah Kelas Online per Tutor
          </div>
          <div class="card-body d-flex justify-content-center">
              <div style="width: 100%; max-width: 600px;">
                  <canvas id="kelasOnlineChart" style="max-height: 300px;"></canvas>
              </div>
          </div>
      </div>
  </div>

</div> <!-- Tutup .content -->

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('pembayaranChart'), {
    type: 'doughnut',
    data: {
        labels: ['Lunas', 'Ditolak', 'Pending'],
        datasets: [{
            label: 'Status Pembayaran',
            data: [<?= $stat_pembayaran['lunas'] ?>, <?= $stat_pembayaran['ditolak'] ?>, <?= $stat_pembayaran['pending'] ?>],
            backgroundColor: ['#4CAF50', '#F44336', '#FFC107']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Status Pembayaran' }
        }
    }
});

new Chart(document.getElementById('kelasOnlineChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($tutor_labels) ?>,
        datasets: [{
            label: 'Jumlah Kelas',
            data: <?= json_encode($tutor_kelas) ?>,
            backgroundColor: '#3f51b5'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Kelas Online per Tutor' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>