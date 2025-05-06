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
                // Extrai os campos individuais em vez de passar o array todo
                return $this->canteiroModel->updateCanteiro(
                    $dados['idCanteiro'],            
                    $dados['cultura'],
                    $dados['data_plantio'],
                    $dados['data_colheita']
                );

            case 'excluir_canteiro':
                // Garante que passamos somente o ID (string ou int), não o array
                $id = isset($dados['idCanteiros']) ? $dados['idCanteiros'] : $dados['idCanteiro'];
                return $this->canteiroModel->deleteCanteiro($id);

            case 'vincular_dispositivo':
                return $this->canteiroModel->linkDispositivo(
                    $dados['idCanteiros'],
                    $dados['idDispositivo']
                );

            case 'desvincular_dispositivo':
                return $this->canteiroModel->unlinkDispositivo($dados['idDispositivo']);

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
