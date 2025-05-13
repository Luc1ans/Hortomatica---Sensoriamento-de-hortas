<?php
namespace Controller;

use Model\Leitura;
use Model\Canteiro;
use Model\Dispositivo;

class LeituraController {
    private Leitura $leituraModel;
    private Canteiro $canteiroModel;
    private Dispositivo $dispositivoModel;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/index.php?page=login');
            exit;
        }

        $pdo = Database::connect();
        $this->leituraModel     = new Leitura($pdo);
        $this->canteiroModel    = new Canteiro($pdo);
        $this->dispositivoModel = new Dispositivo($pdo);
    }

    public function processarRequisicao(): void {
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

        $filtroSensor      = $_GET['sensor']        ?? '';
        $filtroDataInicial = $_GET['data_inicial']  ?? '';
        $filtroDataFinal   = $_GET['data_final']    ?? '';
        $selDevices        = $_GET['dispositivos']  ?? $allDeviceIds;
        if (!is_array($selDevices)) {
            $selDevices = explode(',', (string)$selDevices);
        }
        $selDevices = array_map('intval', $selDevices);

        $leituras       = [];
        $ultimas        = [];
        foreach ($selDevices as $idDisp) {
            $leituras   = array_merge(
                $leituras,
                $this->leituraModel->getByDispositivo($idDisp, $filtroSensor, $filtroDataInicial, $filtroDataFinal)
            );
            $ultimas    = array_merge(
                $ultimas,
                $this->leituraModel->getLatestByDispositivo($idDisp)
            );
        }
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

        require __DIR__ . '/../View/AnaliseDados.php';
    }
}
