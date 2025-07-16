<?php
include '../includes/auth.php';
include '../includes/header.php';
include '../config/database.php';

// Cek role tutor
if ($_SESSION['user']['role'] !== 'tutor') {
  header('Location: ../index.php');
  exit;
}

$tutor_id = $_SESSION['user']['id'];

// Ambil data tutor
$query = "SELECT username FROM users WHERE id = $tutor_id";
$result = mysqli_query($conn, $query);
$tutor = mysqli_fetch_assoc($result);
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5 text-center">
          <img src="https://ui-avatars.com/api/?name=Nur+Apriliyani&background=0D8ABC&color=fff&size=128" 
               class="rounded-circle mb-4 shadow-sm" 
               alt="Foto Profil" width="120" height="120">

          <h4 class="fw-bold mb-2">Nur Apriliyani</h4>
          <p class="text-muted mb-4">Tutor di Bimbel Online</p>

          <div class="d-grid gap-2">
            <a href="ubah_password.php" class="btn btn-outline-primary">
              <i class="bi bi-key"></i> Ubah Password
            </a>
            <a href="../includes/logout.php" class="btn btn-danger">
              <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
