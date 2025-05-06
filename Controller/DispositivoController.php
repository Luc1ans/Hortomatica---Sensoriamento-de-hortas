<?php
require_once __DIR__ . '/../Model/Dispositivo.php';

class DispositivoController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function processarRequisicao() {
        session_start();
        
        // Obter dados para views
        $dispositivos = $this->model->getAllDispositivos();
        $dispositivosIDs = $this->model->getAllDispositivosid($_SESSION['user_id']);
        
        // Processar POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processarPost($_POST, $_SESSION['user_id']);
        }
        
        // Carregar view
        require '../Views/gerenciardispositivos.php';
    }

    private function processarPost($postData, $userId) {
        $acao = $postData['acao'] ?? '';
        
        if ($acao === 'adicionar') {
            $this->adicionarDispositivo($postData, $userId);
        } elseif ($acao === 'excluir') {
            $this->excluirDispositivo($postData);
        }
    }

    private function adicionarDispositivo($dados, $userId) {
        $idDispositivo = $dados['idDispositivo'] ?? '';
        $nome = htmlspecialchars($dados['nome'] ?? '', ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($dados['status'] ?? 'Ativo', ENT_QUOTES, 'UTF-8');
        $dataInstalacao = $dados['dataInstalacao'] ?? '';

        if (empty($idDispositivo) || empty($nome) || empty($dataInstalacao)) {
            $_SESSION['mensagem'] = 'Por favor, preencha todos os campos obrigatórios!';
            $_SESSION['tipo_mensagem'] = 'erro';
        } else {
            $this->model->updateDispositivo(
                $idDispositivo,
                $nome,
                $status,
                $dataInstalacao,
                $userId
            );
            $_SESSION['mensagem'] = 'Dispositivo adicionado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
        }
    }

    private function excluirDispositivo($dados) {
        $idDispositivo = $dados['idDispositivo'] ?? '';
        if ($this->model->deleteDispositivo($idDispositivo)) {
            $_SESSION['mensagem'] = 'Dispositivo excluído com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
        } else {
            $_SESSION['mensagem'] = 'Erro ao excluir o dispositivo';
            $_SESSION['tipo_mensagem'] = 'erro';
        }
    }
}
?>