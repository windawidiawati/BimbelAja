<?php
include '../includes/kasir_header.php';
include '../config/database.php';

// Ambil filter dari GET jika ada
$filter_kelas = isset($_GET['kelas_id']) && is_numeric($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;
$filter_paket = isset($_GET['paket_id']) && is_numeric($_GET['paket_id']) ? intval($_GET['paket_id']) : 0;

// Buat query utama dengan filter
$query = "
    SELECT 
        l.*, 
        u.nama AS nama_siswa,
        k.nama_kelas, k.jenjang,
        p.nama AS nama_paket
    FROM langganan l
    JOIN users u ON l.user_id = u.id
    JOIN kelas k ON l.kelas_id = k.id
    JOIN paket p ON l.paket_id = p.id
    WHERE 1=1
";

// Tambahkan kondisi filter jika ada
if ($filter_kelas > 0) {
    $query .= " AND l.kelas_id = $filter_kelas ";
}
if ($filter_paket > 0) {
    $query .= " AND l.paket_id = $filter_paket ";
}

$query .= " ORDER BY l.created_at DESC";
$result = mysqli_query($conn, $query);

// Ambil data dropdown kelas & paket untuk filter dan modal
$kelas_result = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$paket_result = mysqli_query($conn, "SELECT id, nama FROM paket ORDER BY nama ASC");
?>

<div class="container mt-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-people-fill me-2"></i>Data Siswa (Perpanjang Paket)</h4>

    <!-- Form Filter -->
    <form method="GET" class="row g-3 mb-3 align-items-end">
        <div class="col-auto">
            <label for="kelas_id" class="form-label">Filter Kelas</label>
            <select name="kelas_id" id="kelas_id" class="form-select">
                <option value="0">-- Semua Kelas --</option>
                <?php 
                // Karena $kelas_result sudah dipakai di modal nanti, reload dulu agar pointer kembali ke awal
                mysqli_data_seek($kelas_result, 0); 
                while ($k = mysqli_fetch_assoc($kelas_result)) : ?>
                    <option value="<?= $k['id'] ?>" <?= $filter_kelas == $k['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-auto">
            <label for="paket_id" class="form-label">Filter Paket</label>
            <select name="paket_id" id="paket_id" class="form-select">
                <option value="0">-- Semua Paket --</option>
                <?php 
                mysqli_data_seek($paket_result, 0);
                while ($p = mysqli_fetch_assoc($paket_result)) : ?>
                    <option value="<?= $p['id'] ?>" <?= $filter_paket == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jenjang</th>
                    <th>Paket</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Berakhir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                        <td><?= htmlspecialchars($row['jenjang']); ?></td>
                        <td><?= htmlspecialchars($row['nama_paket']); ?></td>
                        <td><?= htmlspecialchars($row['tanggal_mulai']); ?></td>
                        <td><?= htmlspecialchars($row['tanggal_berakhir']); ?></td>
                        <td>
                            <span class="badge bg-<?= $row['status'] === 'aktif' ? 'success' : 'secondary'; ?>">
                                <?= ucfirst($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Perpanjang tetap sama -->

<div class="modal fade" id="perpanjangModal" tabindex="-1" aria-labelledby="perpanjangModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="proses_perpanjang.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="perpanjangModalLabel">Perpanjang Paket Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nama siswa -->
                <div class="mb-3">
                    <label for="user_id" class="form-label">Nama Siswa</label>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php
                        $siswa = mysqli_query($conn, "SELECT id, nama FROM users WHERE role = 'siswa'");
                        while ($s = mysqli_fetch_assoc($siswa)) {
                            echo "<option value='{$s['id']}'>{$s['nama']}</option>";
                        }
                        ?>
                    </select>
                </div>

              <!-- Kelas -->
                <div class="mb-3">
                    <label for="kelas_id_modal" class="form-label">Kelas</label>
                    <select name="kelas_id" id="kelas_id_modal" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        mysqli_data_seek($kelas_result, 0);
                        while ($k = mysqli_fetch_assoc($kelas_result)) :
                        ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Paket -->
                <div class="mb-3">
                    <label for="paket_id_modal" class="form-label">Paket</label>
                    <select name="paket_id" id="paket_id_modal" class="form-select" required>
                        <option value="">-- Pilih Paket --</option>
                        <?php
                        mysqli_data_seek($paket_result, 0);
                        while ($p = mysqli_fetch_assoc($paket_result)) :
                        ?>
                            <option value="<?= $p['id'] ?>"><?= $p['nama'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Tanggal mulai dan berakhir -->
                <div class="mb-3">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
                    <input type="date" class="form-control" name="tanggal_berakhir" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" readonly>
                </div>

                <!-- Metode pembayaran -->
                <div class="mb-3">
                    <label for="metode" class="form-label">Metode Pembayaran</label>
                    <select name="metode" class="form-select" required>
                        <option value="Tunai">Tunai</option>
                        <option value="Transfer">Transfer</option>
                    </select>
                </div>

                <!-- Status -->
                <input type="hidden" name="status" value="aktif">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan Perpanjangan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/kasir_footer.php'; ?>
