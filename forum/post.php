<?php include '../includes/auth.php'; ?>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
  <h2>Buat Postingan</h2>
  <form method="POST" action="index.php">
    <div class="mb-3">
      <label>Judul</label>
      <input type="text" name="judul" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Isi</label>
      <textarea name="isi" class="form-control" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-success">Posting</button>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
