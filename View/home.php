<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hortomática</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/Assets/css/style.css">
    <?php
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/Site Hortomatica/Hortomatica---Sensoriamento-de-hortas/';
    include __DIR__ . '/../Assets/navbar.php';
    ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Área de botões -->
    <div class="conteudo flex-grow-1">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="index.php?page=gerenciar_hortas" class="btn-home btn-main">
                        <i class="bi bi-tree-fill display-4 mb-3"></i>
                        Gerenciar Hortas
                    </a>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="index.php?page=gerenciar_dispositivos" class="btn-home btn-main">
                        <i class="bi bi-cpu display-4 mb-3"></i>
                        Gerenciar Dispositivos
                    </a>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="index.php?page=relatorios" class="btn-home btn-main">
                        <i class="bi bi-clipboard-data-fill display-4 mb-3"></i>
                        Relatórios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__ . '/../Assets/footer.php'; ?>
</body>

</html>