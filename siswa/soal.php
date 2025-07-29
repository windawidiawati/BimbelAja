<?php
session_start();
include '../config/database.php';
include '../includes/siswa_header_langganan.php';



// Pastikan user adalah siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$kelas_id = $_SESSION['user']['kelas_id'] ?? null;

echo '<div class="container my-4">';

if (isset($_GET['latihan_id'])) {
    $latihan_id = (int)$_GET['latihan_id'];

    

    // Ambil info latihan & pastikan kelas_id cocok
    $qLatihan = mysqli_query($conn, "SELECT * FROM latihan WHERE id = $latihan_id AND kelas_id = $kelas_id");
    $latihan = mysqli_fetch_assoc($qLatihan);

    if ($latihan) {
        echo "<h4 class='mb-3'>Latihan: {$latihan['judul']}</h4>";

        // Ambil soal-soal yang cocok dengan latihan_id dan kelas_id
        $qSoal = mysqli_query($conn, "SELECT * FROM soal WHERE latihan_id = $latihan_id AND kelas_id = $kelas_id");
        if (mysqli_num_rows($qSoal) > 0) {
    // Tampilkan tombol Keluar di atas soal
    echo '<div class="mb-3">';
    echo '<button type="button" onclick="konfirmasiKeluar()" class="btn btn-danger">Keluar</button>';
    echo '</div>';

    $no = 1;
    while ($soal = mysqli_fetch_assoc($qSoal)) {
        echo "

                
                <div class='card mb-3'>
                    <div class='card-body'>
                        <p><strong>Soal $no:</strong> {$soal['pertanyaan']}</p>
                        <ul class='list-unstyled'>
                            <li><input type='radio' name='jawaban_$no'> A. {$soal['opsi_a']}</li>
                            <li><input type='radio' name='jawaban_$no'> B. {$soal['opsi_b']}</li>
                            <li><input type='radio' name='jawaban_$no'> C. {$soal['opsi_c']}</li>
                            <li><input type='radio' name='jawaban_$no'> D. {$soal['opsi_d']}</li>
                        </ul>
                    </div>
                </div>";
                $no++;
            }
        } else {
            echo "<div class='alert alert-warning'>Belum ada soal untuk latihan ini.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Latihan tidak ditemukan atau bukan untuk kelas kamu.</div>";
    }
} else {
    // Daftar latihan berdasarkan kelas_id
    $qLatihan = mysqli_query($conn, "SELECT * FROM latihan WHERE kelas_id = '$kelas_id' ORDER BY created_at DESC");

    echo "<h4 class='mb-3'>Daftar Latihan</h4>";
    if (mysqli_num_rows($qLatihan) > 0) {
        echo "<div class='list-group'>";
        while ($latihan = mysqli_fetch_assoc($qLatihan)) {
            $judul = $latihan['judul'];
            $durasi = $latihan['durasi_menit'];
            $tenggat_waktu = date('d M Y', strtotime($latihan['tenggat_waktu']));
            $id = $latihan['id'];
            echo "<a href='soal.php?latihan_id=$id' class='list-group-item list-group-item-action'>
                    <strong>$judul</strong> <br>
                    Durasi: $durasi menit | Deadline: $tenggat_waktu
                  </a>";
        }
        echo "</div>";
    } else {
        echo "<div class='alert alert-info'>Belum ada latihan tersedia untuk kelas kamu.</div>";
    }
}
echo '<div class="container my-4" style="padding-bottom: 100px;">';
'</div>';
?>
<!-- Modal, Timer, dan Validasi JavaScript -->
<script>
let timerInterval;
let waktuSisa;

function tampilkanPopupDetail(judul, durasi, latihanId) {
  const modalHtml = `
    <div class="modal fade" id="modalLatihan" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Latihan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p><strong>Judul:</strong> ${judul}</p>
            <p><strong>Durasi:</strong> ${durasi} menit</p>
            <p>Jika Anda keluar dari latihan, maka dianggap selesai dan soal yang belum dijawab akan dianggap salah.</p>
          </div>
          <div class="modal-footer">
            <a href="soal.php?start=1&latihan_id=${latihanId}" class="btn btn-primary">Kerjakan Sekarang</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          </div>
        </div>
      </div>
    </div>`;

  document.body.insertAdjacentHTML('beforeend', modalHtml);
  const modal = new bootstrap.Modal(document.getElementById('modalLatihan'));
  modal.show();
}

function mulaiTimer(durasiMenit) {
  waktuSisa = durasiMenit * 60;
  const timerElement = document.getElementById("timer");

  timerInterval = setInterval(() => {
    const menit = Math.floor(waktuSisa / 60);
    const detik = waktuSisa % 60;
    timerElement.textContent = `${menit}m ${detik}s`;

    if (--waktuSisa < 0) {
      clearInterval(timerInterval);
      alert("Waktu habis! Jawaban otomatis dikumpulkan.");
      document.getElementById("formLatihan").submit();
    }
  }, 1000);
}

window.addEventListener("beforeunload", function (e) {
  if (document.getElementById("formLatihan")) {
    e.preventDefault();
    e.returnValue = 'Jika keluar, latihan akan dianggap selesai dan jawaban kosong dianggap salah.';
  }
});

function konfirmasiKeluar() {
  if (confirm("Apakah Anda yakin ingin keluar? Latihan akan dianggap selesai.")) {
    window.location.href = 'soal.php';
  }
}
</script>

<!-- Tambahkan ini di tempat latihan ditampilkan -->
<?php if (!isset($_GET['start']) && isset($_GET['latihan_id'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      tampilkanPopupDetail("<?= $latihan['judul'] ?>", "<?= $latihan['durasi_menit'] ?>", "<?= $latihan_id ?>");
    });
  </script>
<?php endif; ?>

<!-- Tambahkan ini di bagian soal -->
<?php if (isset($_GET['start']) && $latihan): ?>
  <div id="floating-timer">
    Sisa Waktu: <span id="timer"></span>
  </div>
  <script>mulaiTimer(<?= $latihan['durasi_menit'] ?>);</script>
<?php endif; ?>


<?php if (isset($_GET['start'])): ?>
  <form id="formLatihan" method="POST" action="soal.php?latihan_id=<?= $latihan_id ?>">
    <!-- Soal ditampilkan di sini -->

    <button type="submit" name="submit_jawaban" class="btn btn-success">Selesai Mengerjakan</button>
    <?php endif; ?>
</form>

<style>
#floating-timer {
  position: fixed;
  top: 70px;
  right: 20px;
  z-index: 9999;
  background-color: #ffc107; /* warna badge warning */
  color: #000;
  padding: 10px 15px;
  border-radius: 10px;
  font-weight: bold;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
.fixed-btn-left {
  position: fixed;
  bottom: 200px;
  left: 20px;
  z-index: 999;
}
.fixed-btn-right {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 999;
}
body {
  padding-bottom: 100px; /* cukup agar tombol tidak nabrak footer */
}
/* Container umum */
body {
  background-color: #f8f9fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header */
h4.mb-3 {
  font-weight: bold;
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
}

/* Card List Group */
.list-group-item {
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-bottom: 10px;
  padding: 20px;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.list-group-item:hover {
  background-color: #e9f5ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

/* Judul Latihan */
.list-group-item strong {
  color: #0d6efd;
  font-size: 1.1rem;
}

/* Informasi Tambahan */
.list-group-item br + span,
.list-group-item small {
  font-size: 0.9rem;
  color: #6c757d;
}

/* Link */
.list-group-item-action {
  text-decoration: none;
}

/* Tombol (misalnya nanti butuh tombol aksi) */
.btn {
  border-radius: 6px;
}
</style>

