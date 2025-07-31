<?php
include '../config/database.php';

$id = $_POST['id'];
$nama = $_POST['nama'];
$username = $_POST['username'];
$password = $_POST['password'] ?? '';
$role = $_POST['role'];
$jenjang = $_POST['jenjang'] ?? null;
$kelas = $_POST['kelas'] ?? null;
$keahlian = $_POST['keahlian'] ?? null;

if ($password) {
  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $update = "UPDATE users SET nama=?, username=?, password=?, role=?, jenjang=?, kelas=?, keahlian=? WHERE id=?";
  $stmt = $conn->prepare($update);
  $stmt->bind_param("sssssssi", $nama, $username, $hashed, $role, $jenjang, $kelas, $keahlian, $id);
} else {
  $update = "UPDATE users SET nama=?, username=?, role=?, jenjang=?, kelas=?, keahlian=? WHERE id=?";
  $stmt = $conn->prepare($update);
  $stmt->bind_param("ssssssi", $nama, $username, $role, $jenjang, $kelas, $keahlian, $id);
}

$stmt->execute();
header("Location: kelola_user.php");
exit;
?>
