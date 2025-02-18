<?php
// auth.php
session_start(); // Inicia a sessão

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php"); // Redireciona para a página de login
    exit(); // Garante que o restante do código não seja executado
}
?>

