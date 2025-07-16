<?php
include '../config/database.php';
include '../includes/auth.php';

// Hanya kasir yang boleh akses
if ($_SESSION['user']['role'] !== 'kasir') {
  header('Location: ../index.php');
  exit;
}

// Ambil data siswa dan paket
$siswa_result = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'");
$paket_result = mysqli_query($conn, "SELECT * FROM paket WHERE status='aktif'");
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow border-0">
        <div class="card-body">
          <h4 class="card-title mb-4 text-center">
            <i class="bi bi-cash-stack me-2"></i>Checkout Pembayaran Tunai
          </h4>

          <form action="proses_checkout.php" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
              <label for="user_id" class="form-label">Nama Siswa</label>
              <select name="user_id" id="user_id" class="form-select" required>
                <option value="">-- Pilih Siswa --</option>
                <?php while ($siswa = mysqli_fetch_assoc($siswa_result)) : ?>
                  <option value="<?= $siswa['id'] ?>"><?= $siswa['nama'] ?> (<?= $siswa['username'] ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="mb-4">
              <label for="paket_id" class="form-label">Paket Langganan</label>
              <select name="paket_id" id="paket_id" class="form-select" required>
                <option value="">-- Pilih Paket --</option>
                <?php while ($paket = mysqli_fetch_assoc($paket_result)) : ?>
                  <option value="<?= $paket['id'] ?>">
                    <?= $paket['nama'] ?> - Rp<?= number_format($paket['harga']) ?> /
                    <?= $paket['durasi'] . ' ' . $paket['satuan_durasi'] ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Pembayaran
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
