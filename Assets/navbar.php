<?php
require_once('Logout.php');

?>
<nav class="navbar navbar-expand-lg bg-body-tertiary custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand navbar-text" href="<?= BASE_PATH ?>/index.php">
            <img src="<?= ASSETS ?>/image/logo-branca.png" alt="Logo Hortomática" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
            aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link navbar-text" href="<?= BASE_PATH ?>/GerenciarHortas.php">Gerenciar Hortas</a>
                <a class="nav-link navbar-text" href="<?= BASE_PATH ?>/GerenciarDispositivos.php">Gerenciar
                    Dispositivos</a>
                <a class="nav-link navbar-text" href="<?= BASE_PATH ?>/Relatorio.php">Relatórios</a>
            </div>
            <div class="ms-auto">
                <form action="<?= BASE_PATH ?>/index.php" method="POST" class="d-inline">
                    <button type="submit" name="logout" class="btn btn-logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>