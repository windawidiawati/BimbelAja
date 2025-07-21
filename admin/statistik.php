<?php
include '../config/database.php';
include '../includes/auth.php';
include '../includes/admin_header.php';

// 1. Jumlah siswa & tutor
$sql_user = "SELECT role, COUNT(*) AS total FROM users WHERE role IN ('siswa', 'tutor') GROUP BY role";
$result_user = $conn->query($sql_user);
$user_data = ['siswa' => 0, 'tutor' => 0];
while ($row = $result_user->fetch_assoc()) {
    $user_data[$row['role']] = $row['total'];
}

// 2. Jumlah materi
$total_materi = $conn->query("SELECT COUNT(*) as total FROM materi")->fetch_assoc()['total'];

// 3. Jumlah soal
$total_soal = $conn->query("SELECT COUNT(*) as total FROM soal")->fetch_assoc()['total'];

// 4. Pembayaran total & bulan ini
$bulan_ini = date('Y-m');
$sql_total_pembayaran = "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'lunas'";
$sql_bulan_ini = "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'lunas' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'";

$total_pembayaran = $conn->query($sql_total_pembayaran)->fetch_assoc()['total'];
$pembayaran_bulan_ini = $conn->query($sql_bulan_ini)->fetch_assoc()['total'];
?>

<div class="container">
    <h2>📊 Statistik Admin BimbelAja</h2>
    <hr>

    <!-- Chart Pengguna -->
    <div style="width: 80%; margin: auto;">
        <canvas id="userChart"></canvas>
    </div>
    <br>

    <!-- Chart Pembayaran -->
    <div style="width: 80%; margin: auto;">
        <canvas id="pembayaranChart"></canvas>
    </div>
    <br>

    <!-- Tabel Ringkasan -->
    <table border="1" cellpadding="10" cellspacing="0" style="width: 80%; margin: auto;">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Total Siswa</td><td><?= $user_data['siswa'] ?></td></tr>
            <tr><td>Total Tutor</td><td><?= $user_data['tutor'] ?></td></tr>
            <tr><td>Total Materi</td><td><?= $total_materi ?></td></tr>
            <tr><td>Total Soal</td><td><?= $total_soal ?></td></tr>
            <tr><td>Total Pembayaran (Lunas)</td><td><?= $total_pembayaran ?></td></tr>
            <tr><td>Pembayaran Bulan Ini</td><td><?= $pembayaran_bulan_ini ?></td></tr>
        </tbody>
    </table>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxUser = document.getElementById('userChart').getContext('2d');
new Chart(ctxUser, {
    type: 'bar',
    data: {
        labels: ['Siswa', 'Tutor'],
        datasets: [{
            label: 'Jumlah Pengguna',
            data: [<?= $user_data['siswa'] ?>, <?= $user_data['tutor'] ?>],
            backgroundColor: ['#4CAF50', '#2196F3']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Statistik Pengguna'
            }
        }
    }
});

const ctxBayar = document.getElementById('pembayaranChart').getContext('2d');
new Chart(ctxBayar, {
    type: 'pie',
    data: {
        labels: ['Bulan Ini', 'Sebelumnya'],
        datasets: [{
            label: 'Pembayaran',
            data: [<?= $pembayaran_bulan_ini ?>, <?= $total_pembayaran - $pembayaran_bulan_ini ?>],
            backgroundColor: ['#FF6384', '#FFCE56']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Pembayaran Lunas'
            }
        }
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>
