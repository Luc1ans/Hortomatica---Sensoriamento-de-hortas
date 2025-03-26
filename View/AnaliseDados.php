<?php
require_once __DIR__ . '/../Controller/Database.php';
require_once('../Controller/DispositivoController.php');
require_once('../Controller/LeituraSensores.php');
require_once('../Assets/Auth.php');
require_once('../Assets/Logout.php');
if (isset($_POST['gerar_pdf'])) {
    header("Location: gerar_pdf.php?idHorta=" . urlencode($idHorta));
    exit();
}


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

// Recupera os dispositivos selecionados via GET; se nenhum for marcado, usa todos
if (isset($_GET['dispositivos'])) {
    $dispositivosSelecionados = is_array($_GET['dispositivos']) ? $_GET['dispositivos'] : explode(',', $_GET['dispositivos']);
} else {
    $dispositivosSelecionados = array_map(function ($d) {
        return $d['idDispositivo'];
    }, $dispositivosIDs);
}


$filtroSensor = $_GET['sensor'] ?? '';
$filtroDataInicial = $_GET['data_inicial'] ?? '';
$filtroDataFinal = $_GET['data_final'] ?? '';

// Recupera leituras de cada dispositivo selecionado e mescla os resultados
$leituras = [];
$ultimasLeituras = [];
foreach ($dispositivosSelecionados as $idDisp) {
    $leiturasDevice = $leituraController->getLeiturasByDispositivo($idDisp, $filtroSensor, $filtroDataInicial, $filtroDataFinal);
    $ultimasDevice = $leituraController->getUltimasLeituras($idDisp);
    $leituras = array_merge($leituras, $leiturasDevice);
    $ultimasLeituras = array_merge($ultimasLeituras, $ultimasDevice);
}

// Organiza as leituras por sensor e timestamp, separando os valores de cada dispositivo
$leiturasPorSensor = [];
foreach ($leituras as $leitura) {
    $sensor = $leitura['nome_sensor'];
    $idDisp = $leitura['Dispositivo_idDispositivo'];
    $timestamp = $leitura['data_leitura'] . ' ' . $leitura['hora_leitura'];
    if (!isset($leiturasPorSensor[$sensor])) {
        $leiturasPorSensor[$sensor] = [];
    }
    if (!isset($leiturasPorSensor[$sensor][$timestamp])) {
        $leiturasPorSensor[$sensor][$timestamp] = [];
    }
    $leiturasPorSensor[$sensor][$timestamp][$idDisp] = (float) $leitura['valor_leitura'];
}

// Prepara os dados para os gráficos: para cada sensor, cria linhas com a data/hora e uma coluna para cada dispositivo selecionado
$chartData = [];
foreach ($leiturasPorSensor as $sensor => $dataByTime) {
    ksort($dataByTime);
    $rows = [];
    foreach ($dataByTime as $timestamp => $deviceValues) {
        $row = [$timestamp];
        foreach ($dispositivosSelecionados as $idDisp) {
            $row[] = isset($deviceValues[$idDisp]) ? $deviceValues[$idDisp] : null;
        }
        $rows[] = $row;
    }
    $chartData[$sensor] = $rows;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Análise de Dados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="..\Assets\css\style.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

        google.charts.load('current', { 'packages': ['corechart'] });
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var chartData = <?php echo json_encode($chartData); ?>;
            var dispositivosSelecionados = <?php echo json_encode($dispositivosSelecionados); ?>;

            for (var sensor in chartData) {
                drawChart(sensor, chartData[sensor], dispositivosSelecionados);
            }
        }

        function drawChart(sensor, rows, dispositivos) {
            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Data e Hora');

            for (var i = 0; i < dispositivos.length; i++) {
                data.addColumn('number', 'Dispositivo ' + dispositivos[i]);
            }

            var formattedRows = rows.map(row => {
                var dateTime = new Date(row[0]);
                return [dateTime, ...row.slice(1)];
            });

            data.addRows(formattedRows);

            var options = {
                title: 'Leituras do Sensor: ' + sensor,
                legend: { position: 'bottom' },
                hAxis: { title: 'Data e Hora', format: 'yyyy/MM/dd HH:mm', slantedText: true },
                vAxis: { title: 'Valor' },
                colors: ['#3e8914', '#FF0000', '#0000FF', '#FF9900'],
                backgroundColor: '#f8f9fa',
                chartArea: { backgroundColor: '#f8f9fa' }
            };


            var chart = new google.visualization.LineChart(document.getElementById('chart_' + sensor));
            chart.draw(data, options);


            var imgUri = chart.getImageURI();
            var inputId = 'img_' + sensor.replace(/\s+/g, '_');
            var input = document.getElementById(inputId);
            if (!input) {
                input = document.createElement("input");
                input.type = "hidden";
                input.id = inputId;
                input.name = inputId;
                document.getElementById("chartImages").appendChild(input);
            }
            input.value = imgUri;

        }

    </script>
    <?php include '../Assets/navbar.php'; ?>
</head>

<body>
    <div class="container mt-4">
        <h3 class="mb-4 text-success"><i class="bi bi-graph-up me-2"></i>Análise de Dados</h3>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4 text-success">
                    <i class="bi bi-check2-square me-2"></i>Filtrar leituras
                </h5>
                <!-- Formulário de Filtro (Método GET) -->
                <form method="GET" action="AnaliseDados.php">
                    <input type="hidden" name="idHorta" value="<?= htmlspecialchars($idHorta, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Lista de Dispositivos -->
                    <div class="mb-3">
                        <label class="form-label">Dispositivos:</label>
                        <div class="d-flex flex-wrap">
                            <?php foreach ($dispositivosIDs as $dispositivo): ?>
                                <?php $checked = in_array($dispositivo['idDispositivo'], $dispositivosSelecionados) ? 'checked' : ''; ?>
                                <div class="form-check me-3 mb-2">
                                    <input class="form-check-input" type="checkbox" name="dispositivos[]"
                                        value="<?= htmlspecialchars($dispositivo['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= $checked; ?>>
                                    <label class="form-check-label">
                                        Dispositivo
                                        <?= htmlspecialchars($dispositivo['idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Filtros adicionais -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="sensor" class="form-label">Sensor</label>
                            <select name="sensor" id="sensor" class="form-select">
                                <option value="">Todos</option>
                                <option value="Umidade do Solo" <?= $filtroSensor === 'Umidade do Solo' ? 'selected' : ''; ?>>Umidade do Solo</option>
                                <option value="Umidade do Ar" <?= $filtroSensor === 'Umidade do Ar' ? 'selected' : ''; ?>>
                                    Umidade do Ar</option>
                                <option value="Chuva Digital" <?= $filtroSensor === 'Chuva Digital' ? 'selected' : ''; ?>>
                                    Chuva Digital</option>
                                <option value="Chuva Analógico" <?= $filtroSensor === 'Chuva Analógico' ? 'selected' : ''; ?>>Chuva Analógico</option>
                                <option value="Temperatura" <?= $filtroSensor === 'Temperatura' ? 'selected' : ''; ?>>
                                    Temperatura</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="data_inicial" class="form-label">Data Inicial</label>
                            <input type="date" name="data_inicial" id="data_inicial" class="form-control"
                                value="<?= htmlspecialchars($filtroDataInicial, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="data_final" class="form-label">Data Final</label>
                            <input type="date" name="data_final" id="data_final" class="form-control"
                                value="<?= htmlspecialchars($filtroDataFinal, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-action" style="width: 120px;">
                            <i class="bi bi-filter me-2"></i>Filtrar
                        </button>
                        <a href="AnaliseDados.php?idHorta=<?= htmlspecialchars($idHorta, ENT_QUOTES, 'UTF-8'); ?>"
                            class="btn btn-secondary btn-action">
                            <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                        </a>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Formulário para Gerar PDF (Método POST) -->
                <form id="pdfForm" method="POST" action="../Assets/gerar_pdf.php" target="_blank">
                    <input type="hidden" name="idHorta" value="<?= htmlspecialchars($idHorta) ?>">
                    <input type="hidden" name="dispositivos"
                        value="<?= htmlspecialchars(implode(',', $dispositivosSelecionados)) ?>">
                    <input type="hidden" name="sensor" value="<?= htmlspecialchars($filtroSensor) ?>">
                    <input type="hidden" name="data_inicial" value="<?= htmlspecialchars($filtroDataInicial) ?>">
                    <input type="hidden" name="data_final" value="<?= htmlspecialchars($filtroDataFinal) ?>">
                    <!-- Container para armazenar as URIs dos gráficos (preenchido via JavaScript) -->
                    <div id="chartImages" style="display: none;"></div>
                    <button type="submit" class="btn btn-danger btn-action" style="width: auto; min-width: 150px;">
                        <i class="bi bi-file-earmark-pdf me-2"></i> Gerar PDF
                    </button>
                </form>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4 text-success"><i class="bi bi-bar-chart-line me-2"></i>Gráficos das
                    Leituras
                </h5>
                <div class="row g-4">
                    <?php foreach ($chartData as $sensor => $rows): ?>
                        <div class="col-12">
                            <div class="chart-container p-3 rounded-3"
                                id="chart_<?= htmlspecialchars($sensor, ENT_QUOTES, 'UTF-8'); ?>" style="height: 300px;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tabelas de Dados -->
        <div class="row g-4">
            <!-- Últimas Leituras -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4 text-success"><i class="bi bi-clock-history me-2"></i>Últimas
                            Leituras</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                        <th>Dispositivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ultimasLeituras)): ?>
                                        <?php foreach ($ultimasLeituras as $leitura): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($leitura['nome_sensor'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['valor_leitura'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['data_leitura'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['hora_leitura'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['Dispositivo_idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma leitura encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Leituras Filtradas -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4 text-success"><i class="bi bi-table me-2"></i>Leituras
                            Filtradas</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sensor</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                        <th>Dispositivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leituras)): ?>
                                        <?php foreach ($leituras as $leitura): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($leitura['nome_sensor'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['valor_leitura'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['data_leitura'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['hora_leitura'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($leitura['Dispositivo_idDispositivo'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma leitura encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include '../Assets/footer.php'; ?>
</body>
</html>