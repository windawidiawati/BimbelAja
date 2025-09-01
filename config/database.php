<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "BimbelAja";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
// Atur timezone biar sama dengan MySQL
date_default_timezone_set("Asia/Jakarta");
$conn->query("SET time_zone = '+07:00'");
?>
