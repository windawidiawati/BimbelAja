<?php 
include '../config/database.php';
$pesan = '';

// Generate kode unik transaksi final (digunakan juga di database)
$kode_unik = 'TRX' . date('Ymd') . rand(100, 999);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password_plain = $_POST['password']; // Simpan password asli
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $kelas_id = $_POST['kelas_id'];
    $paket_id = $_POST['paket_id'];
    $metode = $_POST['metode'];

    // Ambil data kelas
    $kelas_query = mysqli_query($conn, "SELECT * FROM kelas WHERE id = $kelas_id");
    $kelas_data = mysqli_fetch_assoc($kelas_query);
    $kelas = $kelas_data['nama_kelas'];
    $jenjang = $kelas_data['jenjang'];

    // Ambil data paket
    $paket_query = mysqli_query($conn, "SELECT * FROM paket WHERE id = $paket_id");
    $paket_data = mysqli_fetch_assoc($paket_query);
    $paket = $paket_data['nama'];
    $harga = $paket_data['harga'];
    $durasi = $paket_data['durasi'];
    $satuan_durasi = $paket_data['satuan_durasi'];

    // Insert ke tabel users
    $insert_user = mysqli_query($conn, "INSERT INTO users (username, password, role, nama, kelas_id, jenjang, email, no_hp, status) 
        VALUES ('$username', '$password', 'siswa', '$nama', '$kelas_id', '$jenjang', '$email', '$no_hp', 'aktif')");

    if ($insert_user) {
        $user_id = mysqli_insert_id($conn);
        $tanggal = date('Y-m-d');

        // Insert ke pembayaran dengan kode unik final
        $insert_bayar = mysqli_query($conn, "INSERT INTO pembayaran (user_id, paket_id, paket, harga, metode, status, tanggal, kode_unik) 
            VALUES ($user_id, $paket_id, '$paket', $harga, '$metode', 'Lunas', '$tanggal', '$kode_unik')");
        $pembayaran_id = mysqli_insert_id($conn); // Ambil ID pembayaran langsung setelah insert pembayaran

        // Hitung tanggal berakhir langganan
        $tanggal_mulai = $tanggal;
        if ($satuan_durasi === 'bulan') {
            $tanggal_berakhir = date('Y-m-d', strtotime("+$durasi months"));
        } elseif ($satuan_durasi === 'tahun') {
            $tanggal_berakhir = date('Y-m-d', strtotime("+$durasi years"));
        } else {
            $tanggal_berakhir = date('Y-m-d', strtotime("+$durasi days"));
        }

        // Insert ke langganan
        $insert_langganan = mysqli_query($conn, "INSERT INTO langganan (
            user_id, paket_id, paket, jenjang, kelas_id, tanggal_mulai, tanggal_berakhir, status, created_at
        ) VALUES (
            $user_id, $paket_id, '$paket', '$jenjang', $kelas_id, '$tanggal_mulai', '$tanggal_berakhir', 'Aktif', NOW()
        )");

        if ($insert_bayar && $insert_langganan) {
        // Kirim email ke siswa
        require 'fungsi_email.php'; // path sesuai
    $email_result = kirimEmailSiswa($email, $nama, $username, $password_plain);
        
        if ($email_result === true) {
            // Email berhasil
            header("Location: ../admin/cetak_transaksi.php?id=$pembayaran_id");
            exit;
        } else {
            $pesan = '<div class="alert alert-warning">Data berhasil disimpan, tapi ' . $email_result . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-danger">Gagal menyimpan pembayaran atau langganan.</div>';
    }

    } else {
        $pesan = '<div class="alert alert-danger">Gagal menyimpan data siswa.</div>';
    }
}

include '../includes/kasir_header.php';
?>

<div class="container mt-4">
    <h4 class="mb-3">Form Tambah Siswa & Pembayaran</h4>
    <?= $pesan ?>

    <!-- Tampilkan kode transaksi final -->
    <div class="mb-3">
        <label>Kode Transaksi</label>
        <input type="text" class="form-control" value="<?= $kode_unik ?>" readonly>
    </div>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Username</label>
                <input type="text" name="username" required class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Password</label>
                <input type="text" name="password" required class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" required class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>No. HP</label>
                <input type="text" name="no_hp" required class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Pilih Kelas</label>
                <select name="kelas_id" class="form-control" required>
                    <option value="">-- Pilih --</option>
                    <?php
                    $kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY jenjang, nama_kelas");
                    while ($k = mysqli_fetch_assoc($kelas_result)) {
                        echo "<option value='{$k['id']}'>{$k['jenjang']} - {$k['nama_kelas']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Pilih Paket</label>
                <select name="paket_id" class="form-control" required>
                    <option value="">-- Pilih --</option>
                    <?php
                    $paket_result = mysqli_query($conn, "SELECT * FROM paket WHERE status='Aktif'");
                    while ($p = mysqli_fetch_assoc($paket_result)) {
                        echo "<option value='{$p['id']}'>{$p['nama']} - Rp " . number_format($p['harga'], 0, ',', '.') . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Metode Pembayaran</label>
                <select name="metode" class="form-control" required>
                    <option value="">-- Pilih --</option>
                    <option value="Tunai">Tunai</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Simpan & Bayar</button>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/kasir_footer.php'; ?>
