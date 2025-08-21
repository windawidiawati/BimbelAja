<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
include '../config/database.php';
$title = "Jadwal Kelas";
include '../includes/siswa_header_langganan.php';

// Cek login dan role siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$today = date('Y-m-d');

// Ambil langganan aktif siswa
$queryLangganan = mysqli_query($conn, "
    SELECT * FROM langganan 
    WHERE user_id = $siswa_id 
      AND status = 'aktif' 
    ORDER BY tanggal_mulai DESC 
    LIMIT 1
");

if (!$queryLangganan || mysqli_num_rows($queryLangganan) === 0) {
    die("Kamu belum memiliki langganan aktif.");
}

$langganan = mysqli_fetch_assoc($queryLangganan);
$kelas_id = isset($langganan['kelas_id']) ? (int)$langganan['kelas_id'] : 0;
$paket_id = isset($langganan['paket_id']) ? (int)$langganan['paket_id'] : 0;

if ($kelas_id === 0 || $paket_id === 0) {
    die("Data kelas atau paket langganan tidak valid.");
}

// --- PAGINATION ---
$limit = 5; // jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$countQuery = "
    SELECT COUNT(*) as total 
    FROM jadwal_offline j
    WHERE j.kelas_id = $kelas_id AND j.paket_id = $paket_id
";
$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// Ambil jadwal berdasarkan kelas, paket, dan tanggal
$today = date('Y-m-d');

$queryJadwal = "
    SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, 
           k.nama_kelas, kat.nama_kategori, u.nama 
    FROM jadwal_offline j
    JOIN kelas k ON j.kelas_id = k.id
    JOIN kategori_materi kat ON j.kategori_id = kat.id
    JOIN users u ON j.tutor_id = u.id AND u.role = 'tutor'
    WHERE j.kelas_id = $kelas_id
      AND j.paket_id = $paket_id
    ORDER BY 
      CASE WHEN j.tanggal < '$today' THEN 1 ELSE 0 END, 
      j.tanggal ASC, 
      j.jam_mulai ASC
      LIMIT $limit OFFSET $offset
";


$result = mysqli_query($conn, $queryJadwal);

if (!$result) {
    die("Terjadi kesalahan saat mengambil data jadwal: " . mysqli_error($conn));
}
// Siapkan data untuk kalender (tanpa paging, semua data ditarik)
$allEventsQuery = "
    SELECT j.tanggal, j.jam_mulai, j.jam_selesai, 
           k.nama_kelas, kat.nama_kategori, u.nama 
    FROM jadwal_offline j
    JOIN kelas k ON j.kelas_id = k.id
    JOIN kategori_materi kat ON j.kategori_id = kat.id
    JOIN users u ON j.tutor_id = u.id AND u.role = 'tutor'
    WHERE j.kelas_id = $kelas_id
      AND j.paket_id = $paket_id
";
$allEventsResult = mysqli_query($conn, $allEventsQuery);
$events = [];
if (mysqli_num_rows($allEventsResult) > 0) {
    while ($row = mysqli_fetch_assoc($allEventsResult)) {
        $events[] = [
            'title' => $row['nama_kelas'] . ' - ' . $row['nama_kategori'],
            'start' => $row['tanggal'] . 'T' . $row['jam_mulai'],
            'end'   => $row['tanggal'] . 'T' . $row['jam_selesai'],
            'tutor' => $row['nama']
        ];
    }
}
$events_json = json_encode($events);
mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Jadwal Kelas Kamu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <style>
    body {
        background-color: #f8f9fa;
    }
    .page-title {
        text-align: center;
        font-weight: bold;
        margin-bottom: 30px;
        color: #007bff;
    }
    .jadwal-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.2s ease-in-out;
        border-left: 5px solid #007bff;
    }
    .jadwal-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }
    .jadwal-date {
        font-size: 1.1rem;
        font-weight: bold;
        color: #343a40;
    }
    .jadwal-info {
        color: #6c757d;
        margin-bottom: 5px;
    }
    .no-jadwal {
        text-align: center;
        font-size: 1.1rem;
        background: #e9f5ff;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #b6e0ff;
    }
  </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary">📅 Jadwal Kelas</h3>
        <button id="toggleView" class="btn btn-outline-primary">Lihat Kalender</button>
    </div>

    <!-- LIST JADWAL -->
    <div id="listJadwal">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                    $waktuMulai = strtotime($row['tanggal'] . ' ' . $row['jam_mulai']);
                    $waktuSelesai = strtotime($row['tanggal'] . ' ' . $row['jam_selesai']);
                    $now = time();

                    if ($now < $waktuMulai) {
                        $status = "<span class='badge bg-primary'>⏳ Belum Mulai</span>";
                    } elseif ($now <= $waktuSelesai) {
                        $status = "<span class='badge bg-success'>🚀 Sedang Berlangsung</span>";
                    } else {
                        $status = "<span class='badge bg-secondary'>✅ Selesai</span>";
                    }
                ?>
                <div class="jadwal-card p-3 mb-3 bg-white rounded shadow-sm">
                    <div class="d-flex justify-content-between">
                        <strong><?= date('l, d M Y', strtotime($row['tanggal'])) ?></strong>
                        <?= $status ?>
                    </div>
                    <div>⏰ <?= $row['jam_mulai'] ?> - <?= $row['jam_selesai'] ?></div>
                    <div>🏫 Kelas: <?= htmlspecialchars($row['nama_kelas']) ?></div>
                    <div>📚 Kategori: <?= htmlspecialchars($row['nama_kategori']) ?></div>
                    <div>👨‍🏫 Tutor: <?= htmlspecialchars($row['nama']) ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">Belum ada jadwal.</div>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page-1 ?>">«</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page+1 ?>">»</a>
                </li>
            </ul>
        </nav>
</div>
    <!-- KALENDER -->
    <div id="calendarView" style="display:none;">
        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleView');
    const listJadwal = document.getElementById('listJadwal');
    const calendarView = document.getElementById('calendarView');
    let calendarRendered = false;

    toggleBtn.addEventListener('click', function () {
        if (listJadwal.style.display !== 'none') {
            listJadwal.style.display = 'none';
            calendarView.style.display = 'block';
            toggleBtn.textContent = 'Lihat List Jadwal';

            if (!calendarRendered) {
                let calendarEl = document.getElementById('calendar');
                let calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: <?php echo $events_json; ?>,
                    eventClick: function(info) {
                        alert(
                            `Kelas: ${info.event.title}\nTutor: ${info.event.extendedProps.tutor}\nMulai: ${info.event.start}\nSelesai: ${info.event.end}`
                        );
                    }
                });
                calendar.render();
                calendarRendered = true;
            }
        } else {
            listJadwal.style.display = 'block';
            calendarView.style.display = 'none';
            toggleBtn.textContent = 'Lihat Kalender';
        }
    });
});
</script>
</body>
</html>
