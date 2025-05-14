<?php

use Controller\Database;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    require_once __DIR__ . '/../Controller/Database.php'; // Caminho corrigido

    try {
        $db = Database::connect();
        $query = $db->prepare('SELECT * FROM usuario WHERE email = :email');
        $query->bindParam(':email', $email);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['senha'])) { 
            $_SESSION['user_id'] = $user['idUsuario']; 
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'E-mail ou senha inválidos.';
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/Assets/css/style.css">
    <?php include __DIR__ . '/layout/navbar.php'; ?>
    <title>Login - Hortomática</title>
</head>

<body>
    <div class="container d-flex flex-column align-items-center justify-content-center vh-100">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <h1 class="text-center mb-4">Login</h1>

            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']); 
            }
            ?>

            <form action="<?= BASE_PATH ?>/index.php?page=login" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail"
                        required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn btn-login w-100">Entrar</button>
                <div class="text-center mt-3">
                    <p>Não tem uma conta? <a href="Cadastro.php">Cadastre-se</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__ . '/layout/footer.php'; ?>
</body>


</html>