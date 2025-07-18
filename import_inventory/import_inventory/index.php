<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php"); // Jika sudah login langsung ke dashboard
} else {
    header("Location: login.php"); // Jika belum login, ke halaman login
}
exit;
?>
