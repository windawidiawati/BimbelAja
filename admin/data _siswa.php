<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Data Siswa BimbelAja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php
    // Koneksi ke database
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'bimbelaja';
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Fungsi untuk mendapatkan semua siswa
    function getSiswa($conn) {
        $query = "SELECT * FROM users WHERE role='siswa' ORDER BY created_at DESC";
        $result = $conn->query($query);
        
        $siswa = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $siswa[] = $row;
            }
        }
        return $siswa;
    }

    // Menangani aksi tambah, edit, dan hapus
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_siswa'])) {
            // Tambah siswa
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = 'siswa';
            $nama = $_POST['nama'];
            $keahlian = $_POST['keahlian'];
            $kelas = $_POST['kelas'];
            $jenjang = $_POST['jenjang'];

            $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama, keahlian, kelas, jenjang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $password, $role, $nama, $keahlian, $kelas, $jenjang);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit_siswa'])) {
            // Edit siswa
            $id = $_POST['id'];
            $username = $_POST['username'];
            $role = 'siswa';
            $nama = $_POST['nama'];
            $keahlian = $_POST['keahlian'];
            $kelas = $_POST['kelas'];
            $jenjang = $_POST['jenjang'];

            $stmt = $conn->prepare("UPDATE users SET username=?, role=?, nama=?, keahlian=?, kelas=?, jenjang=? WHERE id=?");
            $stmt->bind_param("ssssssi", $username, $role, $nama, $keahlian, $kelas, $jenjang, $id);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete_siswa'])) {
            // Hapus siswa
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    $siswa = getSiswa($conn);
    ?>

    <div class="min-h-screen">
        <!-- Header Admin -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">Data Siswa BimbelAja</h1>
                <button onclick="document.getElementById('addSiswaModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Tambah Siswa</button>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
           