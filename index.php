<?php
// 1) sessão
session_start();

// 2) constantes de caminho
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_PATH', $basePath);
define('ASSETS', BASE_PATH . '/Assets');

// 3) logout (via GET ou POST)
if (isset($_GET['logout']) || isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_PATH . '/index.php?page=login');
    exit;
}

// 4) defina aqui quais páginas não exigem login
$publicPages = ['login', 'cadastro'];

// 5) capturar a página
$page = $_GET['page'] ?? 'home';

// 6) se for tentativa de acessar página privada sem estar logado → manda pro login
if (empty($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header('Location: ' . BASE_PATH . '/index.php?page=login');
    exit;
}

// 7) inclui dependências (Model, Controller, etc)
require_once __DIR__ . '/Controller/Database.php';
require_once __DIR__ . '/Model/Dispositivo.php';
require_once __DIR__ . '/Model/Horta.php';
require_once __DIR__ . '/Controller/DispositivoController.php';
require_once __DIR__ . '/Controller/HortaController.php';

// 8) rota de login: processa o POST e exibe a View/login.php
if ($page === 'login') {
    // se veio do form de login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $db = Database::connect();
        $stmt = $db->prepare('SELECT * FROM usuario WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['senha'])) {
            $_SESSION['user_id'] = $user['idUsuario'];
            header('Location: ' . BASE_PATH . '/index.php?page=home');
            exit;
        } else {
            $_SESSION['error_message'] = 'E‑mail ou senha inválidos.';
            header('Location: ' . BASE_PATH . '/index.php?page=login');
            exit;
        }
    }
    // exibe o formulário
    require __DIR__ . '/View/login.php';
    exit;
}

// 9) aqui montamos o restante das rotas (já estamos autenticados)
$database = new Database();
$pdo = $database->connect();
$modelD = new Dispositivo($pdo);
$modelH = new Horta($pdo);
$modelC = new Canteiro($pdo);
$dispositivoCtrl = new DispositivoController($modelD);
$hortaCtrl = new HortaController($modelH, $modelC, $modelD);


if ($page === 'gerenciar_dispositivos') {
    $dispositivoCtrl->processarRequisicao();
} elseif ($page === 'gerenciar_hortas') {
    $hortaCtrl->processarRequisicao();   
} elseif ($page === 'home') {
    require __DIR__ . '/View/home.php';
} elseif ($page === 'cadastro') {
    require __DIR__ . '/View/Cadastro.php';
}
// … outras páginas públicas ou privadas
