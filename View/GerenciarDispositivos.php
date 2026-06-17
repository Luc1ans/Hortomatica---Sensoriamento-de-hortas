<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/Assets/css/style.css">
    <title>Gerenciar Dispositivos</title>
    <?php include __DIR__ . '/layout/navbar.php'; ?>
</head>

<body>
    <?php if (!empty($_SESSION['mensagem'])): ?>
        <?php
        $tipo = $_SESSION['tipo_mensagem'] ?? 'info';
        $icone = $tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
        $corBorda = $tipo === 'success' ? '#198754' : '#dc3545'; // verde / vermelho Bootstrap
        ?>
        <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="toast show" role="alert" style="
            background: #fff; 
            border-left: 5px solid <?= $corBorda ?>; 
            border-radius: 8px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            min-width: 280px;
            max-width: 400px;
            padding: 0;
            ">
                <div class="d-flex align-items-center p-3">
                    <i class="bi <?= $icone ?>" style="font-size: 1.5rem; color: <?= $corBorda ?>; margin-right: 12px;"></i>
                    <div class="me-auto" style="color: #333; font-weight: 500; font-size: 0.95rem;">
                        <?= $_SESSION['mensagem'] ?>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="toast"
                        style="font-size: 0.8rem;"></button>
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const toastEl = document.querySelector('.toast.show');
                if (toastEl) {
                    toastEl.classList.remove('show');
                    setTimeout(() => toastEl.parentElement.remove(), 300);
                }
            }, 4000);
        </script>
        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
    <?php endif; ?>
    
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
                            <div class="d-flex gap-2">
                                <button class="btn btn-danger btn-action"
                                    onclick="document.getElementById('modalExcluir<?= $dispositivosID['idDispositivo']; ?>').style.display='block'">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                                <button class="btn btn-warning"
                                    onclick="document.getElementById('modalEditar<?= $dispositivosID['idDispositivo']; ?>').style.display='block'">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Excluir -->
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

                <!-- Modal Editar -->
                <div id="modalEditar<?= $dispositivosID['idDispositivo']; ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Editar Dispositivo</h3>
                        </div>
                        <div class="modal-body">
                            <form action="index.php?page=gerenciar_dispositivos" method="POST" data-remote>
                                <input type="hidden" name="acao" value="editar">
                                <input type="hidden" name="idDispositivo" value="<?= $dispositivosID['idDispositivo']; ?>">

                                <div class="mb-3">
                                    <label for="nome<?= $dispositivosID['idDispositivo']; ?>"
                                        class="form-label">Nome</label>
                                    <input type="text" id="nome<?= $dispositivosID['idDispositivo']; ?>" name="nome"
                                        class="form-control"
                                        value="<?= htmlspecialchars($dispositivosID['nome_dispositivo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="localizacao<?= $dispositivosID['idDispositivo']; ?>"
                                        class="form-label">Localização</label>
                                    <input type="text" id="localizacao<?= $dispositivosID['idDispositivo']; ?>"
                                        name="localizacao" class="form-control"
                                        value="<?= htmlspecialchars($dispositivosID['localizacao'], ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="status<?= $dispositivosID['idDispositivo']; ?>"
                                        class="form-label">Status</label>
                                    <select id="status<?= $dispositivosID['idDispositivo']; ?>" name="status"
                                        class="form-control">
                                        <option value="Ativo" <?= $dispositivosID['status'] === 'Ativo' ? 'selected' : ''; ?>>
                                            Ativo</option>
                                        <option value="Inativo" <?= $dispositivosID['status'] === 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dataInstalacao<?= $dispositivosID['idDispositivo']; ?>"
                                        class="form-label">Data de Instalação</label>
                                    <input type="date" id="dataInstalacao<?= $dispositivosID['idDispositivo']; ?>"
                                        name="dataInstalacao" class="form-control"
                                        value="<?= htmlspecialchars($dispositivosID['data_instalacao'], ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-warning">Salvar</button>
                                    <button type="button" class="btn btn-secondary"
                                        onclick="document.getElementById('modalEditar<?= $dispositivosID['idDispositivo']; ?>').style.display='none'">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
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
                    <form data-remote action="index.php?page=gerenciar_dispositivos" method="POST">
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
                            <label for="localizacao" class="form-label">Localização</label>
                            <input type="text" id="localizacao" name="localizacao" class="form-control" required>
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

    <script src="<?= BASE_PATH ?>/Assets/js/GerenciarDispositivos.js" defer></script>
    <?php include __DIR__ . '/layout/footer.php'; ?>
</body>

</html>