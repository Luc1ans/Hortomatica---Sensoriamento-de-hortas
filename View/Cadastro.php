<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use Controller\Database;
// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados enviados pelo formulário
    $usuario = $_POST['usuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha = $_POST['password'] ?? ''; // Alterando para 'senha'
    $confirmSenha = $_POST['confirmPassword'] ?? ''; // Alterando para 'senha'

    // Verifica se as senhas são iguais
    if ($senha !== $confirmSenha) {
        $_SESSION['error_message'] = 'As senhas não coincidem.';
        header('Location: cadastro.php');
        exit;
    }

    // Conectar ao banco de dados
    require_once __DIR__ . '/../Controller/Database.php'; // Caminho para o arquivo de conexão com o banco

    try {
        // Verifica se o e-mail já está cadastrado
        $db = Database::connect();
        $query = $db->prepare('SELECT * FROM usuario WHERE email = :email');
        $query->bindParam(':email', $email);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Caso o e-mail já esteja cadastrado
            $_SESSION['error_message'] = 'Este e-mail já está em uso.';
            header('Location: cadastro.php');
            exit;
        }

        // Se o e-mail não estiver em uso, insere o novo usuário no banco de dados
        $hashedSenha = password_hash($senha, PASSWORD_DEFAULT); // Alterando para 'senha'
        $insertQuery = $db->prepare('INSERT INTO usuario (usuario, email, telefone, senha) VALUES (:usuario, :email, :telefone, :senha)');
        $insertQuery->bindParam(':usuario', $usuario);
        $insertQuery->bindParam(':email', $email);
        $insertQuery->bindParam(':telefone', $telefone);
        $insertQuery->bindParam(':senha', $hashedSenha); // Alterando para 'senha'
        $insertQuery->execute();

        // Cadastro bem-sucedido
        $_SESSION['success_message'] = 'Cadastro realizado com sucesso! Agora, faça login.';
        header('Location: login.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro no banco de dados: ' . $e->getMessage();
        header(header: 'Location: cadastro.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="..\Assets\css\style.css">
    <title>Cadastro - Hortomática</title>
</head>
<body>
    <div class="container d-flex flex-column align-items-center justify-content-center vh-100">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <h1 class="text-center mb-4">Cadastro</h1>

            <!-- Exibe a mensagem de erro ou sucesso, se houver -->
            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            ?>

            <!-- Formulário de Cadastro -->
            <form action="cadastro.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuário</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Digite seu nome de usuário" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="Digite seu telefone">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirme a Senha</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirme sua senha" required>
                </div>
                <button type="submit" class="btn btn-login w-100">Cadastrar</button>
                <div class="text-center mt-3">
                    <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../Assets/footer.php'; ?>
</body>
</html>
