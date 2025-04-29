<?php
// logout.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: Login.php");
    exit();
}
?>