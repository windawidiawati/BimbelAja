<?php
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../config/database.php';

    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Buat token unik
        $token = bin2hex(random_bytes(16));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Simpan ke tabel password_resets
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires_at);
        $stmt->execute();

        // Buat link reset password
        $reset_link = "http://localhost/BimbelAja/auth/reset_password.php?token=" . $token;

        $message = "<p class='success'>Link reset password: <a href='$reset_link'>$reset_link</a></p>";
    } else {
        $message = "<p class='error'>Email tidak terdaftar.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* Wavy gradient background */
            background: linear-gradient(135deg, 
              #f0f7ff 0%, 
              #d0e3ff 25%, 
              #a8c8ff 50%, 
              #7aadff 75%, 
              #0d6efd 100%);
            background-size: 400% 400%;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }
        input[type="email"]:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 5px rgba(74,144,226,0.3);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #357ab7;
        }
        .success {
            color: #2ecc71;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Lupa Password</h2>
        <?= $message ?>
        <form method="POST">
            <label for="email">Masukkan Email Terdaftar</label>
            <input type="email" id="email" name="email" required placeholder="contoh@email.com">
            <button type="submit">Kirim Link Reset</button>
        </form>
    </div>
</body>
</html>
