<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /BimbelAja/auth/login.php');
    exit;
}
?>
