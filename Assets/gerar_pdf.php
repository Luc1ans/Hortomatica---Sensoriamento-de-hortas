<?php
// gerar_pdf.php

require_once '../vendor/autoload.php'; // ajuste para o caminho correto do autoload do DomPDF
require_once __DIR__ . '/../Controller/Database.php';
require_once('../Controller/DispositivoController.php');
require_once('../Controller/LeituraSensores.php');

if (!isset($_GET['idHorta'])) {
    die("Erro: ID da horta não foi fornecido.");
}

$idHorta = $_GET['idHorta'];
$dispositivosSelecionados = isset($_GET['dispositivos']) ? explode(',', $_GET['dispositivos']) : [];
$filtroSensor = $_GET['sensor'] ?? '';
$filtroDataInicial = $_GET['data_inicial'] ?? '';
$filtroDataFinal = $_GET['data_final'] ?? '';

// Conecta ao banco e busca os dados
$pdo = Database::connect();
$controller = new DispositivoController($pdo);
$leituraController = new LeituraSensores();

// Obtém leituras filtradas (replicar lógica da página de análise)
$leituras = [];
$ultimasLeituras = [];

foreach ($dispositivosSelecionados as $idDisp) {
    $leiturasDevice = $leituraController->getLeiturasByDispositivo($idDisp, $filtroSensor, $filtroDataInicial, $filtroDataFinal);
    $ultimasDevice = $leituraController->getUltimasLeituras($idDisp);
    $leituras = array_merge($leituras, $leiturasDevice);
    $ultimasLeituras = array_merge($ultimasLeituras, $ultimasDevice);
}

// Configura DomPDF
$options = new Dompdf\Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf\Dompdf($options);

// Renderiza o template com os dados
ob_start();
include 'template_pdf.php'; // Certifique-se de que o caminho está correto
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Saída do PDF
$dompdf->stream("relatorio.pdf", ["Attachment" => false]);
?>
