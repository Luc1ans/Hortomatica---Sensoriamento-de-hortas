<?php
// iniciar sessão para manter BASE_PATH, caso use redirecionamento
session_start();

// 1) Autoload e imports
require __DIR__ . '/../vendor/autoload.php';

use Controller\Database;
use Model\Leitura;

// 2) Recebe parâmetros do form
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['idHorta'])) {
    header('Location: index.php');
    exit;
}

$idHorta = (int) $_POST['idHorta'];
$selDevices   = isset($_POST['dispositivos']) 
    ? explode(',', (string)$_POST['dispositivos']) 
    : [];
$filtroSensor     = $_POST['sensor']        ?? '';
$filtroDataInicial= $_POST['data_inicial']  ?? '';
$filtroDataFinal  = $_POST['data_final']    ?? '';

// 3) Conecta e busca leituras
$pdo = Database::connect();
$leituraModel = new Leitura($pdo);

$leituras       = [];
$ultimasLeituras= [];
foreach ($selDevices as $idDisp) {
    $id = (int)$idDisp;
    $leituras       = array_merge(
        $leituras,
        $leituraModel->getByDispositivo($id, $filtroSensor, $filtroDataInicial, $filtroDataFinal)
    );
    $ultimasLeituras= array_merge(
        $ultimasLeituras,
        $leituraModel->getLatestByDispositivo($id)
    );
}

// 4) Coleta imagens de gráfico do POST
$chartImages = [];
foreach ($_POST as $k => $v) {
    if (str_starts_with($k, 'img_') && is_string($v)) {
        $chartImages[$k] = $v;
    }
}

// 5) Renderiza PDF
$options = new Dompdf\Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf\Dompdf($options);

// caminho absoluto para o template
ob_start();
require __DIR__ . '/../View/pdf/template_pdf.php';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("relatorio_horta_{$idHorta}.pdf", ["Attachment" => false]);
exit;
