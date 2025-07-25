<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: /BimbelAja/auth/login.php");
    exit;
}

include_once __DIR__ . '/../config/database.php';
include '../includes/header.php';

$paket_id = $_POST['paket_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;

if (!$paket_id || !$user_id) {
    echo "Data tidak lengkap.";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id AND status = 'aktif'");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "Paket tidak ditemukan.";
    exit;
}

$paket = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pilih Metode Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <h3 class="mb-3">Pembayaran Paket</h3>
        <p><strong>Paket:</strong> <?= htmlspecialchars($paket['nama']) ?> - Rp<?= number_format($paket['harga'], 0, ',', '.') ?></p>

        <div class="alert alert-warning">
            <strong>Catatan:</strong> Pembayaran <strong>tunai</strong> hanya dapat dilakukan langsung di kasir.<br>
            Untuk pembayaran melalui sistem, silakan gunakan metode <strong>transfer bank</strong> berikut.
        </div>

        <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <input type="hidden" name="paket_id" value="<?= htmlspecialchars($paket_id) ?>">
            <input type="hidden" name="metode" value="transfer">

            <!-- Info rekening -->
            <div class="mb-3">
                <div class="alert alert-info">
                    <strong>Transfer ke rekening berikut:</strong><br>
                    Bank: BCA<br>
                    No. Rekening: <strong>1234567890</strong><br>
                    Atas Nama: <strong>BimbelAja Education</strong>
                </div>
            </div>

            <!-- Upload bukti transfer -->
            <div class="mb-3">
                <label class="form-label">Upload Bukti Transfer</label>
                <input type="file" name="bukti_transfer" class="form-control" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Kirim Pembayaran</button>
        </form>
    </div>
</div>
</body>
</html>

<?php include '../includes/footer.php'; ?>
