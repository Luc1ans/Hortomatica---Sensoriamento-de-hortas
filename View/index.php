<?php
require_once('../Assets/Auth.php');
require_once('../Assets/Logout.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="..\Assets\style.css">
    <title>Hortomática</title>

    <nav class="navbar navbar-expand-lg bg-body-tertiary custom-navbar">
        <div class="container-fluid">
            <a class="navbar-brand navbar-text" href="#">
                <i class="bi bi-flower1 me-2"></i>Hortomática
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link navbar-text" href="GerenciarHortas.php">Gerenciar Hortas</a>
                    <a class="nav-link navbar-text" href="GerenciarDispositivos.php">Gerenciar Dispositivos</a>
                    <a class="nav-link navbar-text" href="Relatorio.php">Relatórios</a>
                </div>
                <div class="ms-auto">
                    <form action="" method="POST" class="d-inline">
                        <button type="submit" name="logout" class="btn btn-logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Área de botões -->
    <div class="conteudo flex-grow-1">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="GerenciarHortas.php" class="btn-home btn-main">
                        <i class="bi bi-tree-fill display-4 mb-3"></i>
                        Gerenciar Hortas
                    </a>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="GerenciarDispositivos.php" class="btn-home btn-main">
                        <i class="bi bi-cpu display-4 mb-3"></i>
                        Gerenciar Dispositivos
                    </a>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="Relatorio.php" class="btn-home btn-main">
                        <i class="bi bi-clipboard-data-fill display-4 mb-3"></i>
                        Relatórios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>