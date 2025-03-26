<?php

// Verifica se o usuário clicou no botão de logout
if (isset($_POST['logout'])) {
    // Destrói todas as variáveis de sessão
    session_unset();
    
    // Destrói a sessão
    session_destroy();
    
    // Redireciona para a página de login
    header("Location: Login.php");
    exit();
}
?>
