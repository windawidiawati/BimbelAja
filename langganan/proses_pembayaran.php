<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

$user_id = $_POST['user_id'] ?? null;
$paket_id = $_POST['paket_id'] ?? null;
$metode = $_POST['metode'] ?? null;

if (!$paket_id || !$user_id || !$metode) {
    echo "Data tidak lengkap.";
    exit;
}

// Ambil data paket
$query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "Paket tidak ditemukan atau tidak aktif.";
    exit;
}

$paket = mysqli_fetch_assoc($query);
$nama_paket = $paket['nama'];
$harga = $paket['harga'];
$tanggal = date('Y-m-d H:i:s');

// Tentukan status dan kode bayar
if ($metode === 'tunai') {
    $status = 'menunggu_kasir';
    $kode_bayar = 'TUNAI' . rand(100000, 999999);
} else {
    $status = 'pending';
    $kode_bayar = null;
}

// Simpan ke tabel pembayaran
$stmt = $conn->prepare("INSERT INTO pembayaran (user_id, paket, harga, metode, status, kode_bayar, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isissss", $user_id, $nama_paket, $harga, $metode, $status, $kode_bayar, $tanggal);

if ($stmt->execute()) {
    if ($metode === 'tunai') {
        // Tampilkan kode bayar untuk siswa
        echo "<div style='text-align:center; font-family:Arial; margin-top:50px;'>
                <h2>Pembayaran Tunai</h2>
                <p>Berikan kode berikut ke kasir untuk verifikasi pembayaran:</p>
                <h1 style='color:green;'>$kode_bayar</h1>
                <a href='/BimbelAja/langganan/riwayat.php' style='display:inline-block;margin-top:20px;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>Lihat Riwayat</a>
              </div>";
        exit;
    } else {
        header("Location: /BimbelAja/langganan/riwayat.php");
        exit;
    }
} else {
    echo "Gagal menyimpan pembayaran.";
}
?>
