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
        // 1) Captura e valida idHorta
        $idHorta = (int)($_GET['idHorta'] ?? 0);
        if (!$idHorta) {
            throw new \Exception('ID da horta não recebido.');
        }

        // 2) Busca canteiros da horta
        $canteiros = $this->canteiroModel->getCanteirosByHorta($idHorta);
        if (empty($canteiros)) {
            throw new \Exception('Nenhum canteiro cadastrado para esta horta.');
        }

        // 3) Define o canteiro selecionado
        $selectedCanteiro = (int)($_GET['idCanteiro'] ?? $canteiros[0]['idCanteiros']);

        // 4) Busca dispositivos vinculados
        $devices = $this->dispositivoModel->getDispositivoByCanteiro($selectedCanteiro);
        if (empty($devices)) {
            throw new \Exception('Nenhum dispositivo vinculado a este canteiro.');
        }
        $allDeviceIds = array_column($devices, 'idDispositivo');

        // 5) Captura filtros do GET
        $filtroSensor      = $_GET['sensor']        ?? '';
        $filtroDataInicial = $_GET['data_inicial']  ?? '';
        $filtroDataFinal   = $_GET['data_final']    ?? '';
        $selDevices        = $_GET['dispositivos']  ?? $allDeviceIds;
        if (!is_array($selDevices)) {
            $selDevices = explode(',', (string)$selDevices);
        }
        // garante inteiros
        $selDevices = array_map('intval', $selDevices);

        // 6) BUSCA de leituras
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

        // 7) Monta $chartData: [ sensor => [ [ts, val_disp1, val_disp2,…], … ] ]
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

        // 8) Renderiza view, passando só variáveis
        require __DIR__ . '/../View/AnaliseDados.php';
    }
}
