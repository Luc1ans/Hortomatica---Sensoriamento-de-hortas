<?php
require_once __DIR__ . '/../Controller/Database.php';
require_once('../Controller/HortaController.php');
require_once('../Controller/DispositivoController.php');
require_once('../Controller/CanteiroController.php');
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
$ControllerC = new CanteiroController($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar') {
        $nomeHorta = $_POST['nome'] ?? '';
        $observacoes = $_POST['observacoes'] ?? '';

        if (empty($nomeHorta)) {
            echo "<p style='color: red;'>Por favor, preencha todos os campos obrigatórios!</p>";
        } else {
            $resultado = $controller->createHorta(
                htmlspecialchars($nomeHorta, ENT_QUOTES, 'UTF-8'),
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
        $observacoes = $_POST['observacoes'] ?? '';

        if (!$controller->updateHorta($idHorta, $nomeHorta, $observacoes)) {
            echo "<p style='color: red;'>Erro ao atualizar a horta.</p>";
        }
    } elseif ($acao === 'adicionar_canteiro') {
        $idHorta = $_POST['idHorta'] ?? '';
        $Cultura = $_POST['cultura'] ?? [];
        $dataPlantio = $_POST['data_plantio'] ?? [];
        $dataColheita = $_POST['data_colheita'] ?? [];

        if (empty($Cultura) || empty($dataPlantio) || empty($dataColheita)) {
            echo "<p style='color: red;'>Preencha os campos necessários</p>";
        } else {
            if (!$ControllerC->createCanteiro($idHorta, $Cultura, $dataPlantio, $dataColheita)) {
                echo "<p style='color: red;'>Erro ao adicionar canteiro.</p>";
            }
        }

    } elseif ($acao === 'vincular_dispositivo') {
        // Novo bloco para vincular dispositivo ao canteiro
        $idCanteiro = $_POST['idCanteiros'] ?? '';
        $idDispositivo = $_POST['idDispositivo'] ?? '';

        if (empty($idCanteiro) || empty($idDispositivo)) {
            echo "<p style='color: red;'>Selecione um canteiro e um dispositivo!</p>";
        } else {
            // Aqui usamos o método linkDispositivo do CanteiroController
            if (!$ControllerC->linkDispositivo($idCanteiro, $idDispositivo)) {
                echo "<p style='color: red;'>Erro ao vincular o dispositivo ao canteiro.</p>";
            }
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
                                <!-- Botão para adicionar canteiro -->
                                <button class="btn btn-warning btn-action w-100 mb-2"
                                    onclick="document.getElementById('modalAdicionarCanteiro<?= $horta['idHorta']; ?>').style.display='block'">
                                    <i class="bi bi-plus-circle"></i> Adicionar Canteiro
                                </button>
                                <button class="btn btn-primary btn-action w-100 mb-2"
                                    onclick="document.getElementById('modalCanteiros<?= $horta['idHorta']; ?>').style.display='block'">
                                    <i class="bi bi-eye"></i> Exibir Canteiros
                                </button>
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

                <!-- Modal para adicionar canteiro -->
                <div id="modalAdicionarCanteiro<?= $horta['idHorta']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Adicionar Canteiro</h3>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <input type="hidden" name="acao" value="adicionar_canteiro">
                                <input type="hidden" name="idHorta" value="<?= $horta['idHorta']; ?>">
                                <div id="canteiros-container-<?= $horta['idHorta']; ?>">
                                    <div class="canteiro-group mb-3">
                                        <div class="mb-3">
                                            <label>Cultura</label>
                                            <input type="text" name="cultura[]" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Data de plantio</label>
                                            <input type="date" name="data_plantio[]" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Data de colheita prevista</label>
                                            <input type="date" name="data_colheita[]" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary mb-3"
                                    onclick="adicionarCanteiroInputs('<?= $horta['idHorta']; ?>')">
                                    <i class="bi bi-plus-square"></i> Adicionar novo canteiro
                                </button>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Salvar</button>
                                    <button type="button" class="btn btn-secondary"
                                        onclick="document.getElementById('modalAdicionarCanteiro<?= $horta['idHorta']; ?>').style.display='none'">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal para exibir os canteiros da horta -->
                <div id="modalCanteiros<?= $horta['idHorta']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Canteiros da Horta "<?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES, 'UTF-8'); ?>"
                            </h3>
                        </div>
                        <div class="modal-body">
                            <?php
                            $canteiros = $ControllerC->getCanteirosByHorta($horta['idHorta']);
                            if (count($canteiros) === 0): ?>
                                <p>Nenhum canteiro cadastrado.</p>
                            <?php else:
                                foreach ($canteiros as $canteiro): ?>
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">
                                                <?= htmlspecialchars($canteiro['Cultura'], ENT_QUOTES, 'UTF-8') ?>
                                            </h6>
                                            <p class="card-text">
                                                Plantio: <?= htmlspecialchars($canteiro['DataPlantio'], ENT_QUOTES, 'UTF-8') ?><br>
                                                Colheita: <?= htmlspecialchars($canteiro['DataColheira'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                            <button class="btn btn-sm btn-primary"
                                                onclick="document.getElementById('modalDispositivoCanteiro<?= $canteiro['idCanteiros'] ?>').style.display='block'">
                                                <i class="bi bi-plus-lg"></i> Dispositivo
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('modalCanteiros<?= $horta['idHorta']; ?>').style.display='none'">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>


                <!-- Modal para adicionar dispositivo ao canteiro -->
                <div id="modalDispositivoCanteiro<?= $canteiro['idCanteiros'] ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Vincular Dispositivo</h3>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <input type="hidden" name="acao" value="vincular_dispositivo">
                                <input type="hidden" name="idCanteiros" value="<?= $canteiro['idCanteiros'] ?>">
                                <div class="mb-3">
                                    <label>Dispositivo</label>
                                    <select name="idDispositivo" class="form-control" required>
                                        <?php foreach ($dispositivoadd as $dispositivo): ?>
                                            <option value="<?= $dispositivo['idDispositivo'] ?>">
                                                <?= $dispositivo['idDispositivo'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Vincular</button>
                                    <button type="button" class="btn btn-secondary"
                                        onclick="document.getElementById('modalDispositivoCanteiro<?= $canteiro['idCanteiros'] ?>').style.display='none'">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Modal de Edição -->
            <div id="modal<?= $horta['idHorta']; ?>" class="modal">
                <div class="modal-content">
                    <form action="VincularCanteiroDisp" method="POST">
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

        </div>

        <button class="btn btn-primary btn-lg btn-add mb-5"
            onclick="document.getElementById('modal').style.display='block'">
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
            function adicionarCanteiroInputs(idHorta) {
                const container = document.getElementById(`canteiros-container-${idHorta}`);
                const novoGrupo = document.createElement('div');
                novoGrupo.className = 'canteiro-group mb-3';
                novoGrupo.innerHTML = `
        <div class="mb-3">
            <label>Cultura</label>
            <input type="text" name="cultura[]" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Data de plantio</label>
            <input type="date" name="data_plantio[]" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Data de colheita prevista</label>
            <input type="date" name="data_colheita[]" class="form-control" required>
        </div>
    `;
                container.appendChild(novoGrupo);
            }
        </script>
    </div>
    <?php include '../Assets/footer.php'; ?>

</body>

</html>