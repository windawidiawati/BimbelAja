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

$kode_unik = null;
$bukti_transfer = null;

// Tentukan status & kode unik
if ($metode === 'tunai') {
    $status = 'menunggu_kasir';
    $kode_unik = 'TUNAI' . rand(100000, 999999);
} else {
    $status = 'pending';

    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/bukti_transfer/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $ext = pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("bukti_") . "." . $ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $target_file)) {
            $bukti_transfer = $filename;
        }
    }
}

$stmt = $conn->prepare("INSERT INTO pembayaran (user_id, paket, harga, metode, kode_unik, status, tanggal, bukti_transfer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isisssss", $user_id, $nama_paket, $harga, $metode, $kode_unik, $status, $tanggal, $bukti_transfer);

if ($stmt->execute()) {
    if ($metode === 'tunai') {
        echo "<div style='text-align:center; margin-top:50px;'>
                <h2>Pembayaran Tunai</h2>
                <p>Tunjukkan kode ini ke kasir:</p>
                <h1 style='color:green;'>$kode_unik</h1>
                <a href='/BimbelAja/langganan/riwayat.php' class='btn btn-success mt-3'>Lihat Riwayat</a>
              </div>";
    } else {
        header("Location: /BimbelAja/langganan/riwayat.php");
    }
} else {
    echo "Gagal menyimpan pembayaran.";
}
?>
