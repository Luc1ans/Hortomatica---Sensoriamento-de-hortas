<?php
require_once __DIR__ . '/../Controller/Database.php';
require_once('../Controller/HortaController.php');
require_once('../Controller/DispositivoController.php');
require_once('../Assets/Auth.php');
require_once('../Assets/Logout.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Usuário não autenticado. Faça login para continuar.');</script>";
    header('Location: login.php');
    exit();
}

$usuarioId = $_SESSION['user_id'];
$pdo = Database::connect();
$controller = new HortaController($pdo);
$controllerD = new DispositivoController($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar') {
        $nomeHorta = $_POST['nome'] ?? '';
        $plantacoes = $_POST['plantacoes'] ?? '';
        $observacoes = $_POST['observacoes'] ?? '';

        if (empty($nomeHorta) || empty($plantacoes)) {
            echo "<p style='color: red;'>Por favor, preencha todos os campos obrigatórios!</p>";
        } else {
            $resultado = $controller->createHorta(
                htmlspecialchars($nomeHorta, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($plantacoes, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($observacoes, ENT_QUOTES, 'UTF-8'),
                $usuarioId
            );
            if (!$resultado) {
                echo "<p style='color: red;'>Erro ao adicionar a horta.</p>";
            }
        }
    } elseif ($acao === 'excluir') {
        $idHorta = $_POST['idHorta'] ?? '';
        if (!$controller->deleteHorta($idHorta)) {
            echo "<p style='color: red;'>Erro ao excluir a horta.</p>";
        }
    } elseif ($acao === 'editar') {
        $idHorta = $_POST['idHorta'] ?? '';
        $nomeHorta = $_POST['nome'] ?? '';
        $plantacoes = $_POST['plantacoes'] ?? '';
        $observacoes = $_POST['observacoes'] ?? '';

        if (!$controller->updateHorta($idHorta, $nomeHorta, $plantacoes, $observacoes)) {
            echo "<p style='color: red;'>Erro ao atualizar a horta.</p>";
        }
    } elseif ($acao === 'adicionar_dispositivo') {
        $idHorta = $_POST['idHorta'] ?? '';
        $idDispositivo = $_POST['idDispositivo'] ?? '';
        if (!$controller->linkHortaEDispositivo($idHorta, $idDispositivo)) {
            echo "<p style='color: red;'>Erro ao linkar a horta.</p>";
        }
    }
}

$hortas = $controller->getHortasByUsuario($usuarioId);
$dispositivoadd = $controllerD->getAllDispositivosid($usuarioId);

$dispositivosVinculados = [];
foreach ($hortas as $horta) {
    $dispositivosVinculados[$horta['idHorta']] = $controllerD->getDispositivoByHorta($horta['idHorta']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Assets/css/style.css">
    <title>Hortomática - Gerenciar Hortas</title>
    <?php include '../Assets/navbar.php'; ?>
    
</head>

<body>
    <div class="container mt-4">
        <h3 class="mb-4">Lista de Hortas</h3>
        <div class="row">
            <?php foreach ($hortas as $horta): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 card-hover">
                        <div class="card-body">
                            <h5 class="card-title text-success">
                                <?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                            <p class="card-text">
                                <strong>Plantações:</strong>
                                <?= htmlspecialchars($horta['plantacoes'], ENT_QUOTES, 'UTF-8'); ?><br>
                                <strong>Observações:</strong>
                                <?= htmlspecialchars($horta['observacoes'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="d-flex justify-content-between mb-3">
                                <!-- Botão que abre o modal de confirmação de exclusão -->
                                <button class="btn btn-danger btn-action"
                                    onclick="document.getElementById('modalExcluir<?= $horta['idHorta']; ?>').style.display='block'">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                                <button class="btn btn-warning btn-action"
                                    onclick="document.getElementById('modal<?= $horta['idHorta']; ?>').style.display='block'">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            </div>
                            <div class="mt-3">
                                <!-- Botão para adicionar dispositivo -->
                                <button class="btn btn-warning btn-action w-100 mb-2"
                                    onclick="document.getElementById('modalAdicionarDispositivo<?= $horta['idHorta']; ?>').style.display='block'">
                                    <i class="bi bi-plus-circle"></i> Adicionar dispositivo
                                </button>
                                <?php if (isset($dispositivosVinculados[$horta['idHorta']]) && !empty($dispositivosVinculados[$horta['idHorta']])): ?>
                                    <a href="AnaliseDados.php?idHorta=<?= htmlspecialchars($horta['idHorta'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="btn btn-success btn-action w-100">
                                        <i class="bi bi-eye"></i> Ver dispositivos
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para confirmar exclusão -->
                <div id="modalExcluir<?= $horta['idHorta']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Confirmar Exclusão</h3>
                        </div>
                        <div class="modal-body">
                            <p>Tem certeza que deseja excluir a horta
                            "<strong><?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES, 'UTF-8'); ?></strong>"?
                            </p>
                        </div>
                        <div class="modal-footer">
                            <form action="" method="POST" class="d-inline">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="idHorta" value="<?= $horta['idHorta']; ?>">
                                <button type="submit" class="btn btn-danger">Excluir</button>
                            </form>
                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('modalExcluir<?= $horta['idHorta']; ?>').style.display='none'">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal para adicionar dispositivo -->
                <div id="modalAdicionarDispositivo<?= $horta['idHorta']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Adicionar Dispositivo</h3>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <input type="hidden" name="acao" value="adicionar_dispositivo">
                                <input type="hidden" name="idHorta" value="<?= $horta['idHorta']; ?>">
                                <div class="mb-3">
                                    <label for="idDispositivo" class="form-label">Selecionar ID do dispositivo</label>
                                    <select name="idDispositivo" class="form-control" required>
                                        <?php foreach ($dispositivoadd as $dispositivo): ?>
                                            <option value="<?= htmlspecialchars($dispositivo['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($dispositivo['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Adicionar</button>
                                    <button type="button" class="btn btn-secondary"
                                        onclick="document.getElementById('modalAdicionarDispositivo<?= $horta['idHorta']; ?>').style.display='none'">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Edição -->
                <div id="modal<?= $horta['idHorta']; ?>" class="modal">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <input type="hidden" name="acao" value="editar">
                            <input type="hidden" name="idHorta" value="<?= $horta['idHorta']; ?>">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome da Horta</label>
                                <input type="text" id="nome" name="nome" class="form-control"
                                    value="<?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="plantacoes" class="form-label">Plantações</label>
                                <input type="text" id="plantacoes" name="plantacoes" class="form-control"
                                    value="<?= htmlspecialchars($horta['plantacoes'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <input type="text" id="observacoes" name="observacoes" class="form-control"
                                    value="<?= htmlspecialchars($horta['observacoes'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <button type="submit" class="btn btn-success">Salvar</button>
                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('modal<?= $horta['idHorta']; ?>').style.display='none'">
                                Cancelar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="btn btn-primary btn-lg btn-add mb-5" onclick="document.getElementById('modal').style.display='block'">
            <i class="bi bi-plus-lg"></i> Adicionar Horta
        </button>

        <!-- Modal Adicionar Horta -->
        <div id="modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Adicionar Nova Horta</h3>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="acao" value="adicionar">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome da Horta</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="plantacoes" class="form-label">Plantações</label>
                            <input type="text" id="plantacoes" name="plantacoes" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <input type="text" id="observacoes" name="observacoes" class="form-control">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Adicionar</button>
                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('modal').style.display='none'">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            window.onclick = function (event) {
                var modals = document.getElementsByClassName('modal');
                for (var i = 0; i < modals.length; i++) {
                    if (event.target == modals[i]) {
                        modals[i].style.display = "none";
                    }
                }
            }
        </script>
    </div>
    <?php include '../Assets/footer.php'; ?>
</body>

</html>