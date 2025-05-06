<?php
// auth.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}
?>
