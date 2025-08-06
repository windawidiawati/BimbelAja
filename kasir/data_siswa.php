<?php
include '../includes/kasir_header.php';
include '../config/database.php';

// Ambil data siswa dari langganan yang sudah pernah langganan
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
        ORDER BY l.created_at DESC
    ";
$result = mysqli_query($conn, $query);

// Data dropdown kelas & paket
// $kelas_result = mysqli_query($conn, "SELECT DISTINCT nama_kelas FROM kelas");
// $paket_result = mysqli_query($conn, "SELECT DISTINCT nama FROM paket");
// ?>

<div class="container mt-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-people-fill me-2"></i>Data Siswa (Perpanjang Paket)</h4>

    <!-- Tombol Perpanjang -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#perpanjangModal">
        <i class="bi bi-arrow-repeat me-1"></i> Perpanjang Paket
    </button>

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

<!-- Modal Perpanjang -->
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
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        $kelas_result = mysqli_query($conn, "SELECT id, nama_kelas FROM kelas");
                        while ($k = mysqli_fetch_assoc($kelas_result)) :
                        ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Paket -->
                <div class="mb-3">
                    <label for="paket_id" class="form-label">Paket</label>
                    <select name="paket_id" class="form-select" required>
                        <option value="">-- Pilih Paket --</option>
                        <?php
                        $paket_result = mysqli_query($conn, "SELECT id, nama FROM paket");
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
