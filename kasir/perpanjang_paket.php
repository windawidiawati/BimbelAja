<?php
include '../includes/kasir_header.php';
include '../config/database.php';

// Helper untuk kode unik transaksi
function generateKodeUnik($prefix = "TRX") {
    return $prefix . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

// Ambil data siswa
$siswa_result = mysqli_query($conn, "SELECT id, username, nama, email, no_hp FROM users WHERE role='siswa' ORDER BY username ASC");

// Ambil paket & kelas
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY jenjang, nama_kelas");
$paket_result = mysqli_query($conn, "SELECT * FROM paket WHERE status='Aktif'");

// === Proses perpanjangan paket ===
if (isset($_POST['perpanjang'])) {
    $user_id = intval($_POST['user_id']);
    $langganan_id = intval($_POST['langganan_id']);
    $kelas_id = intval($_POST['kelas_id']);
    $paket_id = intval($_POST['paket_id']);

    // Ambil data langganan lama
    $sql = $conn->prepare("SELECT * FROM langganan WHERE id=? AND user_id=?");
    $sql->bind_param("ii", $langganan_id, $user_id);
    $sql->execute();
    $result = $sql->get_result();
    $langganan = $result->fetch_assoc();

    // Ambil data paket baru
    $sql_paket = $conn->prepare("SELECT * FROM paket WHERE id=?");
    $sql_paket->bind_param("i", $paket_id);
    $sql_paket->execute();
    $res_paket = $sql_paket->get_result();
    $paket = $res_paket->fetch_assoc();

    if ($langganan && $paket) {
        $today = date('Y-m-d');
        $tanggal_mulai = ($langganan['tanggal_berakhir'] >= $today) ? $langganan['tanggal_berakhir'] : $today;

        // Hitung tanggal berakhir
        if ($paket['durasi'] > 0) {
            switch ($paket['satuan_durasi']) {
                case 'hari':
                    $tanggal_berakhir = date('Y-m-d', strtotime("+{$paket['durasi']} days", strtotime($tanggal_mulai)));
                    break;
                case 'bulan':
                    $tanggal_berakhir = date('Y-m-d', strtotime("+{$paket['durasi']} months", strtotime($tanggal_mulai)));
                    break;
                case 'tahun':
                    $tanggal_berakhir = date('Y-m-d', strtotime("+{$paket['durasi']} years", strtotime($tanggal_mulai)));
                    break;
                default:
                    $tanggal_berakhir = $tanggal_mulai;
            }
        } else {
            $tanggal_berakhir = $tanggal_mulai;
        }

        // 1️⃣ Update langganan
        $update = $conn->prepare("UPDATE langganan 
                                  SET tanggal_mulai=?, tanggal_berakhir=?, paket_id=?, kelas_id=?, status='aktif' 
                                  WHERE id=?");
        $update->bind_param("sssii", $tanggal_mulai, $tanggal_berakhir, $paket_id, $kelas_id, $langganan_id);

        if ($update->execute()) {
            // 2️⃣ Update users
            $updateUser = $conn->prepare("UPDATE users SET status='aktif', kelas_id=? WHERE id=?");
            $updateUser->bind_param("ii", $kelas_id, $user_id);
            $updateUser->execute();

            // 3️⃣ Insert pembayaran
            $jumlah = $paket['harga'];
            $paket_nama = $paket['nama'];
            $metode = "kasir";
            $kode_unik = generateKodeUnik();
            $status_bayar = "lunas";

            $insertBayar = $conn->prepare("INSERT INTO pembayaran 
                (user_id, paket_id, paket, harga, metode, kode_unik, status, tanggal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $insertBayar->bind_param("iisssss", $user_id, $paket_id, $paket_nama, $jumlah, $metode, $kode_unik, $status_bayar);
            $insertBayar->execute();

            $success = "Paket berhasil diperpanjang dengan paket <b>{$paket['nama']}</b> 
                        sampai <b>" . date('d-m-Y', strtotime($tanggal_berakhir)) . "</b>. 
                        <br>Kode Transaksi: <b>{$kode_unik}</b>";
        } else {
            $error = "Gagal memperpanjang paket!";
        }
    } else {
        $error = "Langganan atau Paket tidak ditemukan!";
    }
}

// Ambil data siswa yang dipilih
$selected_siswa = null;
$langganan_siswa = [];
$user_id_show = 0;
if (isset($_POST['user_id_show'])) {
    $user_id_show = intval($_POST['user_id_show']);
    $stmt_siswa = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt_siswa->bind_param("i", $user_id_show);
    $stmt_siswa->execute();
    $res_siswa = $stmt_siswa->get_result();
    $selected_siswa = $res_siswa->fetch_assoc();

    // Ambil langganan aktif siswa
    $stmt = $conn->prepare("SELECT l.id, l.paket_id, l.tanggal_mulai, l.tanggal_berakhir, p.nama
                            FROM langganan l
                            JOIN paket p ON l.paket_id = p.id
                            WHERE l.user_id=? AND l.status='aktif'");
    $stmt->bind_param("i", $user_id_show);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $langganan_siswa[] = $row;
    }
}
?>

<div class="container mt-4">
    <h4 class="mb-3">Perpanjang Paket Siswa</h4>
    <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <!-- Pilih siswa -->
    <form method="post" class="mb-3 row g-3">
        <div class="col-md-4">
            <label class="form-label">Pilih Siswa</label>
            <select name="user_id_show" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Siswa --</option>
                <?php while($s = mysqli_fetch_assoc($siswa_result)): ?>
                    <option value="<?= $s['id'] ?>" <?= ($user_id_show==$s['id'])?'selected':'' ?>>
                        <?= htmlspecialchars($s['username']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>

    <?php if($selected_siswa): ?>
        <!-- Info siswa -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($selected_siswa['nama']) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($selected_siswa['email']) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">No HP</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($selected_siswa['no_hp']) ?>" readonly>
            </div>
        </div>

        <?php if(count($langganan_siswa) > 0): ?>
            <form method="post" id="formPerpanjang">
                <input type="hidden" name="user_id" value="<?= $user_id_show ?>">

                <!-- Pilih langganan -->
                <div class="mb-3">
                    <label class="form-label">Pilih Langganan</label>
                    <?php foreach($langganan_siswa as $l): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="langganan_id" value="<?= $l['id'] ?>" required>
                            <label class="form-check-label">
                                <?= htmlspecialchars($l['nama']) ?> | 
                                <?= date('d-m-Y', strtotime($l['tanggal_mulai'])) ?> s/d 
                                <?= date('d-m-Y', strtotime($l['tanggal_berakhir'])) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pilih Kelas -->
                <div class="mb-3">
                    <label class="form-label">Pilih Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php mysqli_data_seek($kelas_result, 0); ?>
                        <?php while($k = mysqli_fetch_assoc($kelas_result)): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['jenjang'] ?> - <?= $k['nama_kelas'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Pilih Paket -->
                <div class="mb-3">
                    <label class="form-label">Pilih Paket</label>
                    <select name="paket_id" class="form-select" required>
                        <option value="">-- Pilih Paket --</option>
                        <?php mysqli_data_seek($paket_result, 0); ?>
                        <?php while($p = mysqli_fetch_assoc($paket_result)): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= $p['nama'] ?> - Rp <?= number_format($p['harga'],0,',','.') ?> 
                                (<?= $p['durasi'].' '.$p['satuan_durasi'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" name="perpanjang" class="btn btn-primary">Perpanjang Paket</button>
            </form>
        <?php else: ?>
            <p class="text-warning">Siswa ini tidak memiliki langganan aktif.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/kasir_footer.php'; ?>
