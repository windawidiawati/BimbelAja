<?php
include '../config/database.php';
include '../admin_header.php';

// Verifikasi pembayaran
if (isset($_GET['verifikasi'])) {
    $id = intval($_GET['verifikasi']);

    // Ambil kode_unik berdasarkan ID pembayaran
    $getKode = mysqli_query($conn, "SELECT kode_unik FROM pembayaran WHERE id = $id LIMIT 1");
    if ($getKode && mysqli_num_rows($getKode) > 0) {
        $kode_unik = mysqli_fetch_assoc($getKode)['kode_unik'];

        // Update status pembayaran dan langganan
        mysqli_query($conn, "UPDATE pembayaran SET status='lunas' WHERE id=$id");
        mysqli_query($conn, "UPDATE langganan SET status='aktif' WHERE kode_unik='$kode_unik'");
    }
}

// Tolak pembayaran
if (isset($_GET['tolak'])) {
    $id = intval($_GET['tolak']);

    // Ambil kode_unik berdasarkan ID pembayaran
    $getKode = mysqli_query($conn, "SELECT kode_unik FROM pembayaran WHERE id = $id LIMIT 1");
    if ($getKode && mysqli_num_rows($getKode) > 0) {
        $kode_unik = mysqli_fetch_assoc($getKode)['kode_unik'];

        // Update status pembayaran dan langganan
        mysqli_query($conn, "UPDATE pembayaran SET status='ditolak' WHERE id=$id");
        mysqli_query($conn, "UPDATE langganan SET status='ditolak' WHERE kode_unik='$kode_unik'");
    }
}
?>

<div class="container mt-4">
    <h4>Data Pembayaran Siswa</h4>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Kode Unik</th>
                    <th>Total</th>
                    <th>Bukti Transfer</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $query = mysqli_query($conn, "
                    SELECT p.*, u.nama 
                    FROM pembayaran p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.id DESC
                ");
                while ($row = mysqli_fetch_assoc($query)) :
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kode_unik']) ?></td>
                        <td>Rp<?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td>
                            <?php if (!empty($row['bukti_transfer'])) : ?>
                                <a href="../uploads/<?= $row['bukti_transfer'] ?>" target="_blank">Lihat Bukti</a>
                            <?php else : ?>
                                Tidak ada
                            <?php endif; ?>
                        </td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] == 'menunggu') : ?>
                                <a href="?verifikasi=<?= $row['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Yakin verifikasi pembayaran ini?')">✔</a>
                                <a href="?tolak=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pembayaran ini?')">✘</a>
                            <?php else : ?>
                                <span class="text-muted">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../admin_footer.php'; ?>
