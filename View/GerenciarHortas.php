<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hortomática - Gerenciar Hortas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/Assets/css/style.css">
    <?php include __DIR__ . '/../Assets/navbar.php'; ?>
</head>

<body>
    <?php if (!empty($_SESSION['mensagem'])): ?>
        <div class="position-fixed top-50 start-50 translate-middle" style="z-index:2000; min-width:300px;">
            <div class="toast show align-items-center text-bg-<?= $_SESSION['tipo_mensagem'] ?> border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body text-center w-100">
                        <?= $_SESSION['mensagem']; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.toast').classList.remove('show');
            }, 2000);
        </script>
        <?php
        // limpa flash
        unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
        ?>
    <?php endif; ?>
    <div class="container mt-4">
        <h3 class="mb-4">Lista de Hortas</h3>
        <div class="row">
            <?php foreach ($hortas as $horta):
                $idH = $horta['idHorta'];
                $canteiros = $canteirosMap[$idH] ?? [];
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 card-hover">
                        <div class="card-body">
                            <h5 class="card-title text-success"><?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES) ?>
                            </h5>
                            <p class="card-text"><strong>Observações:</strong>
                                <?= htmlspecialchars($horta['observacoes'], ENT_QUOTES) ?></p>
                            <div class="d-flex justify-content-between mb-3">
                                <button class="btn btn-danger" onclick="toggleModal('modalExcluir<?= $idH ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button class="btn btn-warning" onclick="toggleModal('modalEditar<?= $idH ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                            <button class="btn btn-warning w-100 mb-2"
                                onclick="toggleModal('modalAdicionarCanteiro<?= $idH ?>')">
                                <i class="bi bi-plus-circle"></i> Canteiro
                            </button>
                            <button class="btn btn-success w-100 mb-2" onclick="toggleModal('modalCanteiros<?= $idH ?>')">
                                <i class="bi bi-eye"></i> Ver Canteiros
                            </button>
                            <button class="btn btn-info w-100 mb-2"
                                onclick="window.location.href='<?= BASE_PATH ?>/View/AnaliseDados.php?idHorta=<?= $idH ?>'">
                                <i class="bi bi-bar-chart-line"></i> Análise de dados
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Excluir Horta -->
                <div id="modalExcluir<?= $idH ?>" class="modal">
                    <div class="modal-content">
                        <h3 class="modal-header">Confirmar Exclusão</h3>
                        <div class="modal-body">
                            Deseja excluir “<strong><?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES) ?></strong>”?
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="idHorta" value="<?= $idH ?>">
                                <button class="btn btn-danger">Excluir</button>
                            </form>
                            <button class="btn btn-secondary"
                                onclick="toggleModal('modalExcluir<?= $idH ?>')">Cancelar</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar Horta -->
                <div id="modalEditar<?= $idH ?>" class="modal">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h3>Editar Horta</h3>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="acao" value="editar">
                                <input type="hidden" name="idHorta" value="<?= $idH ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input name="nome" class="form-control"
                                        value="<?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Observações</label>
                                    <input name="observacoes" class="form-control"
                                        value="<?= htmlspecialchars($horta['observacoes'], ENT_QUOTES) ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-success">Salvar</button>
                                <button type="button" class="btn btn-secondary"
                                    onclick="toggleModal('modalEditar<?= $idH ?>')">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Adicionar Canteiro -->
                <div id="modalAdicionarCanteiro<?= $idH ?>" class="modal min-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="header-content">
                                <h3 class="modal-title">Adicionar Canteiro</h3>
                                <p class="modal-subtitle">Preencha os dados do canteiro</p>
                            </div>
                            <button class="close-icon"
                                onclick="toggleModal('modalAdicionarCanteiro<?= $idH ?>')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form method="POST">
                                <input type="hidden" name="acao" value="adicionar_canteiro">
                                <input type="hidden" name="idHorta" value="<?= $idH ?>">

                                <div id="canteiros-container-<?= $idH ?>">
                                    <div class="canteiro-group">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Cultura</label>
                                            <input type="text" name="cultura[]" class="form-control" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Data de plantio</label>
                                            <input type="date" name="data_plantio[]" class="form-control" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Data de colheita prevista</label>
                                            <input type="date" name="data_colheita[]" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="link-button" onclick="adicionarCanteiroInputs('<?= $idH ?>')">
                                    + Adicionar novo canteiro
                                </button>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Salvar</button>
                                    <button type="button" class="btn btn-secondary"
                                        onclick="toggleModal('modalAdicionarCanteiro<?= $idH ?>')">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Ver Canteiros -->
                <div id="modalCanteiros<?= $idH ?>" class="modal min-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="header-content">
                                <h3 class="modal-title"><?= htmlspecialchars($horta['nome_horta'], ENT_QUOTES) ?></h3>
                                <p class="modal-subtitle">Canteiros cadastrados</p>
                            </div>
                            <button class="close-icon" onclick="toggleModal('modalCanteiros<?= $idH ?>')">&times;</button>
                        </div>

                        <div class="modal-body">
                            <?php if (empty($canteiros)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">🌱</div>
                                    <p>Nenhum canteiro encontrado</p>
                                </div>
                            <?php else: ?>
                                <div class="canteiro-list">
                                    <?php foreach ($canteiros as $canteiro):
                                        $idC = $canteiro['idCanteiros'];
                                        $disps = $dispMap[$idC] ?? [];
                                        $countDisp = count($disps);
                                        ?>
                                        <div class="canteiro-item">
                                            <div class="canteiro-header">
                                                <h4 class="cultura-name"><?= htmlspecialchars($canteiro['Cultura'], ENT_QUOTES) ?>
                                                </h4>
                                                <div class="d-flex gap-2">
                                                    <form method="POST"
                                                        onsubmit="return confirm('Tem certeza que deseja excluir este canteiro? Todos os dispositivos serão desvinculados.');">
                                                        <input type="hidden" name="acao" value="excluir_canteiro">
                                                        <input type="hidden" name="idCanteiros" value="<?= $idC ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i
                                                                class="bi bi-trash-fill"></i></button>
                                                    </form>
                                                    <button class="btn btn-outline-primary btn-sm" title="Editar canteiro"
                                                        onclick="toggleModal('modalEditarCanteiro<?= $idC ?>')">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="timeline">
                                                <div class="date-group">
                                                    <div class="date-item"><span class="date-label">Plantio</span>
                                                        <?= htmlspecialchars($canteiro['DataPlantio'], ENT_QUOTES) ?></div>
                                                    <div class="date-item"><span class="date-label">Colheita</span>
                                                        <?= htmlspecialchars($canteiro['DataColheira'], ENT_QUOTES) ?></div>
                                                </div>
                                            </div>

                                            <!-- Modal Editar Canteiro -->
                                            <div id="modalEditarCanteiro<?= $idC ?>" class="modal">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <input type="hidden" name="acao" value="editar_canteiro">
                                                        <input type="hidden" name="idCanteiro" value="<?= $idC ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Cultura</label>
                                                            <input type="text" name="cultura" class="form-control"
                                                                value="<?= htmlspecialchars($canteiro['Cultura'], ENT_QUOTES) ?>"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Data de plantio</label>
                                                            <input type="date" name="data_plantio" class="form-control"
                                                                value="<?= htmlspecialchars($canteiro['DataPlantio'], ENT_QUOTES) ?>"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Data de colheita prevista</label>
                                                            <input type="date" name="data_colheita" class="form-control"
                                                                value="<?= htmlspecialchars($canteiro['DataColheira'], ENT_QUOTES) ?>"
                                                                required>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-success">Salvar</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                onclick="toggleModal('modalEditarCanteiro<?= $idC ?>')">Cancelar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Modal Vincular Dispositivo -->
                                            <div id="modalDispositivoCanteiro<?= $idC ?>" class="modal min-modal">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <div class="header-content">
                                                            <h3 class="modal-title">Vincular Dispositivo</h3>
                                                            <p class="modal-subtitle">
                                                                <?= htmlspecialchars($canteiro['Cultura'], ENT_QUOTES) ?>
                                                            </p>
                                                        </div>
                                                        <button class="close-icon"
                                                            onclick="toggleModal('modalDispositivoCanteiro<?= $idC ?>')">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST">
                                                            <input type="hidden" name="acao" value="vincular_dispositivo">
                                                            <input type="hidden" name="idCanteiros" value="<?= $idC ?>">
                                                            <div class="form-group mb-3">
                                                                <label class="form-label">Dispositivo</label>
                                                                <select name="idDispositivo" class="form-select" required>
                                                                    <option value="" disabled selected>Selecione um dispositivo
                                                                    </option>
                                                                    <?php foreach ($dispositivosLivres as $dispositivo): ?>
                                                                        <option value="<?= $dispositivo['idDispositivo'] ?>">
                                                                            <?= htmlspecialchars($dispositivo['nome_dispositivo'], ENT_QUOTES) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="d-flex justify-content-end">
                                                                <button type="submit" class="btn btn-success me-2">Vincular</button>
                                                                <button type="button" class="btn btn-secondary"
                                                                    onclick="toggleModal('modalDispositivoCanteiro<?= $idC ?>')">Cancelar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if ($countDisp > 0): ?>
                                                <div class="device-list">
                                                    <span class="device-count"><?= $countDisp ?> dispositivos</span>
                                                    <?php foreach ($disps as $disp): ?>
                                                        <div class="device-item">
                                                            <span
                                                                class="device-name"><?= htmlspecialchars($disp['nome_dispositivo'], ENT_QUOTES) ?></span>
                                                            <form method="POST" class="inline-form">
                                                                <input type="hidden" name="acao" value="desvincular_dispositivo">
                                                                <input type="hidden" name="idDispositivo"
                                                                    value="<?= $disp['idDispositivo'] ?>">
                                                                <button type="submit" class="unlink-button" title="Desvincular">✕</button>
                                                            </form>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <button class="link-button"
                                                onclick="toggleModal('modalDispositivoCanteiro<?= $idC ?>')">+ Adicionar
                                                dispositivo</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary"
                                onclick="toggleModal('modalCanteiros<?= $idH ?>')">Fechar</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div> <!-- Fecha div.row -->

        <!-- Botão Adicionar Horta -->
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg mb-5" onclick="toggleModal('modalAdicionarHorta')">
                <i class="bi bi-plus-lg"></i> Adicionar Horta
            </button>
        </div>

    </div> <!-- Fecha div.container -->

    <!-- Modal Adicionar Horta -->
    <div id="modalAdicionarHorta" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nova Horta</h3>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="acao" value="adicionar">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <input name="observacoes" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Criar</button>
                    <button type="button" class="btn btn-secondary"
                        onclick="toggleModal('modalAdicionarHorta')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(id) {
            const m = document.getElementById(id);
            m.style.display = m.style.display === 'block' ? 'none' : 'block';
        }
        window.onclick = e => { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; }

        function adicionarCanteiroInputs(idHorta) {
            const container = document.getElementById('canteiros-container-' + idHorta);
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

    <?php include __DIR__ . '/../Assets/footer.php'; ?>
</body>

</html>