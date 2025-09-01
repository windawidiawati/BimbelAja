<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../config/database.php';

    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    // Cek apakah email & username cocok
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? AND username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Hapus token lama kalau ada
        $conn->query("DELETE FROM password_resets WHERE email='" . $conn->real_escape_string($email) . "'");

        // Buat token unik
        $token = bin2hex(random_bytes(32));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Simpan ke tabel password_resets
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires_at);
        $stmt->execute();

        // Buat link reset password
        $reset_link = "https://sibimbel.software-cgs.my.id/auth/reset_password.php?token=" . $token;



        // Kirim email ke user
        $subject = "Reset Password BimbelAja";
        $body = "Halo {$user['username']},\n\n"
              . "Klik link berikut untuk reset password Anda:\n$reset_link\n\n"
              . "Link ini hanya berlaku 1 jam.";
        $headers = "From: no-reply@bimbelaja.com\r\n";

        $mail = new PHPMailer(true);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'bimbelaja29@gmail.com';
    $mail->Password   = 'jcdg ktoc ejgw qgir'; // pakai App Password Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('bimbelaja29@gmail.com', 'BimbelAja');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = "
        <p>Halo <b>{$user['username']}</b>,</p>
        <p>Klik link berikut untuk reset password Anda:</p>
        <p><a href='$reset_link'>$reset_link</a></p>
        <p><i>Link ini hanya berlaku 1 jam.</i></p>
    ";

    $mail->send();
    $message = "<p class='success'>Link reset password sudah dikirim ke email kamu.</p>";
} catch (Exception $e) {
    $message = "<p class='error'>Mailer Error: {$mail->ErrorInfo}</p>";
}


    } else {
        $message = "<p class='error'>Email atau username tidak cocok.</p>";
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
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }
        input:focus {
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
            
            <label for="username">Masukkan Username</label>
            <input type="text" id="username" name="username" required placeholder="username kamu">

            <button type="submit">Kirim Link Reset</button>
        </form>
    </div>
</body>
</html>
