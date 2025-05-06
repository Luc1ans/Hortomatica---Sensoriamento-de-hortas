<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Assets/css/style.css">
    <title>Gerenciar Dispositivos</title>
    <?php include '../Assets/navbar.php'; ?>
</head>

<body>
    <div class="container mt-4">
        <h3 class="mb-4">Lista de Dispositivos</h3>
        <div class="row">
            <?php foreach ($dispositivosIDs as $dispositivosID): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 card-hover">
                        <div class="card-body">
                            <h5 class="card-title text-success">
                                <?= htmlspecialchars($dispositivosID['nome_dispositivo'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                            <p class="card-text">
                                <strong>ID:</strong>
                                <?= htmlspecialchars($dispositivosID['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?><br>
                                <strong>Localização:</strong>
                                <?= htmlspecialchars($dispositivosID['localizacao'], ENT_QUOTES, 'UTF-8'); ?><br>
                                <strong>Status:</strong>
                                <span
                                    class="badge bg-<?= $dispositivosID['status'] === 'Ativo' ? 'success' : 'secondary'; ?>">
                                    <?= htmlspecialchars($dispositivosID['status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span><br>
                                <strong>Data de Instalação:</strong>
                                <?= htmlspecialchars($dispositivosID['data_instalacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <!-- Botão para abrir o modal de confirmação de exclusão -->
                            <button class="btn btn-danger btn-action"
                                onclick="document.getElementById('modalExcluir<?= $dispositivosID['idDispositivo']; ?>').style.display='block'">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal de confirmação para exclusão do dispositivo -->
                <div id="modalExcluir<?= $dispositivosID['idDispositivo']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Confirmar Exclusão</h3>
                        </div>
                        <div class="modal-body">
                            <p>Tem certeza que deseja excluir o dispositivo
                                "<strong><?= htmlspecialchars($dispositivosID['nome_dispositivo'], ENT_QUOTES, 'UTF-8'); ?></strong>"?
                            </p>
                        </div>
                        <div class="modal-footer">
                            <form action="" method="POST" class="d-inline">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="idDispositivo" value="<?= $dispositivosID['idDispositivo']; ?>">
                                <button type="submit" class="btn btn-danger">Excluir</button>
                            </form>
                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('modalExcluir<?= $dispositivosID['idDispositivo']; ?>').style.display='none'">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="btn btn-primary btn-lg btn-add mb-5"
            onclick="document.getElementById('modalAdicionar').style.display='block'">
            <i class="bi bi-plus-lg"></i> Adicionar Dispositivo
        </button>

        <!-- Modal Adicionar Dispositivo -->
        <div id="modalAdicionar" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Adicionar Dispositivo</h3>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="acao" value="adicionar">
                        <div class="mb-3">
                            <label for="idDispositivo" class="form-label">Selecionar ID</label>
                            <select id="idDispositivo" name="idDispositivo" class="form-control" required>
                                <option value="">Selecione um ID</option>
                                <?php foreach ($dispositivos as $dispositivo): ?>
                                    <option
                                        value="<?= htmlspecialchars($dispositivo['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($dispositivo['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dataInstalacao" class="form-label">Data de Instalação</label>
                            <input type="date" id="dataInstalacao" name="dataInstalacao" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Adicionar</button>
                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('modalAdicionar').style.display='none'">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
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
    <?php include '../Assets/footer.php'; ?>
</body>

</html>