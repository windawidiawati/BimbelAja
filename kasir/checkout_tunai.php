<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

$success = $error = '';

// Ambil daftar siswa yang belum aktif
$siswa_result = mysqli_query($conn, "
    SELECT id, email, kelas, jenjang 
    FROM users 
    WHERE role='siswa' AND status='belum_aktif'
    ORDER BY email ASC
");

// Ambil daftar paket aktif
$paket_result = mysqli_query($conn, "SELECT * FROM paket WHERE status='aktif' ORDER BY nama ASC");

// Proses form pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id  = intval($_POST['user_id']);
    $paket_id = intval($_POST['paket']);
    $metode   = 'transfer'; // langsung di-set ke transfer
    $status   = 'lunas';
    $tanggal  = date('Y-m-d');

    if (!$user_id || !$paket_id) {
        $error = "Semua field wajib dipilih!";
    } else {
        $paket_query = mysqli_query($conn, "SELECT * FROM paket WHERE id=$paket_id AND status='aktif'");
        $paket_data = mysqli_fetch_assoc($paket_query);

        if (!$paket_data) {
            $error = "Data paket tidak ditemukan.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO pembayaran (user_id, paket, harga, metode, status, tanggal) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssss", $user_id, $paket_data['nama'], $paket_data['harga'], $metode, $status, $tanggal);

            if ($stmt->execute()) {
                mysqli_query($conn, "UPDATE users SET status='aktif' WHERE id=$user_id");

                $user_result = mysqli_query($conn, "SELECT kelas, jenjang FROM users WHERE id=$user_id");
                $user_data = mysqli_fetch_assoc($user_result);
                $kelas = $user_data['kelas'];
                $jenjang = $user_data['jenjang'];

                $tanggal_mulai = $tanggal;
                $date_end = new DateTime($tanggal_mulai);
                if (strtolower($paket_data['nama']) === 'bulanan') {
                    $date_end->modify('+1 month');
                } elseif (strtolower($paket_data['nama']) === 'mingguan') {
                    $date_end->modify('+7 days');
                } else {
                    $date_end->modify('+1 month');
                }
                $tanggal_berakhir = $date_end->format('Y-m-d');

                $stmt2 = $conn->prepare("
                    INSERT INTO langganan (user_id, paket, jenjang, kelas, tanggal_mulai, tanggal_berakhir, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'aktif', NOW())
                ");
                $stmt2->bind_param("isssss", $user_id, $paket_data['nama'], $jenjang, $kelas, $tanggal_mulai, $tanggal_berakhir);
                $stmt2->execute();
                $stmt2->close();

                $success = "Pembayaran transfer berhasil dan langganan ditambahkan!";
            } else {
                $error = "Gagal menyimpan transaksi.";
            }
            $stmt->close();
        }
    }
}

include '../includes/kasir_header.php';

$hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari_ini = $hari[date('l')] . ', ' . date('d-m-Y');
?>

<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="bi bi-bank2 me-2"></i>Pembayaran Transfer (Langsung ke Kasir)</h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body">
            <form method="POST">
                <!-- Dropdown Siswa -->
                <div class="mb-3">
                    <label class="form-label">Pilih Siswa (Email - Kelas)</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($s = mysqli_fetch_assoc($siswa_result)): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['email']) ?> (<?= htmlspecialchars($s['kelas']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Dropdown Paket -->
                <div class="mb-3">
                    <label class="form-label">Pilih Paket</label>
                    <select name="paket" class="form-select" required>
                        <option value="">-- Pilih Paket --</option>
                        <?php while ($p = mysqli_fetch_assoc($paket_result)): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nama']) ?> - Rp<?= number_format($p['harga'], 0, ',', '.') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Tanggal Transaksi -->
                <div class="mb-3">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="text" class="form-control" value="<?= $hari_ini ?>" readonly>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-cash-coin me-1"></i> Simpan Transaksi Transfer
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
