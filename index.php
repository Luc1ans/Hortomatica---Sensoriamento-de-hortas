<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use Controller\Database;
use Controller\DispositivoController;
use Controller\HortaController;
use Controller\LeituraController;
use Model\Leitura;
use Model\Dispositivo;
use Model\Horta;
use Model\Canteiro;

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_PATH', $basePath);
define('ASSETS', BASE_PATH . '/Assets');

if (isset($_GET['logout']) || isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_PATH . '/index.php?page=login');
    exit;
}

$publicPages = ['login', 'cadastro'];

$page = $_GET['page'] ?? 'home';

if (empty($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header('Location: ' . BASE_PATH . '/index.php?page=login');
    exit;
}

if ($page === 'login') {
    
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
    require __DIR__ . '/View/login.php';
    exit;
}

$database = new Database();
$pdo = $database->connect();
$modelD = new Dispositivo($pdo);
$modelH = new Horta($pdo);
$modelC = new Canteiro($pdo);
$dispositivoCtrl = new DispositivoController($modelD);
$hortaCtrl = new HortaController($modelH, $modelC, $modelD);
$leituraCtrl = new LeituraController();

if ($page === 'gerenciar_dispositivos') {
    $dispositivoCtrl->processarRequisicao();
} elseif ($page === 'gerenciar_hortas') {
    $hortaCtrl->processarRequisicao();   
} 
elseif ($page === 'analise') {
    $leituraCtrl->processarRequisicao();
}elseif ($page === 'home') {
    require __DIR__ . '/View/home.php';
} elseif ($page === 'cadastro') {
    require __DIR__ . '/View/Cadastro.php';
}

