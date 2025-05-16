<?php
namespace Controller;

use Model\Leitura;
use Model\Canteiro;
use Model\Dispositivo;
use Model\Horta;
use Controller\Database;

class LeituraController {
    private Leitura $leituraModel;
    private Canteiro $canteiroModel;
    private Dispositivo $dispositivoModel;
    private Horta $hortaModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/index.php?page=login');
            exit;
        }

        $pdo = Database::connect();
        $this->leituraModel     = new Leitura($pdo);
        $this->canteiroModel    = new Canteiro($pdo);
        $this->dispositivoModel = new Dispositivo($pdo);
        $this->hortaModel       = new Horta($pdo);
    }

    public function processarRequisicao(): void {
        $userId = $_SESSION['user_id'];
        
        // Intercepta geração de PDF via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['acao'] ?? '') === 'pdf') {
            $this->gerarPdf();
            return;
        }

        $idHorta = (int)($_GET['idHorta'] ?? 0);
        if (!$idHorta) {
            throw new \Exception('ID da horta não recebido.');
        }

        $canteiros = $this->canteiroModel->getCanteirosByHorta($idHorta);
        if (empty($canteiros)) {
            throw new \Exception('Nenhum canteiro cadastrado para esta horta.');
        }
        
        $selectedCanteiro = (int)($_GET['idCanteiro'] ?? $canteiros[0]['idCanteiros']);

        $devices = $this->dispositivoModel->getDispositivoByCanteiro($selectedCanteiro);
        if (empty($devices)) {
            throw new \Exception('Nenhum dispositivo vinculado a este canteiro.');
        }
        $allDeviceIds = array_column($devices, 'idDispositivo');

        $filtroSensor      = $_GET['sensor']       ?? '';
        $filtroDataInicial = $_GET['data_inicial'] ?? '';
        $filtroDataFinal   = $_GET['data_final']   ?? '';
        $selDevices        = $_GET['dispositivos'] ?? $allDeviceIds;
        if (!is_array($selDevices)) {
            $selDevices = explode(',', (string)$selDevices);
        }
        $selDevices = array_map('intval', $selDevices);

        $leituras = [];
        $ultimas  = [];
        foreach ($selDevices as $idDisp) {
            $leituras = array_merge(
                $leituras,
                $this->leituraModel->getByDispositivo($idDisp, $filtroSensor, $filtroDataInicial, $filtroDataFinal)
            );
            $ultimas  = array_merge(
                $ultimas,
                $this->leituraModel->getLatestByDispositivo($idDisp)
            );
        }

        // Preparação dos dados para gráficos
        $porSensor = [];
        foreach ($leituras as $l) {
            $sensor = $l['nome_sensor'];
            $ts     = "{$l['data_leitura']} {$l['hora_leitura']}";
            $disp   = (int)$l['Dispositivo_idDispositivo'];
            $porSensor[$sensor][$ts][$disp] = (float)$l['valor_leitura'];
        }
        $chartData = [];
        foreach ($porSensor as $sensor => $times) {
            ksort($times);
            $rows = [];
            foreach ($times as $ts => $vals) {
                $row = [$ts];
                foreach ($selDevices as $disp) {
                    $row[] = $vals[$disp] ?? null;
                }
                $rows[] = $row;
            }
            $chartData[$sensor] = $rows;
        }

        // Renderiza view de análise de dados (HTML + scripts)
        require __DIR__ . '/../View/AnaliseDados.php';
    }

    private function gerarPdf(): void
    {
        // Recebe parâmetros do POST
        $idHorta        = (int)($_POST['idHorta'] ?? 0);
        $selDevices     = isset($_POST['dispositivos']) ? explode(',', $_POST['dispositivos']) : [];
        $filtroSensor   = $_POST['sensor']       ?? '';
        $dataInicial    = $_POST['data_inicial'] ?? '';
        $dataFinal      = $_POST['data_final']   ?? '';

        // Recupera nome da horta
        $horta = $this->hortaModel->getHortaById($idHorta);
        $nome_horta = $horta['nome_horta'] ?? $horta['nome'] ?? '–';

        // Reusa lógica de leituras
        $leituras   = [];
        $ultimas    = [];
        foreach ($selDevices as $idDisp) {
            $leituras = array_merge(
                $leituras,
                $this->leituraModel->getByDispositivo((int)$idDisp, $filtroSensor, $dataInicial, $dataFinal)
            );
            $ultimas  = array_merge(
                $ultimas,
                $this->leituraModel->getLatestByDispositivo((int)$idDisp)
            );
        }

        // Captura imagens dos gráficos do POST
        $chartImages = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'img_') === 0) {
                $chartImages[$key] = $value;
            }
        }

        // Gera HTML via buffer
        ob_start();
        require __DIR__ . '/../View/pdf/template_pdf.php';
        $html = ob_get_clean();

        // Gera PDF com Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio-horta-{$idHorta}.pdf", ['Attachment' => true]);
        exit;
    }
}
