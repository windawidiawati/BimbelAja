<?php include '../includes/header.php'; ?>

<div class="container mt-5">
  <h2>Paket Langganan</h2>
  <div class="row mt-4">
    <div class="col-md-4">
      <div class="card border-primary">
        <div class="card-body">
          <h4 class="card-title">Basic</h4>
          <p>Rp 50.000 / bulan</p>
          <a href="checkout.php?paket=basic" class="btn btn-primary">Langganan</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-success">
        <div class="card-body">
          <h4 class="card-title">Premium</h4>
          <p>Rp 100.000 / bulan</p>
          <a href="checkout.php?paket=premium" class="btn btn-success">Langganan</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
