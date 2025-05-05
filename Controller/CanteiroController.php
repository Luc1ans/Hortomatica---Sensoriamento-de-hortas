<?php
require_once __DIR__ . '/../Model/Canteiro.php';
require_once __DIR__ . '/../Model/Dispositivo.php';

class CanteiroController
{
    private $canteiroModel;
    private $dispositivoModel;

    public function __construct($canteiroModel, $dispositivoModel)
    {
        $this->canteiroModel = $canteiroModel;
        $this->dispositivoModel = $dispositivoModel;
    }

    public function processarAcao($acao, $dados, $usuarioId)
    {
        switch ($acao) {
            case 'adicionar_canteiro':
                return $this->adicionarCanteiro($dados, $usuarioId);
            case 'editar_canteiro':
                return $this->canteiroModel->updateCanteiro($dados);
            case 'excluir_canteiro':
                return $this->canteiroModel->deleteCanteiro($dados);
            case 'vincular_dispositivo':
                return $this->canteiroModel->linkDispositivo($dados);
            case 'desvincular_dispositivo':
                return $this->canteiroModel->unlinkDispositivo($dados);
            case 'getCanteirosByHorta':
                return $this->canteiroModel->getCanteirosByHorta($dados['idHorta']);
            case 'getDispositivosByCanteiro':
                return $this->canteiroModel->getDispositivosByCanteiro($dados['idCanteiro']);
            default:
                return false;
        }
    }

    private function adicionarCanteiro($dados, $usuarioId)
    {
        // Validação dos dados
        if (
            empty($dados['idHorta']) ||
            empty($dados['cultura']) ||
            empty($dados['data_plantio']) ||
            empty($dados['data_colheita'])
        ) {
            throw new Exception('Todos os campos são obrigatórios');
        }

        return $this->canteiroModel->createCanteiro(
            $dados['idHorta'],
            $dados['cultura'],
            $dados['data_plantio'],
            $dados['data_colheita'],
            $usuarioId
        );
    }

}