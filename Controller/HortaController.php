<?php
// Controller/HortaController.php
namespace Controller;
require_once __DIR__ . '/../Model/Horta.php';
require_once __DIR__ . '/../Model/Canteiro.php';
require_once __DIR__ . '/../Model/Dispositivo.php';
require_once __DIR__ . '/../Controller/CanteiroController.php';

class HortaController
{
    private $model;
    private $canteiroController;
    private $dispositivoModel;

    public function __construct($hortaModel, $canteiroModel, $dispositivoModel)
    {
        $this->model = $hortaModel;
        $this->dispositivoModel = $dispositivoModel;
        $this->canteiroController = new CanteiroController(
            $canteiroModel,
            $dispositivoModel
        );
    }

    public function processarRequisicao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/index.php?page=login');
            exit;
        }
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarPost($_POST, $userId);
        }

        $hortas = $this->model->getHortasByUsuario($userId);

        $canteirosMap = [];
        $dispMap = [];
        foreach ($hortas as $horta) {
            $idHorta = $horta['idHorta'];
            $canteiros = $this->canteiroController->processarAcao('getCanteirosByHorta', ['idHorta' => $idHorta], $userId);
            $canteirosMap[$idHorta] = $canteiros;
            foreach ($canteiros as $canteiro) {
                $idC = $canteiro['idCanteiros'];
                $dispMap[$idC] = $this->canteiroController->processarAcao('getDispositivosByCanteiro', ['idCanteiro' => $idC], $userId);
            }
        }

        $dispositivosLivres = $this->dispositivoModel->getAvailableDispositivos($userId);

        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode([
                'hortas' => $hortas,
                'canteirosMap' => $canteirosMap,
                'dispMap' => $dispMap,
                'dispositivosLivres' => $dispositivosLivres
            ]);
            exit;
        }
        require __DIR__ . '/../View/gerenciarhortas.php';
    }

    private function processarPost($data, $userId)
    {
        $acao = $data['acao'] ?? '';
        switch ($acao) {
            case 'adicionar':
                $this->model->createHorta(
                    htmlspecialchars($data['nome'] ?? '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($data['observacoes'] ?? '', ENT_QUOTES, 'UTF-8'),
                    $userId
                );
                break;

            case 'editar':
                $this->model->updateHorta(
                    (int) $data['idHorta'],
                    htmlspecialchars($data['nome'] ?? '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($data['observacoes'] ?? '', ENT_QUOTES, 'UTF-8')
                );
                break;

            case 'excluir':
                $this->model->deleteHorta((int) $data['idHorta']);
                break;

            case 'listar_canteiros':
                $canteiros = $this->canteiroController->processarAcao('getCanteirosByHorta', ['idHorta' => $data['idHorta']], $userId);
                header('Content-Type: application/json');
                echo json_encode(['canteiros' => $canteiros]);
                exit;

            case 'adicionar_canteiro':
            case 'editar_canteiro':
            case 'excluir_canteiro':
            case 'vincular_dispositivo':
            case 'desvincular_dispositivo':
                $result = $this->canteiroController->processarAcao($acao, $data, $userId);

                if ($result) {
                    $_SESSION['mensagem'] = 'Operação realizada com sucesso!';
                    $_SESSION['tipo_mensagem'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Falha ao executar operação.';
                    $_SESSION['tipo_mensagem'] = 'danger';
                }
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;

            default:

                break;
        }
    }

}
