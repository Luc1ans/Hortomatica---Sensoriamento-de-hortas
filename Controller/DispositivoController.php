<?php
namespace Controller;
require_once __DIR__ . '/../Model/Dispositivo.php';

class DispositivoController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function processarRequisicao() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/index.php?page=login');
            exit;
        }
        $userId = $_SESSION['user_id'];
    
        // Processar POST normalmente
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarPost($_POST, $userId);
        }
    
        // Buscar os dados atualizados
        $dispositivos    = $this->model->getAllDispositivos();
        $dispositivosIDs = $this->model->getAllDispositivosid($userId);
    
        // Se for requisição AJAX, devolve JSON e sai
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode([
                'available' => $dispositivos,
                'assigned'  => $dispositivosIDs
            ]);
            exit;
        }
    
        // Senão, carrega a página completa
        require __DIR__ . '/../View/gerenciardispositivos.php';
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