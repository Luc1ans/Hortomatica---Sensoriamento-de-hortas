<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use Controller\Database;
use Model\Leitura;

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

$chartImages = [];
foreach ($_POST as $k => $v) {
    if (str_starts_with($k, 'img_') && is_string($v)) {
        $chartImages[$k] = $v;
    }
}
$options = new Dompdf\Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf\Dompdf($options);
ob_start();
require __DIR__ . '/../View/pdf/template_pdf.php';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("relatorio_horta_{$idHorta}.pdf", ["Attachment" => false]);
exit;
