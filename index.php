<?php
require_once __DIR__ . '/Controller/Database.php';
// 1) iniciar sessão antes de qualquer saída
session_start();

// 2) tratar logout antes de incluir views
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: Login.php');
    exit;
}
require_once __DIR__ . '/Model/Dispositivo.php';
require_once __DIR__ . '/Controller/DispositivoController.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_PATH', $basePath);
define('ASSETS', BASE_PATH . '/Assets');

$database = new Database();
$pdo = $database->connect();

$dispositivoModel = new Dispositivo($pdo);
$dispositivoController = new DispositivoController($dispositivoModel);

// Roteamento
$page = $_GET['page'] ?? 'home';

if ($page === 'gerenciar_dispositivos') {
    $dispositivoController->processarRequisicao();
} elseif ($page === 'home') {
    require __DIR__ . '/View/home.php';
}
// ... outros casos
?>