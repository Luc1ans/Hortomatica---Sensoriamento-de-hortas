<?php
require_once __DIR__ . '/../Controller/Database.php';
require_once('../Controller/DispositivoController.php');
require_once('../Controller/LeituraSensores.php');
require_once('../Assets/Auth.php');
require_once('../Assets/Logout.php');

$pdo = Database::connect();
$controller = new DispositivoController($pdo);
$leituraController = new LeituraSensores();

if (!isset($_GET['idHorta'])) {
    die("Erro: ID da horta não recebido.");
}

$idHorta = $_GET['idHorta'];
$dispositivosIDs = $controller->getDispositivoByHorta($idHorta);

if (empty($dispositivosIDs)) {
    die("Nenhum dispositivo vinculado a esta horta.");
}

$idDispositivo = $dispositivosIDs[0]['idDispositivo'];

$filtroSensor = $_GET['sensor'] ?? '';
$filtroDataInicial = $_GET['data_inicial'] ?? '';
$filtroDataFinal = $_GET['data_final'] ?? '';

$leituras = $leituraController->getLeiturasByDispositivo($idDispositivo, $filtroSensor, $filtroDataInicial, $filtroDataFinal);
$ultimasLeituras = $leituraController->getUltimasLeituras($idDispositivo);

$leiturasPorSensor = [];
foreach ($leituras as $leitura) {
    $sensor = $leitura['nome_sensor'];
    if (!isset($leiturasPorSensor[$sensor])) {
        $leiturasPorSensor[$sensor] = [];
    }
    $leiturasPorSensor[$sensor][] = $leitura;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Análise de Dados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="..\Assets\style.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', { 'packages': ['corechart'] });
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            <?php foreach ($leiturasPorSensor as $sensor => $leituras): ?>
                drawChart('<?= $sensor ?>', <?= json_encode($leituras) ?>);
            <?php endforeach; ?>
        }

        function drawChart(sensor, leituras) {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Data e Hora');
            data.addColumn('number', 'Valor');

            leituras.forEach(function (leitura) {
                data.addRow([leitura.data_leitura + ' ' + leitura.hora_leitura, parseFloat(leitura.valor_leitura)]);
            });

            var options = {
                title: 'Leituras do Sensor: ' + sensor,
                titleTextStyle: { color: '#2c5a1d', fontSize: 18 },
                curveType: 'function',
                legend: { position: 'bottom', textStyle: { color: '#555' } },
                hAxis: { title: 'Data e Hora', textStyle: { color: '#666' }, titleTextStyle: { color: '#666' } },
                vAxis: { title: 'Valor', textStyle: { color: '#666' }, titleTextStyle: { color: '#666' } },
                colors: ['#3e8914'],
                backgroundColor: '#f8f9fa',
                chartArea: { backgroundColor: '#f8f9fa' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart_' + sensor));
            chart.draw(data, options);
        }
    </script>
    <nav class="navbar navbar-expand-lg bg-body-tertiary custom-navbar">
        <div class="container-fluid">
            <a class="navbar-brand navbar-text" href="index.php">
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

<body>
    <div class="container mt-4">
        <h3 class="mb-4 text-success"><i class="bi bi-graph-up me-2"></i>Análise de Dados</h3>

        <!-- Filtros -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4 text-success"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                <form method="GET" action="">
                    <input type="hidden" name="idHorta" value="<?= htmlspecialchars($idHorta, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="sensor" class="form-label">Sensor</label>
                            <select name="sensor" id="sensor" class="form-select">
                                <option value="">Todos</option>
                                <option value="Umidade do Solo" <?= $filtroSensor === 'Umidade do Solo' ? 'selected' : ''; ?>>Umidade do Solo</option>
                                <option value="Umidade do Ar" <?= $filtroSensor === 'Umidade do Ar' ? 'selected' : ''; ?>>Umidade do Ar</option>
                                <option value="Chuva Digital" <?= $filtroSensor === 'Chuva Digital' ? 'selected' : ''; ?>>Chuva Digital</option>
                                <option value="Chuva Analógico" <?= $filtroSensor === 'Chuva Analógico' ? 'selected' : ''; ?>>Chuva Analógico</option>
                                <option value="Temperatura" <?= $filtroSensor === 'Temperatura' ? 'selected' : ''; ?>>Temperatura</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="data_inicial" class="form-label">Data Inicial</label>
                            <input type="date" name="data_inicial" id="data_inicial" class="form-control"
                                value="<?= htmlspecialchars($filtroDataInicial, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="data_final" class="form-label">Data Final</label>
                            <input type="date" name="data_final" id="data_final" class="form-control"
                                value="<?= htmlspecialchars($filtroDataFinal, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary btn-action flex-grow-1">
                                <i class="bi bi-filter me-2"></i>Filtrar
                            </button>
                            <a href="AnaliseDados.php?idHorta=<?= htmlspecialchars($idHorta, ENT_QUOTES, 'UTF-8'); ?>" 
                               class="btn btn-secondary btn-action">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4 text-success"><i class="bi bi-bar-chart-line me-2"></i>Gráficos das Leituras</h5>
                <div class="row g-4">
                    <?php foreach ($leiturasPorSensor as $sensor => $leituras): ?>
                        <div class="col-12">
                            <div class="chart-container p-3 rounded-3" 
                                 id="chart_<?= htmlspecialchars($sensor, ENT_QUOTES, 'UTF-8'); ?>" 
                                 style="height: 300px;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Dados -->
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4 text-success"><i class="bi bi-clock-history me-2"></i>Últimas Leituras</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ultimasLeituras)): ?>
                                        <?php foreach ($ultimasLeituras as $leitura): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($leitura['nome_sensor'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($leitura['valor_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($leitura['data_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($leitura['hora_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Nenhuma leitura encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4 text-success"><i class="bi bi-table me-2"></i>Leituras Filtradas</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leituras)): ?>
                                        <?php foreach ($leituras as $leitura): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($leitura['nome_sensor'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($leitura['valor_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($leitura['data_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($leitura['hora_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Nenhuma leitura encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary btn-action">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>
</body>
</html>