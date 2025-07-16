<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';

// Proses pembayaran saat tombol diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paket_id'])) {
    $user_id = $_SESSION['user']['id'];
    $paket_id = (int) $_POST['paket_id'];

    // Ambil data paket dari database
    $query = "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'";
    $result = mysqli_query($conn, $query);
    if (!$result || mysqli_num_rows($result) == 0) {
        echo "Paket tidak ditemukan atau tidak aktif.";
        exit;
    }

    $paket = mysqli_fetch_assoc($result);
    $nama_paket = $paket['nama'];
    $harga = $paket['harga'];
    $tanggal = date('Y-m-d H:i:s');
    $status = 'pending';

    // Simpan ke tabel pembayaran
    $stmt = $conn->prepare("INSERT INTO pembayaran (user_id, paket, harga, status, tanggal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $user_id, $nama_paket, $harga, $status, $tanggal);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: /BimbelAja/langganan/proses_pembayaran.php");
        exit;
    } else {
        echo "Gagal memproses pembayaran.";
        exit;
    }
}

// Jika GET (tampilan checkout)
$paket_id = $_GET['paket_id'] ?? null;
if (!$paket_id) {
    echo "ID paket tidak ditemukan!";
    exit;
}

$query = "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo "Paket tidak ditemukan atau tidak aktif.";
    exit;
}
$paket = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Checkout - <?= htmlspecialchars($paket['nama']) ?> | BimbelAja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2>Checkout Paket: <?= htmlspecialchars($paket['nama']) ?></h2>
  <p><strong>Kategori:</strong> <?= htmlspecialchars($paket['kategori']) ?></p>
  <p><strong>Jenjang:</strong> <?= htmlspecialchars($paket['jenjang']) ?> - Kelas <?= htmlspecialchars($paket['kelas']) ?></p>
  <p><strong>Harga:</strong> Rp <?= number_format($paket['harga'], 0, ',', '.') ?></p>
  <p><strong>Durasi:</strong> <?= $paket['durasi'] . ' ' . $paket['satuan_durasi'] ?></p>
  <p><strong>Deskripsi:</strong> <?= htmlspecialchars($paket['deskripsi']) ?></p>

  <form method="post">
    <input type="hidden" name="paket_id" value="<?= $paket['id'] ?>">
    <button type="submit" class="btn btn-success">Lanjut ke Pembayaran</button>
  </form>
</div>
</body>
</html>
