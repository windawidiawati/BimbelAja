<?php
include '../config/database.php';
include '../includes/auth.php';

if ($_SESSION['user']['role'] !== 'tutor' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$tutor_id = $_SESSION['user']['id'];

// Set header untuk export ke Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=jadwal_offline_group_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query untuk data jadwal offline group per tanggal & kelas
$query = "SELECT jo.tanggal, k.nama_kelas, km.nama_kategori, jo.jam_mulai, jo.jam_selesai
          FROM jadwal_offline jo
          LEFT JOIN kelas k ON jo.kelas_id = k.id
          LEFT JOIN kategori_materi km ON jo.kategori_id = km.id
          WHERE jo.tutor_id = '$tutor_id'
          ORDER BY jo.tanggal ASC, k.nama_kelas ASC, jo.jam_mulai ASC";
$res = mysqli_query($conn, $query);

// Output tabel ke Excel
$current_date = '';
if (mysqli_num_rows($res) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    while ($row = mysqli_fetch_assoc($res)) {
        $tgl = date('d-m-Y', strtotime($row['tanggal']));
        if ($current_date != $tgl) {
            if ($current_date != '') echo "</tbody>";
            echo "<thead>
                    <tr style='background-color:#d9d9d9; font-weight:bold;'>
                        <th colspan='3'>Tanggal: $tgl</th>
                    </tr>
                    <tr style='background-color:#f2f2f2;'>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Jam</th>
                    </tr>
                  </thead>
                  <tbody>";
            $current_date = $tgl;
        }
        echo "<tr>
                <td>{$row['nama_kelas']}</td>
                <td>{$row['nama_kategori']}</td>
                <td>{$row['jam_mulai']} - {$row['jam_selesai']}</td>
              </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "Tidak ada jadwal offline.";
}
?>
