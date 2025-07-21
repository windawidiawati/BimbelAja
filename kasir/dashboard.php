<?php
include '../includes/kasir_header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Kasir</h4>

<div class="row g-4">
    <!-- Card 1: Total Transaksi -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-receipt-cutoff display-5 text-primary mb-3"></i>
                <h5 class="fw-bold">Total Transaksi</h5>
                <p class="fs-4 text-dark">120</p>
            </div>
        </div>
    </div>

    <!-- Card 2: Pending -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-hourglass-split display-5 text-warning mb-3"></i>
                <h5 class="fw-bold">Menunggu Verifikasi</h5>
                <p class="fs-4 text-dark">8</p>
            </div>
        </div>
    </div>

    <!-- Card 3: Siswa Terdaftar -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-people display-5 text-success mb-3"></i>
                <h5 class="fw-bold">Siswa Terdaftar</h5>
                <p class="fs-4 text-dark">56</p>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<!-- Tabel Transaksi Terbaru -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</h6>
    </div>
    <div class="card-body">
        <table class="table table-striped text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Siswa</th>
                    <th>Paket</th>
                    <th>Harga</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Ani</td>
                    <td>Paket 3 Bulan</td>
                    <td>Rp 500.000</td>
                    <td><span class="badge bg-success">Lunas</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Budi</td>
                    <td>Paket 1 Bulan</td>
                    <td>Rp 200.000</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../includes/kasir_footer.php';
?>
