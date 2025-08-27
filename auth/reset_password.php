<?php
include '../config/database.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    die("Token tidak valid.");
}

// Cek token
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Token sudah kadaluarsa atau tidak valid.");
}

$row = $result->fetch_assoc();
$email = $row['email'];

// Jika submit password baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Update password di tabel users
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $password, $email);
    $stmt->execute();

    // Hapus token setelah dipakai
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    echo "Password berhasil direset. Silakan login kembali.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .reset-container {
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            width: 350px;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }
        .reset-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .reset-container label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .reset-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: border 0.3s;
        }
        .reset-container input[type="password"]:focus {
            border-color: #4facfe;
        }
        .reset-container button {
            width: 100%;
            padding: 12px;
            background: #4facfe;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .reset-container button:hover {
            background: #00c6ff;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            margin: 15px auto;
            border-radius: 8px;
            font-size: 14px;
            width: 350px;
            text-align: center;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Password</h2>
        <form method="POST">
            <label for="password">Password Baru</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
