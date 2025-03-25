<?php
require_once '../vendor/autoload.php'; 
require_once __DIR__ . '/../Controller/Database.php';
require_once('../Controller/DispositivoController.php');
require_once('../Controller/LeituraSensores.php');

if (!isset($_POST['idHorta'])) {
    die("Erro: ID da horta não foi fornecido.");
}

$idHorta = $_POST['idHorta'];
$dispositivosSelecionados = isset($_POST['dispositivos']) ? explode(',', $_POST['dispositivos']) : [];
$filtroSensor = $_POST['sensor'] ?? '';
$filtroDataInicial = $_POST['data_inicial'] ?? '';
$filtroDataFinal = $_POST['data_final'] ?? '';


// Conecta ao banco e busca os dados
$pdo = Database::connect();
$controller = new DispositivoController($pdo);
$leituraController = new LeituraSensores();

$chartImages = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'img_') === 0) {
        $sensorName = str_replace("img_", "", $key);
        $chartImages[$sensorName] = $value;
    }
}

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