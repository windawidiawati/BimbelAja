<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];
$latihan_id = $_GET['id'] ?? null;

if (!$latihan_id) {
    header("Location: soal.php");
    exit;
}

$sql = "SELECT * FROM latihan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $latihan_id);
$stmt->execute();
$latihan = $stmt->get_result()->fetch_assoc();

if (!$latihan) {
    header("Location: soal.php");
    exit;
}

$now = date('Y-m-d H:i:s');
if ($now > $latihan['tenggat_waktu']) {
    echo "<script>alert('Latihan sudah lewat tenggat waktu!'); window.location='soal.php';</script>";
    exit;
}

$sql = "SELECT * FROM jawaban_siswa WHERE latihan_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $latihan_id, $siswa_id);
$stmt->execute();
$cek = $stmt->get_result();

if ($cek->num_rows > 0) {
    header("Location: soal.php?msg=sudah_dikerjakan");
    exit;
}

$sql = "SELECT id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d 
        FROM soal 
        WHERE latihan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $latihan_id);
$stmt->execute();
$soal = $stmt->get_result();

$durasi_detik = intval($latihan['durasi_menit'] ?? 0) * 60;
$end_time = time() + $durasi_detik;

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($latihan['judul']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #f0f8ff, #e6f7ff);
        font-family: 'Segoe UI', sans-serif;
    }
    .container {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        margin-top: 30px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.08);
    }
    h3 {
        background: linear-gradient(to right, #0d6efd, #00c4ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .timer-box {
    position: fixed; /* atau absolute kalau mau relatif ke parent tertentu */
    top: 50px;
    right: 130px;
    background: linear-gradient(to right, #ffecd2, #fcb69f);
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: bold;
    color: #5a2e00;
    box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
    z-index: 1000; /* biar di atas elemen lain */
}

    .soal-box {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        background: #fdfdfd;
        border-left: 6px solid #0d6efd;
        transition: all 0.2s ease-in-out;
    }
    .soal-box:nth-child(even) {
        border-left-color: #ff7f50; /* Oranye untuk variasi */
    }
    .soal-box:hover {
        background: #f7faff;
        transform: scale(1.01);
    }
    .form-check {
        margin-bottom: 8px;
        padding: 6px;
        border-radius: 5px;
        transition: background 0.2s;
    }
    .form-check:hover {
        background: #f1f9ff;
    }
    button.btn-primary {
        background: linear-gradient(to right, #0d6efd, #00c4ff);
        border: none;
        font-weight: bold;
        box-shadow: 0px 3px 6px rgba(0,0,0,0.15);
        transition: transform 0.2s ease;
    }
    button.btn-primary:hover {
        transform: translateY(-2px);
    }
    .form-check {
    display: flex;
    align-items: center; /* sejajarkan bulet & teks */
    gap: 8px; /* jarak bulet & teks */
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
    padding: 6px 12px;
}

.form-check-input {
    margin: 0 !important; /* hapus margin bawaan */
    width: 18px;
    height: 18px;
    accent-color: #0d6efd; /* warna bulet */
}

.form-check-label {
    cursor: pointer;
    flex: 1; /* teks memenuhi sisa ruang */
}
</style>
</head>
<body>

<div class="container">
    <h3><?= htmlspecialchars($latihan['judul']) ?></h3>
    <div class="mb-3">
        <div class="timer-box">
            ⏳ Sisa Waktu: <span id="timer"></span>
        </div>
    </div>

    <form id="formLatihan" action="submit_latihan.php" method="POST">
        <input type="hidden" name="latihan_id" value="<?= $latihan_id ?>">

        <?php $no = 1; while ($row = $soal->fetch_assoc()): ?>
            <div class="soal-box">
                <p><strong><?= $no++ ?>. <?= htmlspecialchars($row['pertanyaan']) ?></strong></p>
                <?php foreach (['A', 'B', 'C', 'D'] as $opsi): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="jawaban[<?= $row['id'] ?>]" 
                               value="<?= $opsi ?>" 
                               id="<?= $opsi . $row['id'] ?>">
                        <label class="form-check-label" for="<?= $opsi . $row['id'] ?>">
                            <?= $opsi ?>. <?= htmlspecialchars($row['opsi_' . strtolower($opsi)]) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endwhile; ?>

        <button type="submit" class="btn btn-primary mt-3">
            🚀 Kirim Jawaban
        </button>
    </form>
</div>

<script>
let endTime = localStorage.getItem("latihan_<?= $latihan_id ?>_endtime");

if (!endTime) {
    // Simpan end time baru kalau belum ada
    endTime = <?= $end_time ?> * 1000; // ubah ke ms
    localStorage.setItem("latihan_<?= $latihan_id ?>_endtime", endTime);
} else {
    endTime = parseInt(endTime);
}

function updateTimer() {
    let now = Date.now();
    let sisa = Math.floor((endTime - now) / 1000);

    if (sisa <= 0) {
        clearInterval(countdown);
        alert("Waktu habis! Jawaban akan dikirim otomatis.");
        document.getElementById("formLatihan").submit();
        return;
    }

    let menit = Math.floor(sisa / 60);
    let detik = sisa % 60;
    document.getElementById("timer").textContent = `${menit}:${detik < 10 ? '0' : ''}${detik}`;
}

updateTimer();
let countdown = setInterval(updateTimer, 1000);

// Simpan jawaban ketika user memilih
document.querySelectorAll(".form-check-input").forEach(input => {
    input.addEventListener("change", function() {
        let latihanId = <?= $latihan_id ?>;
        let jawabanSiswa = JSON.parse(localStorage.getItem("latihan_" + latihanId + "_jawaban") || "{}");
        jawabanSiswa[this.name] = this.value;
        localStorage.setItem("latihan_" + latihanId + "_jawaban", JSON.stringify(jawabanSiswa));
    });
});

// Pas halaman dimuat, ambil jawaban yang tersimpan
window.addEventListener("DOMContentLoaded", () => {
    let latihanId = <?= $latihan_id ?>;
    let jawabanSiswa = JSON.parse(localStorage.getItem("latihan_" + latihanId + "_jawaban") || "{}");
    for (let name in jawabanSiswa) {
        let value = jawabanSiswa[name];
        let input = document.querySelector(`input[name="${name}"][value="${value}"]`);
        if (input) {
            input.checked = true;
        }
    }
});

// Saat form disubmit, hapus data jawaban
document.getElementById("formLatihan").addEventListener("submit", function() {
    let latihanId = <?= $latihan_id ?>;
    localStorage.removeItem("latihan_" + latihanId + "_jawaban");
    localStorage.removeItem("latihan_" + latihanId + "_endtime"); // sekalian hapus timer
});


</script>

</body>
</html>


